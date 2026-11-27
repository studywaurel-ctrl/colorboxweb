<?php $__env->startSection('judul', 'Donasi '.$donasi->donation_code); ?>

<?php $__env->startPush('gaya'); ?>
<style>
    .donasi-qr { display: flex; justify-content: center; min-height: 200px; }
    .donasi-qr svg, .donasi-qr img { width: 200px; height: 200px; }
    .donasi-kode { font-size: 1.9rem; font-weight: 700; letter-spacing: 0.08em; text-align: center; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('akun.index')); ?>">Akun Saya</a></li>
            <li><a href="<?php echo e(route('donasi.riwayat')); ?>">Riwayat Donasi</a></li>
            <li><?php echo e($donasi->donation_code); ?></li>
        </ol>
        <div class="d-flex justify-content-between gap-3 flex-wrap align-items-center">
            <h1>Jadwal Donasi</h1>
            <span class="cb-lencana
                <?php if($donasi->status === 'disetujui'): ?> cb-lencana--berhasil
                <?php elseif($donasi->status === 'dijadwalkan' || $donasi->status === 'check_in'): ?> cb-lencana--ingat
                <?php endif; ?>">
                <?php echo e(ucfirst(str_replace('_', ' ', $donasi->status))); ?>

            </span>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-7">

            <section class="cb-panel">
                <h2 style="font-size: var(--cb-h4)">Rincian jadwal</h2>
                <ul class="cb-rincian-biaya">
                    <li><span>Toko</span><strong><?php echo e($donasi->toko ? $donasi->toko->name : '-'); ?></strong></li>
                    <li><span>Alamat</span><strong><?php echo e($donasi->toko ? $donasi->toko->address.', '.$donasi->toko->city : '-'); ?></strong></li>
                    <li><span>Tanggal</span><strong><?php echo e($donasi->slot ? tanggal_id($donasi->slot->date, true) : '-'); ?></strong></li>
                    <li><span>Jam</span><strong>
                        <?php if($donasi->slot): ?>
                            <?php echo e(jam_singkat($donasi->slot->start_time)); ?> - <?php echo e(jam_singkat($donasi->slot->end_time)); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </strong></li>
                    <li><span>Perkiraan jumlah</span><strong><?php echo e($donasi->estimated_items); ?> potong</strong></li>
                    <?php if($donasi->condition_note): ?>
                        <li><span>Catatan kondisi</span><strong><?php echo e($donasi->condition_note); ?></strong></li>
                    <?php endif; ?>
                    <?php if($donasi->checked_in_at): ?>
                        <li><span>Check-in</span><strong><?php echo e(tanggal_id($donasi->checked_in_at, true)); ?></strong></li>
                    <?php endif; ?>
                    <?php if($donasi->received_items !== null): ?>
                        <li><span>Diterima staf</span><strong><?php echo e($donasi->received_items); ?> potong</strong></li>
                        <li class="cb-jumlah"><span>Poin diberikan</span><strong><?php echo e($donasi->points_awarded); ?> poin</strong></li>
                    <?php endif; ?>
                    <?php if($donasi->verified_by): ?>
                        <li><span>Diperiksa staf</span><strong><?php echo e($donasi->verified_by); ?></strong></li>
                    <?php endif; ?>
                    <?php if($donasi->reject_reason): ?>
                        <li><span>Alasan ditolak</span><strong><?php echo e($donasi->reject_reason); ?></strong></li>
                    <?php endif; ?>
                </ul>
            </section>

            <div class="d-flex gap-3 flex-wrap mt-4">
                <?php if($donasi->status === 'dijadwalkan' && $donasi->slot && $donasi->slot->date->toDateString() === now()->toDateString()): ?>
                    <form action="<?php echo e(route('donasi.check-in', $donasi)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--kecil">
                            <i class="bi bi-geo-alt"></i> Saya sudah di toko
                        </button>
                    </form>
                <?php endif; ?>

                <?php if(in_array($donasi->status, ['dijadwalkan', 'check_in'])): ?>
                    <form action="<?php echo e(route('donasi.batal', $donasi)); ?>" method="POST"
                          data-konfirmasi="Batalkan jadwal donasi ini?">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <button type="submit" class="cb-tombol cb-tombol--garis cb-tombol--kecil">
                            <i class="bi bi-x-circle"></i> Batalkan jadwal
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if($donasi->status === 'dijadwalkan'): ?>
                <p class="cb-catatan mt-4">
                    Tombol "Saya sudah di toko" hanya bisa ditekan pada hari jadwal donasi Anda.
                </p>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <div class="cb-panel">
                <h2 style="font-size: var(--cb-h4)">Kode penyerahan</h2>
                <p class="donasi-kode"><?php echo e($donasi->donation_code); ?></p>
                <p style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    Staf toko memindai QR berikut untuk membuka halaman verifikasi donasi Anda.
                </p>

                
                <div class="donasi-qr" id="gambar-qr" data-kode="<?php echo e($alamatStaf); ?>"></div>

                <p class="mt-3 mb-0" style="font-size: var(--cb-mini); color: var(--cb-tinta-samar); word-break: break-all">
                    <?php echo e($alamatStaf); ?>

                </p>
            </div>

            <div class="cb-panel mt-4">
                <h2 style="font-size: var(--cb-h4)">Perhitungan poin</h2>
                <p class="mb-0" style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    Setiap potong pakaian yang diterima staf bernilai <?php echo e($poinPerPotong); ?> poin.
                    Poin masuk setelah staf toko selesai memeriksa pakaian Anda.
                </p>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('skrip'); ?>
<script src="<?php echo e(asset('vendor/qrcode/qrcode.js')); ?>"></script>
<script>
// Menggambar QR dari tautan verifikasi staf yang disimpan pada atribut data-kode.
document.addEventListener('DOMContentLoaded', function () {
    var wadah = document.getElementById('gambar-qr');
    if (!wadah) { return; }

    var kode = wadah.dataset.kode;
    if (!kode) { return; }

    var qr = qrcode(0, 'M');
    qr.addData(kode);
    qr.make();
    wadah.innerHTML = qr.createSvgTag(5, 2);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/donasi/show.blade.php ENDPATH**/ ?>