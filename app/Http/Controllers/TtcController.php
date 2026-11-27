<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\TapTryCollect;
use App\Models\TapTryCollectItem;
use App\Models\TapTryCollectSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tap Try and Collect: pelanggan memilih produk, toko, tanggal, dan jam
 * kunjungan. Slot layanan ini memakai tabel tap_try_collect_slots yang
 * benar-benar terpisah dari slot donasi (kuota dan jamnya sendiri).
 */
class TtcController extends Controller
{
    /** Halaman utama pemesanan. Boleh dilihat tamu, memesannya harus masuk dulu. */
    public function index(Request $request)
    {
        $request->validate([
            'varian' => ['nullable', 'integer'],
            'produk' => ['nullable', 'string', 'max:191'],
        ]);

        // Produk yang bisa dicoba di toko
        $produk = Product::where('is_active', true)
            ->with(['kategori', 'gambar', 'varian'])
            ->orderBy('name')
            ->get();

        // Varian yang ditandai ketika pelanggan datang dari halaman produk
        $varianTerpilih = 0;
        if ($request->query('varian')) {
            $varian = ProductVariant::find((int) $request->query('varian'));
            if ($varian) {
                $varianTerpilih = $varian->id;
            }
        } elseif ($request->query('produk')) {
            $dariSlug = $produk->firstWhere('slug', $request->query('produk'));
            if ($dariSlug) {
                $pertama = $dariSlug->varian->firstWhere('stock', '>', 0) ?: $dariSlug->varian->first();
                $varianTerpilih = $pertama ? $pertama->id : 0;
            }
        }

        // Kalender 14 hari ke depan (sesuai pengaturan booking_window_days)
        $jumlahHari = (int) setting('booking_window_days', 14);
        $tanggal = [];
        for ($i = 0; $i < $jumlahHari; $i++) {
            $tanggal[] = now()->addDays($i)->toDateString();
        }

        return view('ttc.index', [
            'produk' => $produk,
            'varianTerpilih' => $varianTerpilih,
            'toko' => Store::where('is_active', true)->where('has_ttc', true)->orderBy('city')->orderBy('name')->get(),
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * Daftar jam Tap Try and Collect pada satu toko dan satu tanggal.
     * Dipanggil halaman index memakai fetch, jadi jawabannya berupa JSON.
     */
    public function slot(Request $request)
    {
        $data = $request->validate([
            'toko' => ['required', 'integer', 'exists:stores,id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
        ]);

        $daftar = TapTryCollectSlot::where('store_id', $data['toko'])
            ->whereDate('date', $data['tanggal'])
            ->where('status', 'aktif')
            ->orderBy('start_time')
            ->get();

        $slot = [];
        foreach ($daftar as $satu) {
            $slot[] = [
                'id' => $satu->id,
                'jam' => jam_singkat($satu->start_time).' - '.jam_singkat($satu->end_time),
                'sisa' => $satu->sisaKuota(),
                'penuh' => $satu->sisaKuota() < 1,
            ];
        }

        return response()->json(['slot' => $slot]);
    }

    /** Menyimpan booking. Kuota diperiksa ulang di dalam transaksi. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tap_try_collect_slot_id' => ['required', 'integer', 'exists:tap_try_collect_slots,id'],
            'product_variant_id' => ['required', 'array', 'min:1'],
            'product_variant_id.*' => ['integer', 'exists:product_variants,id'],
        ], [
            'tap_try_collect_slot_id.required' => 'Pilih tanggal dan jam kunjungan terlebih dahulu.',
            'product_variant_id.required' => 'Pilih paling sedikit satu produk yang ingin dicoba.',
        ]);

        $slot = TapTryCollectSlot::with('toko')->find($data['tap_try_collect_slot_id']);

        if (! $slot || $slot->status !== 'aktif') {
            return back()->withInput()->with('gagal', 'Jam yang dipilih sudah tidak dilayani. Silakan pilih waktu lainnya.');
        }

        if ($slot->date->toDateString() < now()->toDateString()) {
            return back()->withInput()->with('gagal', 'Tanggal yang dipilih sudah lewat. Silakan pilih tanggal lainnya.');
        }

        if (! $slot->toko || ! $slot->toko->is_active || ! $slot->toko->has_ttc) {
            return back()->withInput()->with('gagal', 'Toko tersebut belum melayani Tap Try and Collect.');
        }

        // Satu pelanggan hanya boleh punya satu booking aktif pada slot yang sama
        $sudahAda = TapTryCollect::where('user_id', auth()->id())
            ->where('tap_try_collect_slot_id', $slot->id)
            ->whereIn('status', ['dijadwalkan', 'siap'])
            ->exists();

        if ($sudahAda) {
            return back()->withInput()->with('gagal', 'Anda sudah memiliki jadwal pada jam tersebut.');
        }

        $varian = ProductVariant::whereIn('id', $data['product_variant_id'])->get();

        if ($varian->count() < 1) {
            return back()->withInput()->with('gagal', 'Produk yang dipilih tidak ditemukan.');
        }

        // Kuota diperiksa ULANG di dalam transaksi, bukan hanya di JavaScript
        $booking = DB::transaction(function () use ($slot, $varian) {
            $terkunci = TapTryCollectSlot::where('id', $slot->id)->lockForUpdate()->first();

            if ($terkunci->booked_count >= $terkunci->quota) {
                return null;
            }

            $urut = TapTryCollect::whereDate('created_at', now()->toDateString())->count() + 1;

            $baru = TapTryCollect::create([
                'booking_code' => 'TTC-'.now()->format('Ymd').'-'.str_pad($urut, 4, '0', STR_PAD_LEFT),
                'user_id' => auth()->id(),
                'tap_try_collect_slot_id' => $terkunci->id,
                'store_id' => $terkunci->store_id,
                'status' => 'dijadwalkan',
                'total_paid' => 0,
            ]);

            foreach ($varian as $satu) {
                TapTryCollectItem::create([
                    'tap_try_collect_id' => $baru->id,
                    'product_variant_id' => $satu->id,
                    'quantity' => 1,
                    'purchased' => false,
                ]);
            }

            $terkunci->booked_count = $terkunci->booked_count + 1;
            $terkunci->save();

            return $baru;
        });

        if (! $booking) {
            return back()->withInput()->with('gagal', 'Maaf, slot baru saja penuh. Silakan pilih waktu lainnya.');
        }

        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Jadwal Tap Try and Collect tercatat',
            'body' => 'Kode '.$booking->booking_code.' pada '.tanggal_id($slot->date, true)
                .' pukul '.jam_singkat($slot->start_time).' di '.$slot->toko->name.'.',
            'url' => route('ttc.show', $booking),
        ]);

        return redirect()->route('ttc.show', $booking)
            ->with('berhasil', 'Jadwal Tap Try and Collect '.$booking->booking_code.' berhasil dibuat.');
    }

    /** Riwayat booking milik pelanggan yang sedang masuk. */
    public function riwayat()
    {
        $daftar = TapTryCollect::where('user_id', auth()->id())
            ->with(['slot', 'toko', 'item.varian.produk'])
            ->orderByDesc('id')
            ->get();

        return view('ttc.riwayat', ['daftar' => $daftar]);
    }

    /** Rincian satu booking beserta QR untuk staf toko. */
    public function show(TapTryCollect $tapTryCollect)
    {
        abort_unless($tapTryCollect->user_id === auth()->id(), 404);

        $tapTryCollect->load(['slot', 'toko', 'item.varian.produk.gambar']);

        return view('ttc.show', [
            'booking' => $tapTryCollect,
            'alamatStaf' => route('staf.verifikasi', ['jenis' => 'ttc', 'kode' => $tapTryCollect->booking_code]),
        ]);
    }

    /** Membatalkan booking dan mengembalikan kuota slotnya. */
    public function batal(TapTryCollect $tapTryCollect)
    {
        abort_unless($tapTryCollect->user_id === auth()->id(), 404);

        if ($tapTryCollect->status !== 'dijadwalkan') {
            return back()->with('gagal', 'Jadwal ini sudah tidak bisa dibatalkan.');
        }

        DB::transaction(function () use ($tapTryCollect) {
            $slot = TapTryCollectSlot::where('id', $tapTryCollect->tap_try_collect_slot_id)->lockForUpdate()->first();

            if ($slot && $slot->booked_count > 0) {
                $slot->booked_count = $slot->booked_count - 1;
                $slot->save();
            }

            $tapTryCollect->status = 'dibatalkan';
            $tapTryCollect->save();
        });

        return redirect()->route('ttc.show', $tapTryCollect)
            ->with('berhasil', 'Jadwal '.$tapTryCollect->booking_code.' dibatalkan.');
    }
}
