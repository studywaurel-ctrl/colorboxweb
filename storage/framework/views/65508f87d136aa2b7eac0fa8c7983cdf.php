<?php $__env->startSection('judul', 'Daftar Toko'); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <li>Daftar Toko</li>
        </ol>
        <h1>Daftar Toko Colorbox</h1>
        <p style="color: var(--cb-tinta-lembut); max-width: 64ch">
            Cari toko berdasarkan nama atau kota, lalu lihat jam buka dan layanan yang tersedia di sana.
        </p>
    </div>

    
    <form action="<?php echo e(route('toko.index')); ?>" method="GET" class="cb-borang cb-bagian cb-bagian--rapat">
        <div class="cb-baris-isian cb-baris-isian--dua">
            <div class="cb-isian">
                <label class="cb-label" for="cari-toko">Nama toko atau kota</label>
                <input class="cb-masukan" type="search" id="cari-toko" name="cari" value="<?php echo e($cari); ?>"
                       placeholder="Misalnya Serpong">
            </div>
            <div class="cb-isian">
                <label class="cb-label" for="layanan">Layanan</label>
                <select class="cb-masukan" id="layanan" name="layanan">
                    <option value="">Semua layanan</option>
                    <option value="ttc" <?php if($layanan === 'ttc'): ?> selected <?php endif; ?>>Tap Try and Collect</option>
                    <option value="donasi" <?php if($layanan === 'donasi'): ?> selected <?php endif; ?>>Donasi Poin</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-3 flex-wrap mt-3">
            <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--kecil">Cari toko</button>
            <a class="cb-tombol cb-tombol--polos cb-tombol--kecil" href="<?php echo e(route('toko.index')); ?>">Bersihkan</a>
        </div>
    </form>

    <section class="cb-bagian cb-bagian--rapat">
        <div class="cb-kepala-bagian">
            <div>
                <h2 style="font-size: var(--cb-h3)"><?php echo e($toko->count()); ?> toko ditemukan</h2>
                <p>Tersebar di <?php echo e($kota->count()); ?> kota.</p>
            </div>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $toko; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="cb-toko">
                <div>
                    <div class="cb-toko__nama">
                        <a href="<?php echo e(route('toko.show', $satu)); ?>"><?php echo e($satu->name); ?></a>
                    </div>
                    <div class="cb-toko__ket">
                        <?php echo e($satu->address); ?>, <?php echo e($satu->city); ?>

                        <?php if($satu->floor): ?> &middot; <?php echo e($satu->floor); ?> <?php endif; ?>
                    </div>
                    <div class="cb-toko__ket">
                        Buka <?php echo e(jam_singkat($satu->open_time)); ?> – <?php echo e(jam_singkat($satu->close_time)); ?>

                        <?php if($satu->phone): ?> &middot; <?php echo e($satu->phone); ?> <?php endif; ?>
                    </div>
                    <div class="cb-toko__layanan">
                        <span class="cb-lencana">Kode <?php echo e($satu->code); ?></span>
                        <?php if($satu->has_ttc): ?>
                            <span class="cb-lencana cb-lencana--berhasil">Tap Try and Collect</span>
                        <?php endif; ?>
                        <?php if($satu->has_donation): ?>
                            <span class="cb-lencana cb-lencana--aksen">Donasi Poin</span>
                        <?php endif; ?>
                    </div>
                </div>
                <a class="cb-tombol cb-tombol--garis cb-tombol--kecil" href="<?php echo e(route('toko.show', $satu)); ?>">Lihat toko</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="cb-kosong">
                <h2>Toko tidak ditemukan</h2>
                <p>Coba kata kunci lain atau bersihkan penyaring layanan.</p>
                <a class="cb-tombol cb-tombol--utama" href="<?php echo e(route('toko.index')); ?>">Lihat semua toko</a>
            </div>
        <?php endif; ?>
    </section>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/toko/index.blade.php ENDPATH**/ ?>