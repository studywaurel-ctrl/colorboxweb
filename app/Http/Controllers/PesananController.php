<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Daftar dan rincian pesanan milik pelanggan yang sedang masuk. */
class PesananController extends Controller
{
    /** Daftar status yang dipakai untuk menyaring pesanan. */
    private array $daftarStatus = [
        'menunggu_pembayaran', 'dibayar', 'diproses', 'dikemas',
        'dikirim', 'siap_diambil', 'diterima', 'selesai', 'dibatalkan',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');

        $pesanan = Order::where('user_id', auth()->id())
            ->with(['item', 'pembayaran'])
            ->orderByDesc('id');

        if ($status && in_array($status, $this->daftarStatus)) {
            $pesanan->where('status', $status);
        }

        return view('pesanan.index', [
            'daftar' => $pesanan->paginate(10)->withQueryString(),
            'status' => $status,
            'daftarStatus' => $this->daftarStatus,
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['item.varian.produk', 'alamat', 'toko', 'pembayaran', 'pengiriman']);

        // Ulasan hanya dibuka setelah barang diterima
        $bisaUlas = in_array($order->status, ['diterima', 'selesai']);

        return view('pesanan.show', [
            'pesanan' => $order,
            'riwayat' => $order->riwayat()->orderByDesc('happened_at')->orderByDesc('id')->get(),
            'bisaUlas' => $bisaUlas,
            'bisaRetur' => $bisaUlas && ! $this->lewatBatasRetur($order),
            'hariRetur' => (int) setting('return_window_days', 7),
        ]);
    }

    /** Pembatalan oleh pelanggan, stok dan poin dikembalikan. */
    public function batal(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $order->bisaDibatalkan()) {
            return back()->with('gagal', 'Pesanan dengan status '.$order->labelStatus().' tidak dapat dibatalkan lagi.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'dibatalkan', 'cancelled_at' => now()]);

            foreach ($order->item as $baris) {
                if ($baris->varian) {
                    $baris->varian->increment('stock', $baris->quantity);
                }
            }

            if ($order->pembayaran && $order->pembayaran->payment_status === 'menunggu') {
                $order->pembayaran->update([
                    'transaction_status' => 'cancel',
                    'payment_status' => 'dibatalkan',
                ]);
            }

            if ($order->points_used > 0 && $order->pengguna) {
                $order->pengguna->ubahPoin(
                    (int) $order->points_used,
                    'pesanan',
                    'Poin dikembalikan karena pesanan '.$order->order_code.' dibatalkan'
                );
            }

            // kuota voucher dilepas kembali
            if ($order->voucher_id) {
                \App\Models\Voucher::where('id', $order->voucher_id)
                    ->where('used', '>', 0)->decrement('used');
            }

            TrackingEvent::create([
                'order_id' => $order->id,
                'status' => 'dibatalkan',
                'description' => 'Pesanan dibatalkan oleh pelanggan',
                'happened_at' => now(),
            ]);
        });

        return back()->with('berhasil', 'Pesanan '.$order->order_code.' dibatalkan.');
    }

    /** Pelanggan menyatakan barangnya sudah diterima. */
    public function terima(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($order->status, ['dikirim', 'siap_diambil'])) {
            return back()->with('gagal', 'Tombol ini hanya dipakai setelah pesanan dikirim atau siap diambil di toko.');
        }

        DB::transaction(function () use ($order) {
            TrackingEvent::create([
                'order_id' => $order->id,
                'status' => 'diterima',
                'description' => 'Barang diterima pelanggan',
                'happened_at' => now(),
            ]);

            // Setelah diterima, pesanan langsung ditutup sebagai selesai
            // sehingga ulasan dan pengajuan retur terbuka.
            $order->update(['status' => 'selesai', 'completed_at' => now()]);

            if ($order->pengiriman) {
                $order->pengiriman->update(['status' => 'diterima', 'delivered_at' => now()]);
            }

            TrackingEvent::create([
                'order_id' => $order->id,
                'status' => 'selesai',
                'description' => 'Pesanan selesai, ulasan dan pengajuan retur dibuka',
                'happened_at' => now(),
            ]);
        });

        return back()->with('berhasil', 'Terima kasih. Pesanan '.$order->order_code.' ditandai sudah diterima.');
    }

    /** Memasukkan lagi seluruh barang pesanan ini ke keranjang. */
    public function beliLagi(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $keranjang = $this->keranjangAktif();
        $masuk = 0;
        $kosong = 0;

        foreach ($order->item as $baris) {
            $varian = $baris->varian;
            if (! $varian || $varian->stock < 1) {
                $kosong++;

                continue;
            }

            $item = $keranjang->item()->where('product_variant_id', $varian->id)->first();
            $jumlah = $item ? $item->quantity + $baris->quantity : $baris->quantity;
            $jumlah = min($jumlah, (int) $varian->stock);

            if ($item) {
                $item->update(['quantity' => $jumlah, 'selected' => true]);
            } else {
                CartItem::create([
                    'cart_id' => $keranjang->id,
                    'product_variant_id' => $varian->id,
                    'quantity' => $jumlah,
                    'selected' => true,
                ]);
            }
            $masuk++;
        }

        if ($masuk === 0) {
            return back()->with('gagal', 'Barang pada pesanan ini sedang tidak tersedia.');
        }

        $pesan = $masuk.' barang dimasukkan lagi ke keranjang.';
        if ($kosong > 0) {
            $pesan .= ' '.$kosong.' barang dilewati karena stoknya habis.';
        }

        return redirect()->route('keranjang.index')->with('berhasil', $pesan);
    }

    /** Memeriksa apakah batas waktu pengajuan retur sudah lewat. */
    private function lewatBatasRetur(Order $pesanan): bool
    {
        $hari = (int) setting('return_window_days', 7);
        $diterima = $pesanan->completed_at ?: $pesanan->updated_at;

        if (! $diterima) {
            return true;
        }

        return $diterima->copy()->addDays($hari)->isPast();
    }

    /** Keranjang milik pengguna yang sedang masuk. */
    private function keranjangAktif(): Cart
    {
        return Cart::firstOrCreate(['user_id' => auth()->id()]);
    }
}
