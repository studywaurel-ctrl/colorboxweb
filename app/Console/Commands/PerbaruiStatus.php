<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\Order;
use App\Models\TapTryCollect;
use App\Models\TrackingEvent;
use App\Models\Voucher;
use Illuminate\Console\Command;

/**
 * Pekerjaan berkala yang sederhana:
 * 1. membatalkan pesanan yang melewati batas waktu pembayaran,
 * 2. menutup jadwal Tap Try and Collect serta donasi yang tanggalnya lewat.
 *
 * Dijalankan otomatis lewat "php artisan schedule:work", atau sekali jalan
 * dengan "php artisan colorbox:perbarui".
 */
class PerbaruiStatus extends Command
{
    protected $signature = 'colorbox:perbarui';

    protected $description = 'Memperbarui status pesanan, donasi, dan Tap Try and Collect yang kedaluwarsa';

    public function handle(): int
    {
        $batasJam = (int) setting('payment_expiry_hours', 24);

        // 1. pesanan yang belum dibayar melewati batas waktu
        $pesanan = Order::where('status', 'menunggu_pembayaran')
            ->where('created_at', '<', now()->subHours($batasJam))
            ->get();

        foreach ($pesanan as $p) {
            $p->update(['status' => 'dibatalkan', 'cancelled_at' => now()]);
            $p->pembayaran?->update([
                'transaction_status' => 'expire',
                'payment_status' => 'kedaluwarsa',
            ]);
            TrackingEvent::create([
                'order_id' => $p->id,
                'status' => 'dibatalkan',
                'description' => 'Batas waktu pembayaran terlampaui',
                'happened_at' => now(),
            ]);
            // stok yang tadi dikunci dikembalikan
            foreach ($p->item as $item) {
                $item->varian?->increment('stock', $item->quantity);
            }

            // poin yang dipakai dikembalikan ke akun pelanggan
            if ($p->points_used > 0 && $p->pengguna) {
                $p->pengguna->ubahPoin(
                    (int) $p->points_used,
                    'pesanan',
                    'Poin dikembalikan karena pesanan '.$p->order_code.' kedaluwarsa'
                );
            }

            // kuota voucher dilepas kembali
            if ($p->voucher_id) {
                Voucher::where('id', $p->voucher_id)->where('used', '>', 0)->decrement('used');
            }
        }

        // 2. jadwal yang tanggalnya sudah lewat
        $ttc = TapTryCollect::where('status', 'dijadwalkan')
            ->whereHas('slot', fn ($q) => $q->whereDate('date', '<', now()->toDateString()))
            ->get();
        foreach ($ttc as $b) {
            $b->update(['status' => 'kedaluwarsa']);
        }

        $donasi = Donation::where('status', 'dijadwalkan')
            ->whereHas('slot', fn ($q) => $q->whereDate('date', '<', now()->toDateString()))
            ->get();
        foreach ($donasi as $d) {
            $d->update(['status' => 'dibatalkan', 'reject_reason' => 'Pelanggan tidak datang pada jadwalnya']);
        }

        $this->info('Pesanan kedaluwarsa: '.$pesanan->count().
            ', booking kedaluwarsa: '.$ttc->count().
            ', donasi kedaluwarsa: '.$donasi->count());

        return self::SUCCESS;
    }
}
