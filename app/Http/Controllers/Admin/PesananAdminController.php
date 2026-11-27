<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pesanan pelanggan dan pembayarannya.
 *
 * Catatan penting: status pembayaran tidak pernah diubah dengan tangan.
 * Admin hanya boleh menanyakan ulang status ke Midtrans, lalu jawabannya
 * yang disimpan.
 */
class PesananAdminController extends Controller
{
    /** Status pesanan yang boleh dipilih admin. */
    private array $statusBoleh = [
        'menunggu_pembayaran', 'dibayar', 'diproses', 'dikemas',
        'dikirim', 'siap_diambil', 'diterima', 'selesai', 'dibatalkan',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $cari = trim((string) $request->query('cari', ''));

        $kueri = Order::with(['pengguna', 'pembayaran'])->orderByDesc('id');

        if ($status && in_array($status, $this->statusBoleh)) {
            $kueri->where('status', $status);
        }

        if ($cari !== '') {
            $kueri->where(function ($bagian) use ($cari) {
                $bagian->where('order_code', 'like', '%'.$cari.'%')
                    ->orWhereHas('pengguna', function ($pemilik) use ($cari) {
                        $pemilik->where('name', 'like', '%'.$cari.'%')
                            ->orWhere('email', 'like', '%'.$cari.'%');
                    });
            });
        }

        return view('admin.pesanan.index', [
            'daftar' => $kueri->paginate(20)->withQueryString(),
            'daftarStatus' => $this->statusBoleh,
            'status' => $status,
            'cari' => $cari,
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['pengguna', 'alamat', 'toko', 'item', 'pembayaran', 'pengiriman']);

        return view('admin.pesanan.show', [
            'pesanan' => $order,
            'riwayat' => $order->riwayat()->orderByDesc('happened_at')->orderByDesc('id')->get(),
            'daftarStatus' => $this->statusBoleh,
        ]);
    }

    /** Mengubah status pesanan dan mencatat riwayatnya. */
    public function ubahStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', $this->statusBoleh)],
            'keterangan' => ['nullable', 'string', 'max:191'],
            'courier' => ['nullable', 'string', 'max:40'],
            'service' => ['nullable', 'string', 'max:60'],
            'tracking_number' => ['nullable', 'string', 'max:60'],
        ], [
            'status.required' => 'Pilih status baru pesanan.',
            'status.in' => 'Status yang dipilih tidak dikenal.',
        ]);

        $statusBaru = $data['status'];

        if ($statusBaru === $order->status) {
            return back()->with('ingat', 'Status pesanan sudah '.$order->labelStatus().', tidak ada yang diubah.');
        }

        if ($order->status === 'dibatalkan') {
            return back()->with('gagal', 'Pesanan yang sudah dibatalkan tidak bisa diubah lagi.');
        }

        // Pengiriman wajib lengkap supaya pelanggan bisa melacak paketnya
        if ($statusBaru === 'dikirim' && empty($data['tracking_number'])) {
            return back()->with('gagal', 'Isi nomor resi lebih dahulu sebelum menandai pesanan sebagai dikirim.');
        }

        DB::transaction(function () use ($order, $statusBaru, $data) {
            $order->status = $statusBaru;

            if ($statusBaru === 'dibayar' && ! $order->paid_at) {
                $order->paid_at = now();
            }
            if ($statusBaru === 'selesai' && ! $order->completed_at) {
                $order->completed_at = now();
            }
            if ($statusBaru === 'dibatalkan') {
                $order->cancelled_at = now();
                $this->kembalikanStok($order);
            }

            $order->save();

            if ($statusBaru === 'dikirim') {
                Shipment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'courier' => $data['courier'] ?? null,
                        'service' => $data['service'] ?? null,
                        'tracking_number' => $data['tracking_number'] ?? null,
                        'cost' => (int) $order->shipping_cost,
                        'status' => 'dikirim',
                        'shipped_at' => now(),
                    ]
                );
            }

            $keterangan = $data['keterangan'] ?? '';
            if ($keterangan === '') {
                $keterangan = $this->keteranganBawaan($statusBaru);
            }

            TrackingEvent::create([
                'order_id' => $order->id,
                'status' => $statusBaru,
                'description' => $keterangan,
                'happened_at' => now(),
            ]);

            Notification::create([
                'user_id' => $order->user_id,
                'title' => 'Pesanan '.$order->order_code.' '.$order->labelStatus(),
                'body' => $keterangan,
                'url' => route('pesanan.show', $order->order_code),
            ]);
        });

        return redirect()->route('admin.pesanan.show', $order)
            ->with('berhasil', 'Status pesanan '.$order->order_code.' diubah menjadi '.$order->labelStatus().'.');
    }

    /** Daftar pembayaran beserta status terakhir dari Midtrans. */
    public function pembayaran(Request $request)
    {
        $status = $request->query('status');
        $cari = trim((string) $request->query('cari', ''));

        $kueri = Payment::with('pesanan.pengguna')->orderByDesc('id');

        if ($status) {
            $kueri->where('payment_status', $status);
        }

        if ($cari !== '') {
            $kueri->whereHas('pesanan', function ($pesanan) use ($cari) {
                $pesanan->where('order_code', 'like', '%'.$cari.'%');
            });
        }

        return view('admin.pembayaran.index', [
            'daftar' => $kueri->paginate(20)->withQueryString(),
            'daftarStatus' => ['menunggu', 'lunas', 'gagal', 'kedaluwarsa', 'dibatalkan'],
            'status' => $status,
            'cari' => $cari,
        ]);
    }

    /**
     * Menanyakan ulang status sebuah pembayaran ke Midtrans.
     * Nilai yang disimpan hanya berasal dari jawaban Midtrans.
     */
    public function periksaPembayaran(Payment $payment, MidtransService $midtrans)
    {
        $pesanan = $payment->pesanan;

        if (! $pesanan) {
            return back()->with('gagal', 'Pembayaran ini tidak punya pesanan, tidak bisa diperiksa.');
        }

        $jawaban = $midtrans->ambilStatus($pesanan->order_code);

        if (! $jawaban) {
            return back()->with('gagal', 'Midtrans belum bisa dihubungi atau transaksi ini belum terdaftar di sana. Silakan coba lagi sebentar lagi.');
        }

        $statusColorbox = $midtrans->statusColorbox($jawaban);

        $payment->transaction_status = $jawaban['transaction_status'] ?? $payment->transaction_status;
        $payment->payment_status = $statusColorbox;
        $payment->transaction_id = $jawaban['transaction_id'] ?? $payment->transaction_id;
        $payment->payment_method = $jawaban['payment_type'] ?? $payment->payment_method;
        $payment->raw_response = json_encode($jawaban);
        if ($statusColorbox === 'lunas' && ! $payment->paid_at) {
            $payment->paid_at = now();
        }
        $payment->save();

        // Status pesanan ikut menyesuaikan jawaban Midtrans
        if ($statusColorbox === 'lunas' && $pesanan->status === 'menunggu_pembayaran') {
            $pesanan->status = 'dibayar';
            $pesanan->paid_at = $pesanan->paid_at ?: now();
            $pesanan->save();

            TrackingEvent::create([
                'order_id' => $pesanan->id,
                'status' => 'dibayar',
                'description' => 'Pembayaran diterima menurut jawaban Midtrans.',
                'happened_at' => now(),
            ]);

            Notification::create([
                'user_id' => $pesanan->user_id,
                'title' => 'Pembayaran pesanan '.$pesanan->order_code.' diterima',
                'body' => 'Terima kasih, pembayaran Anda sudah kami terima dan pesanan segera diproses.',
                'url' => route('pesanan.show', $pesanan->order_code),
            ]);
        }

        if (in_array($statusColorbox, ['kedaluwarsa', 'dibatalkan', 'gagal']) && $pesanan->status === 'menunggu_pembayaran') {
            $pesanan->status = 'dibatalkan';
            $pesanan->cancelled_at = now();
            $pesanan->save();
            $this->kembalikanStok($pesanan);

            TrackingEvent::create([
                'order_id' => $pesanan->id,
                'status' => 'dibatalkan',
                'description' => 'Pesanan dibatalkan karena pembayaran '.$statusColorbox.'.',
                'happened_at' => now(),
            ]);
        }

        return back()->with('berhasil', 'Status dari Midtrans: '.($jawaban['transaction_status'] ?? '-').' (dicatat sebagai '.$statusColorbox.').');
    }

    /* ------------------------------------------------------------------ */

    /**
     * Mengembalikan stok varian ketika pesanan dibatalkan, sekaligus
     * mengembalikan poin yang dipakai dan melepas kuota vouchernya.
     */
    private function kembalikanStok(Order $pesanan): void
    {
        foreach ($pesanan->item as $baris) {
            if (! $baris->product_variant_id) {
                continue;
            }
            $varian = ProductVariant::find($baris->product_variant_id);
            if ($varian) {
                $varian->stock = $varian->stock + (int) $baris->quantity;
                $varian->save();
            }
        }

        if ($pesanan->points_used > 0 && $pesanan->pengguna) {
            $pesanan->pengguna->ubahPoin(
                (int) $pesanan->points_used,
                'pesanan',
                'Poin dikembalikan karena pesanan '.$pesanan->order_code.' dibatalkan'
            );
            $pesanan->points_used = 0;
            $pesanan->save();
        }

        if ($pesanan->voucher_id) {
            Voucher::where('id', $pesanan->voucher_id)->where('used', '>', 0)->decrement('used');
        }
    }

    /** Keterangan bawaan untuk riwayat status bila admin tidak menulis apa pun. */
    private function keteranganBawaan(string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => 'Pesanan menunggu pembayaran.',
            'dibayar' => 'Pembayaran sudah diterima.',
            'diproses' => 'Pesanan sedang disiapkan.',
            'dikemas' => 'Pesanan sedang dikemas.',
            'dikirim' => 'Pesanan sudah diserahkan kepada kurir.',
            'siap_diambil' => 'Pesanan siap diambil di toko.',
            'diterima' => 'Pesanan sudah diterima pelanggan.',
            'selesai' => 'Pesanan selesai.',
            'dibatalkan' => 'Pesanan dibatalkan.',
            default => 'Status pesanan diperbarui.',
        };
    }
}
