<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Models\Store;
use App\Models\TapTryCollect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Retur dan penukaran barang.
 *
 * Ada dua asal pembelian yang dilayani:
 * 1. pesanan daring, dikenali dari kode pesanan (contoh CBX-20260720-0001),
 * 2. pembelian di toko lewat Tap Try and Collect, dikenali dari kode pembelian
 *    yang tertera pada struk.
 *
 * Karena keduanya memakai satu alamat formulir yang sama, kode pada alamat
 * halaman dibaca sebagai teks biasa lalu dicari sendiri di controller ini.
 */
class ReturController extends Controller
{
    public function index()
    {
        $pengguna = auth()->user();

        $pesanan = Order::where('user_id', $pengguna->id)
            ->whereIn('status', ['diterima', 'selesai'])
            ->with('item')
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($p) => ! $this->lewatBatasRetur($p->completed_at ?: $p->updated_at))
            ->values();

        return view('retur.index', [
            'daftar' => ReturnRequest::where('user_id', $pengguna->id)
                ->with(['pesanan', 'toko', 'item'])
                ->orderByDesc('id')
                ->get(),
            'pesananBisaRetur' => $pesanan,
            'hariRetur' => (int) setting('return_window_days', 7),
        ]);
    }

    /** Mencari struk pembelian di toko memakai kode pembelian. */
    public function cariStruk(Request $request)
    {
        $data = $request->validate([
            'purchase_code' => ['required', 'string', 'max:40'],
        ], [
            'purchase_code.required' => 'Masukkan kode pembelian yang tertera pada struk.',
        ]);

        $kode = strtoupper(trim($data['purchase_code']));

        $booking = TapTryCollect::where('user_id', auth()->id())
            ->where('purchase_code', $kode)
            ->first();

        if (! $booking) {
            return back()->with('gagal', 'Kode pembelian '.$kode.' tidak ditemukan pada akun Anda. Periksa kembali struk Anda.');
        }

        return redirect()->route('retur.create', $kode);
    }

    /**
     * Formulir pengajuan retur. Parameter $order berisi kode pesanan daring
     * atau kode pembelian di toko.
     */
    public function create(Request $request, string $order)
    {
        $kode = strtoupper(trim($order));
        $pesanan = $this->cariPesanan($kode);
        $booking = $pesanan ? null : $this->cariBooking($kode);

        if (! $pesanan && ! $booking) {
            abort(404);
        }

        $penolakan = $this->periksaBatas($pesanan, $booking);
        if ($penolakan) {
            return redirect()->route('retur.index')->with('gagal', $penolakan);
        }

        return view('retur.form', [
            'kode' => $kode,
            'pesanan' => $pesanan,
            'booking' => $booking,
            'barang' => $this->daftarBarang($pesanan, $booking),
            'daftarToko' => Store::where('is_active', true)->orderBy('name')->get(),
            'hariRetur' => (int) setting('return_window_days', 7),
        ]);
    }

    /** Menyimpan pengajuan retur. */
    public function store(Request $request, string $order)
    {
        $kode = strtoupper(trim($order));
        $pesanan = $this->cariPesanan($kode);
        $booking = $pesanan ? null : $this->cariBooking($kode);

        if (! $pesanan && ! $booking) {
            abort(404);
        }

        $penolakan = $this->periksaBatas($pesanan, $booking);
        if ($penolakan) {
            return redirect()->route('retur.index')->with('gagal', $penolakan);
        }

        $data = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*' => ['required', 'integer'],
            'jumlah' => ['nullable', 'array'],
            'jumlah.*' => ['nullable', 'integer', 'min:1', 'max:20'],
            'reason' => ['required', 'string', 'max:191'],
            'type' => ['required', 'in:tukar,refund'],
            'method' => ['required', 'in:online,toko'],
            'store_id' => ['required_if:method,toko', 'nullable', 'integer', 'exists:stores,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'item.required' => 'Pilih dulu barang yang ingin diretur.',
            'reason.required' => 'Tuliskan alasan pengajuan retur.',
            'type.required' => 'Pilih penyelesaian yang diharapkan.',
            'method.required' => 'Pilih cara pengembalian barang.',
            'store_id.required_if' => 'Pilih toko tujuan pengembalian.',
            'photo.max' => 'Ukuran foto paling besar 2 MB.',
        ]);

        $barang = $this->daftarBarang($pesanan, $booking);
        $dipilih = [];
        foreach ($data['item'] as $kunci) {
            $baris = $barang->firstWhere('kunci', (int) $kunci);
            if (! $baris) {
                return back()->withInput()->with('gagal', 'Ada barang yang dipilih tidak terdapat pada pembelian ini.');
            }
            $jumlah = (int) ($data['jumlah'][$kunci] ?? 1);
            $baris['jumlah'] = max(1, min($jumlah, $baris['maks']));
            $dipilih[] = $baris;
        }

        // Foto bukti disimpan langsung ke folder public/images/retur
        $foto = null;
        if ($request->hasFile('photo')) {
            $berkas = $request->file('photo');
            $nama = 'retur-'.now()->format('YmdHis').'-'.random_int(100, 999).'.'.$berkas->getClientOriginalExtension();
            $folder = public_path('images/retur');
            if (! file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            $berkas->move($folder, $nama);
            $foto = 'images/retur/'.$nama;
        }

        $retur = DB::transaction(function () use ($data, $dipilih, $pesanan, $booking, $foto) {
            // Kode retur: RTR-YYYYMMDD-0001 dari jumlah pengajuan hari ini + 1
            $urut = ReturnRequest::whereDate('created_at', now()->toDateString())->count() + 1;
            $kodeRetur = 'RTR-'.now()->format('Ymd').'-'.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);

            $retur = ReturnRequest::create([
                'return_code' => $kodeRetur,
                'user_id' => auth()->id(),
                'order_id' => $pesanan?->id,
                'store_id' => $data['method'] === 'toko' ? (int) $data['store_id'] : $booking?->store_id,
                'purchase_code' => $booking?->purchase_code,
                'type' => $data['type'],
                'method' => $data['method'],
                'reason' => $data['reason'],
                'photo' => $foto,
                'status' => 'menunggu_validasi',
            ]);

            foreach ($dipilih as $baris) {
                ReturnItem::create([
                    'return_request_id' => $retur->id,
                    'order_item_id' => $baris['order_item_id'],
                    'product_variant_id' => $baris['product_variant_id'],
                    'product_name' => $baris['nama'],
                    'quantity' => $baris['jumlah'],
                    'note' => $baris['ket'],
                ]);
            }

            return $retur;
        });

        return redirect()->route('retur.show', $retur)
            ->with('berhasil', 'Pengajuan retur '.$retur->return_code.' terkirim dan sedang menunggu validasi.');
    }

    public function show(ReturnRequest $returnRequest)
    {
        if ($returnRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $returnRequest->load(['pesanan', 'toko', 'item']);

        return view('retur.show', [
            'retur' => $returnRequest,
            'riwayat' => $this->riwayatStatus($returnRequest),
        ]);
    }

    public function batal(ReturnRequest $returnRequest)
    {
        if ($returnRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if ($returnRequest->status !== 'menunggu_validasi') {
            return back()->with('gagal', 'Pengajuan yang sudah diperiksa tim Colorbox tidak dapat dibatalkan sendiri.');
        }

        $returnRequest->update(['status' => 'dibatalkan']);

        return back()->with('berhasil', 'Pengajuan retur '.$returnRequest->return_code.' dibatalkan.');
    }

    /** Mencari pesanan daring milik pengguna dari kodenya. */
    private function cariPesanan(string $kode): ?Order
    {
        return Order::where('user_id', auth()->id())
            ->where('order_code', $kode)
            ->with(['item.varian', 'toko'])
            ->first();
    }

    /** Mencari pembelian di toko milik pengguna dari kode pembeliannya. */
    private function cariBooking(string $kode): ?TapTryCollect
    {
        return TapTryCollect::where('user_id', auth()->id())
            ->where('purchase_code', $kode)
            ->with(['item.varian.produk', 'toko'])
            ->first();
    }

    /** Menyusun daftar barang yang boleh diretur dari kedua asal pembelian. */
    private function daftarBarang(?Order $pesanan, ?TapTryCollect $booking)
    {
        $barang = collect();

        if ($pesanan) {
            foreach ($pesanan->item as $baris) {
                $barang->push([
                    'kunci' => $baris->id,
                    'order_item_id' => $baris->id,
                    'product_variant_id' => $baris->product_variant_id,
                    'nama' => $baris->product_name,
                    'ket' => trim($baris->color.' / '.$baris->size, ' /'),
                    'gambar' => $baris->image_path,
                    'maks' => (int) $baris->quantity,
                ]);
            }

            return $barang;
        }

        if ($booking) {
            foreach ($booking->item as $baris) {
                if (! $baris->purchased || ! $baris->varian) {
                    continue;
                }
                $produk = $baris->varian->produk;
                $barang->push([
                    'kunci' => $baris->product_variant_id,
                    'order_item_id' => null,
                    'product_variant_id' => $baris->product_variant_id,
                    'nama' => $produk?->name ?: 'Produk Colorbox',
                    'ket' => $baris->varian->label(),
                    'gambar' => $produk?->gambarUtama()?->path,
                    'maks' => (int) $baris->quantity,
                ]);
            }
        }

        return $barang;
    }

    /**
     * Memeriksa apakah pembelian masih boleh diretur. Mengembalikan pesan
     * penolakan, atau null bila masih boleh.
     */
    private function periksaBatas(?Order $pesanan, ?TapTryCollect $booking): ?string
    {
        $hari = (int) setting('return_window_days', 7);

        if ($pesanan) {
            if (! in_array($pesanan->status, ['diterima', 'selesai'])) {
                return 'Retur dapat diajukan setelah pesanan '.$pesanan->order_code.' diterima.';
            }
            if ($this->lewatBatasRetur($pesanan->completed_at ?: $pesanan->updated_at)) {
                return 'Batas pengajuan retur '.$hari.' hari untuk pesanan '.$pesanan->order_code.' sudah lewat.';
            }

            return null;
        }

        if ($booking) {
            if ($booking->status !== 'selesai') {
                return 'Pembelian '.$booking->purchase_code.' belum tercatat selesai di toko.';
            }
            if ($this->lewatBatasRetur($booking->completed_at ?: $booking->updated_at)) {
                return 'Batas pengajuan retur '.$hari.' hari untuk pembelian '.$booking->purchase_code.' sudah lewat.';
            }
        }

        return null;
    }

    /** Batas waktu retur dihitung sejak barang diterima pelanggan. */
    private function lewatBatasRetur($tanggal): bool
    {
        if (! $tanggal) {
            return true;
        }

        $hari = (int) setting('return_window_days', 7);

        return $tanggal->copy()->addDays($hari)->isPast();
    }

    /** Riwayat status sederhana untuk halaman rincian retur. */
    private function riwayatStatus(ReturnRequest $retur): array
    {
        $riwayat = [[
            'status' => 'Pengajuan dikirim',
            'keterangan' => 'Pengajuan '.$retur->return_code.' diterima sistem.',
            'waktu' => $retur->created_at,
        ]];

        if ($retur->approved_at) {
            $riwayat[] = [
                'status' => 'Pengajuan disetujui',
                'keterangan' => 'Tim Colorbox menyetujui pengajuan ini.',
                'waktu' => $retur->approved_at,
            ];
        }

        if ($retur->completed_at) {
            $riwayat[] = [
                'status' => 'Retur selesai',
                'keterangan' => 'Penukaran atau pengembalian dana sudah diselesaikan.',
                'waktu' => $retur->completed_at,
            ];
        }

        if (! $retur->completed_at && $retur->status !== 'menunggu_validasi') {
            $riwayat[] = [
                'status' => ucfirst(str_replace('_', ' ', $retur->status)),
                'keterangan' => $retur->admin_note ?: 'Status terakhir pengajuan ini.',
                'waktu' => $retur->updated_at,
            ];
        }

        return array_reverse($riwayat);
    }
}
