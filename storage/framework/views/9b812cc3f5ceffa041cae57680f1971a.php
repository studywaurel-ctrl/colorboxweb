<?php $__env->startSection('judul', 'Lacak Pesanan'); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <li>Lacak Pesanan</li>
        </ol>
        <h1>Lacak Pesanan</h1>
        <p style="color: var(--cb-tinta-lembut); max-width: 64ch">
            Masukkan kode pesanan yang Anda terima setelah memesan, contohnya CBX-20260101-0001.
        </p>
    </div>

    <section class="cb-bagian cb-bagian--rapat">
        <form action="<?php echo e(route('lacak.cari')); ?>" method="POST" class="cb-borang" style="max-width: 520px">
            <?php echo csrf_field(); ?>
            <div class="cb-isian">
                <label class="cb-label" for="kode">Kode pesanan</label>
                <input class="cb-masukan" type="text" id="kode" name="kode" required maxlength="30"
                       value="<?php echo e(old('kode', $kode)); ?>" placeholder="CBX-20260101-0001">
                <p class="cb-isian__bantu">Kode pesanan juga tercantum pada halaman rincian pesanan di akun Anda.</p>
            </div>
            <button type="submit" class="cb-tombol cb-tombol--utama">Lacak Pesanan</button>
        </form>
    </section>

    <?php if($pesanan): ?>
        <section class="cb-bagian cb-bagian--rapat">
            <div class="cb-kepala-bagian">
                <div>
                    <h2 style="font-size: var(--cb-h3)">Pesanan <?php echo e($pesanan->order_code); ?></h2>
                    <p>Dibuat pada <?php echo e(tanggal_id($pesanan->created_at, true)); ?>.</p>
                </div>
                <span class="cb-lencana cb-lencana--aksen"><?php echo e($pesanan->labelStatus()); ?></span>
            </div>

            <div class="cb-kisi cb-kisi--dua">
                <div class="cb-panel">
                    <h3 style="font-size: var(--cb-h4)">Keterangan pesanan</h3>
                    <ul class="cb-rincian-biaya">
                        <li><span>Cara menerima</span>
                            <span><?php echo e($pesanan->method === 'ambil_toko' ? 'Diambil di toko' : 'Dikirim kurir'); ?></span></li>
                        <?php if($pesanan->toko): ?>
                            <li><span>Toko pengambilan</span><span><?php echo e($pesanan->toko->name); ?></span></li>
                        <?php endif; ?>
                        <?php if($pesanan->pengiriman && $pesanan->pengiriman->tracking_number): ?>
                            <li><span>Nomor resi</span><span><?php echo e($pesanan->pengiriman->tracking_number); ?></span></li>
                        <?php endif; ?>
                        <?php if($pesanan->paid_at): ?>
                            <li><span>Dibayar pada</span><span><?php echo e(tanggal_id($pesanan->paid_at, true)); ?></span></li>
                        <?php endif; ?>
                        <li class="cb-jumlah"><span>Total</span><span><?php echo e(rupiah($pesanan->total)); ?></span></li>
                    </ul>
                </div>

                <div class="cb-panel">
                    <h3 style="font-size: var(--cb-h4)">Barang dalam pesanan</h3>
                    <?php $__empty_1 = true; $__currentLoopData = $pesanan->item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <p class="mb-2" style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                            <?php echo e($barang->product_name); ?>

                            <span style="color: var(--cb-tinta-samar)">
                                (<?php echo e($barang->color); ?> / <?php echo e($barang->size); ?>) &times; <?php echo e($barang->quantity); ?>

                            </span>
                        </p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="mb-0" style="font-size: var(--cb-kecil)">Rincian barang tidak tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="cb-bagian cb-bagian--rapat">
            <div class="cb-kepala-bagian">
                <div>
                    <h2 style="font-size: var(--cb-h3)">Riwayat Status</h2>
                    <p>Setiap perubahan status pesanan dicatat di sini.</p>
                </div>
            </div>

            <?php if($riwayat->count() > 0): ?>
                <ol class="cb-langkah">
                    <?php $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catatan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="<?php echo e($loop->first ? 'tercapai' : ''); ?>">
                            <p class="cb-langkah__judul mb-1"><?php echo e(ucfirst(str_replace('_', ' ', $catatan->status))); ?></p>
                            <p class="mb-1" style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                                <?php echo e($catatan->description); ?>

                            </p>
                            <p class="cb-langkah__waktu mb-0">
                                <?php echo e(tanggal_id($catatan->happened_at, true)); ?>

                                <?php if($catatan->happened_at): ?> pukul <?php echo e($catatan->happened_at->format('H:i')); ?> <?php endif; ?>
                            </p>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            <?php else: ?>
                <p class="cb-catatan">Belum ada catatan riwayat untuk pesanan ini.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/lacak/index.blade.php ENDPATH**/ ?>