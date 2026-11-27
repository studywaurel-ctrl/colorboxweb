<?php $__env->startSection('judul', 'Beranda'); ?>

<?php $__env->startSection('isi'); ?>

    
    <section class="cb-sorot">
        <div class="cb-wadah">
            <div class="cb-sorot__isi">
                <p class="cb-lencana cb-lencana--aksen mb-3">Koleksi Colorbox</p>
                <h1>Temukan Gaya Favoritmu di Colorbox</h1>
                <p>Eksplor koleksi fashion terbaru yang siap melengkapi gaya sehari-harimu.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="cb-tombol cb-tombol--utama" href="<?php echo e(route('produk.index')); ?>">Belanja Sekarang</a>
                    <a class="cb-tombol cb-tombol--garis" href="<?php echo e(route('produk.baru')); ?>">Lihat Koleksi</a>
                </div>
            </div>
        </div>
    </section>

    
    <section class="cb-bagian">
        <div class="cb-wadah">
            <div class="cb-kepala-bagian">
                <div>
                    <h2>New Arrival</h2>
                    <p>Koleksi yang baru saja masuk ke katalog Colorbox.</p>
                </div>
                <a class="cb-tautan-lanjut" href="<?php echo e(route('produk.baru')); ?>">Lihat semua</a>
            </div>

            <?php if($produkBaru->count() > 0): ?>
                <div class="cb-kisi">
                    <?php $__currentLoopData = $produkBaru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginald18e3842edab56819b3ba4966f0edddd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald18e3842edab56819b3ba4966f0edddd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu-produk','data' => ['produk' => $produk]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu-produk'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['produk' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($produk)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald18e3842edab56819b3ba4966f0edddd)): ?>
<?php $attributes = $__attributesOriginald18e3842edab56819b3ba4966f0edddd; ?>
<?php unset($__attributesOriginald18e3842edab56819b3ba4966f0edddd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald18e3842edab56819b3ba4966f0edddd)): ?>
<?php $component = $__componentOriginald18e3842edab56819b3ba4966f0edddd; ?>
<?php unset($__componentOriginald18e3842edab56819b3ba4966f0edddd); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="cb-catatan">Belum ada produk baru untuk ditampilkan.</p>
            <?php endif; ?>
        </div>
    </section>

    
    <?php if($kategoriPilihan->count() > 0): ?>
        <section class="cb-bagian cb-bagian--lembut">
            <div class="cb-wadah">
                <div class="cb-kepala-bagian">
                    <div>
                        <h2>Kategori Pilihan</h2>
                        <p>Mulai dari kategori yang paling sering dicari pelanggan.</p>
                    </div>
                    <a class="cb-tautan-lanjut" href="<?php echo e(route('produk.index')); ?>">Semua produk</a>
                </div>

                <div class="cb-saring__kiri">
                    <?php $__currentLoopData = $kategoriPilihan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="cb-cip" href="<?php echo e(route('produk.index', ['kategori' => $kategori->slug])); ?>"><?php echo e($kategori->name); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    
    <section class="cb-bagian">
        <div class="cb-wadah">
            <div class="cb-kepala-bagian">
                <div>
                    <h2>Layanan Colorbox</h2>
                    <p>Layanan yang menghubungkan belanja lewat situs dengan toko Colorbox.</p>
                </div>
            </div>

            <div class="cb-papan">
                <div class="cb-papan__butir">
                    <h3>Virtual Try On</h3>
                    <p>Lihat gambaran pakaian sebelum Anda memutuskan untuk membelinya.</p>
                    <a class="cb-tautan-lanjut" href="<?php echo e(route('vto.index')); ?>">Mulai mencoba</a>
                </div>
                <div class="cb-papan__butir">
                    <h3>Tap Try and Collect</h3>
                    <p>Pesan lewat situs, coba dan ambil barangnya di toko pilihan Anda tanpa ongkos kirim.</p>
                    <a class="cb-tautan-lanjut" href="<?php echo e(route('ttc.index')); ?>">Cara kerjanya</a>
                </div>
                <div class="cb-papan__butir">
                    <h3>Donasi Poin</h3>
                    <p>Serahkan pakaian layak pakai di toko dan tukarkan poinnya menjadi potongan belanja.</p>
                    <a class="cb-tautan-lanjut" href="<?php echo e(route('donasi.index')); ?>">Jadwalkan donasi</a>
                </div>
                <div class="cb-papan__butir">
                    <h3>Daftar Toko</h3>
                    <p>Cari alamat, jam buka, dan layanan yang tersedia di setiap toko Colorbox.</p>
                    <a class="cb-tautan-lanjut" href="<?php echo e(route('toko.index')); ?>">Lihat daftar toko</a>
                </div>
            </div>
        </div>
    </section>

    
    <?php if($produkKolaborasi->count() > 0): ?>
        <section class="cb-bagian cb-bagian--lembut">
            <div class="cb-wadah">
                <div class="cb-kepala-bagian">
                    <div>
                        <h2>Koleksi Kolaborasi</h2>
                        <p>
                            <?php if($namaKoleksi->count() > 0): ?>
                                Koleksi khusus hasil kerja sama: <?php echo e($namaKoleksi->implode(', ')); ?>.
                            <?php else: ?>
                                Koleksi khusus hasil kerja sama Colorbox.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if($namaKoleksi->count() > 0): ?>
                        <a class="cb-tautan-lanjut" href="<?php echo e(route('produk.index', ['koleksi' => $namaKoleksi->first()])); ?>">Lihat koleksinya</a>
                    <?php endif; ?>
                </div>

                <div class="cb-kisi">
                    <?php $__currentLoopData = $produkKolaborasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginald18e3842edab56819b3ba4966f0edddd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald18e3842edab56819b3ba4966f0edddd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu-produk','data' => ['produk' => $produk]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu-produk'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['produk' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($produk)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald18e3842edab56819b3ba4966f0edddd)): ?>
<?php $attributes = $__attributesOriginald18e3842edab56819b3ba4966f0edddd; ?>
<?php unset($__attributesOriginald18e3842edab56819b3ba4966f0edddd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald18e3842edab56819b3ba4966f0edddd)): ?>
<?php $component = $__componentOriginald18e3842edab56819b3ba4966f0edddd; ?>
<?php unset($__componentOriginald18e3842edab56819b3ba4966f0edddd); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/beranda.blade.php ENDPATH**/ ?>