<?php $__env->startSection('judul', $hanyaBaru ? 'New Arrival' : 'Semua Produk'); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <li><?php echo e($hanyaBaru ? 'New Arrival' : 'Semua Produk'); ?></li>
        </ol>
        <h1>
            <?php if($kategoriTerpilih): ?>
                <?php echo e($kategoriTerpilih->name); ?>

            <?php else: ?>
                <?php echo e($hanyaBaru ? 'New Arrival' : 'Semua Produk'); ?>

            <?php endif; ?>
        </h1>
        <p style="color: var(--cb-tinta-lembut); max-width: 64ch">
            <?php echo e($produk->total()); ?> produk ditemukan.
            <?php if($hanyaBaru): ?> Halaman ini hanya menampilkan koleksi yang baru masuk. <?php endif; ?>
        </p>
    </div>

    
    <div class="cb-saring">
        <div class="cb-saring__kiri">
            <button type="button" class="cb-cip" id="buka-saring" aria-expanded="false">
                <i class="bi bi-sliders"></i> Saring
            </button>

            <?php if(request('cari')): ?>
                <a class="cb-cip aktif" href="<?php echo e(request()->fullUrlWithoutQuery(['cari', 'page'])); ?>">
                    "<?php echo e(request('cari')); ?>" <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
            <?php if($kategoriTerpilih): ?>
                <a class="cb-cip aktif" href="<?php echo e(request()->fullUrlWithoutQuery(['kategori', 'page'])); ?>">
                    <?php echo e($kategoriTerpilih->name); ?> <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
            <?php if(request('koleksi')): ?>
                <a class="cb-cip aktif" href="<?php echo e(request()->fullUrlWithoutQuery(['koleksi', 'page'])); ?>">
                    <?php echo e(request('koleksi')); ?> <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
            <?php if(request('warna')): ?>
                <a class="cb-cip aktif" href="<?php echo e(request()->fullUrlWithoutQuery(['warna', 'page'])); ?>">
                    <?php echo e(request('warna')); ?> <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
            <?php if(request('ukuran')): ?>
                <a class="cb-cip aktif" href="<?php echo e(request()->fullUrlWithoutQuery(['ukuran', 'page'])); ?>">
                    Ukuran <?php echo e(request('ukuran')); ?> <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </div>

        <form action="<?php echo e($hanyaBaru ? route('produk.baru') : route('produk.index')); ?>" method="GET"
              class="d-flex align-items-center gap-2">
            
            <input type="hidden" name="cari" value="<?php echo e(request('cari')); ?>">
            <input type="hidden" name="kategori" value="<?php echo e(request('kategori')); ?>">
            <input type="hidden" name="koleksi" value="<?php echo e(request('koleksi')); ?>">
            <input type="hidden" name="warna" value="<?php echo e(request('warna')); ?>">
            <input type="hidden" name="ukuran" value="<?php echo e(request('ukuran')); ?>">
            <input type="hidden" name="harga_min" value="<?php echo e(request('harga_min')); ?>">
            <input type="hidden" name="harga_max" value="<?php echo e(request('harga_max')); ?>">

            <label class="cb-sr" for="urut">Urutkan</label>
            <select class="cb-masukan" id="urut" name="urut" style="min-height: 38px; width: auto"
                    onchange="this.form.submit()">
                <option value="terbaru" <?php if($urut === 'terbaru'): ?> selected <?php endif; ?>>Terbaru</option>
                <option value="termurah" <?php if($urut === 'termurah'): ?> selected <?php endif; ?>>Harga terendah</option>
                <option value="termahal" <?php if($urut === 'termahal'): ?> selected <?php endif; ?>>Harga tertinggi</option>
                <option value="nama" <?php if($urut === 'nama'): ?> selected <?php endif; ?>>Nama A – Z</option>
            </select>
            <noscript><button type="submit" class="cb-tombol cb-tombol--kecil cb-tombol--garis">Urutkan</button></noscript>
        </form>
    </div>

    
    <div class="cb-laci" id="laci-saring">
        <form action="<?php echo e($hanyaBaru ? route('produk.baru') : route('produk.index')); ?>" method="GET" class="cb-borang">
            <input type="hidden" name="urut" value="<?php echo e($urut); ?>">

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="cb-isian">
                        <label class="cb-label" for="f-cari">Kata kunci</label>
                        <input class="cb-masukan" type="search" id="f-cari" name="cari" value="<?php echo e(request('cari')); ?>"
                               placeholder="Misalnya kemeja linen">
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="cb-isian">
                        <label class="cb-label" for="f-kategori">Kategori</label>
                        <select class="cb-masukan" id="f-kategori" name="kategori">
                            <option value="">Semua kategori</option>
                            <?php $__currentLoopData = $kategoriInduk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $induk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($induk->slug); ?>" <?php if(request('kategori') === $induk->slug): ?> selected <?php endif; ?>><?php echo e($induk->name); ?></option>
                                <?php $__currentLoopData = $induk->anak->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anak): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($anak->slug); ?>" <?php if(request('kategori') === $anak->slug): ?> selected <?php endif; ?>>&nbsp;&nbsp;<?php echo e($anak->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="cb-isian">
                        <label class="cb-label" for="f-koleksi">Koleksi</label>
                        <select class="cb-masukan" id="f-koleksi" name="koleksi">
                            <option value="">Semua koleksi</option>
                            <?php $__currentLoopData = $daftarKoleksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $koleksi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($koleksi); ?>" <?php if(request('koleksi') === $koleksi): ?> selected <?php endif; ?>><?php echo e($koleksi); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="cb-isian">
                        <label class="cb-label" for="f-warna">Warna</label>
                        <select class="cb-masukan" id="f-warna" name="warna">
                            <option value="">Semua warna</option>
                            <?php $__currentLoopData = $daftarWarna; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($warna); ?>" <?php if(request('warna') === $warna): ?> selected <?php endif; ?>><?php echo e($warna); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="cb-isian">
                        <label class="cb-label" for="f-ukuran">Ukuran tersedia</label>
                        <select class="cb-masukan" id="f-ukuran" name="ukuran">
                            <option value="">Semua ukuran</option>
                            <?php $__currentLoopData = $daftarUkuran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ukuran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ukuran); ?>" <?php if(request('ukuran') === $ukuran): ?> selected <?php endif; ?>><?php echo e($ukuran); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="cb-baris-isian cb-baris-isian--dua">
                        <div class="cb-isian">
                            <label class="cb-label" for="f-harga-min">Harga dari</label>
                            <input class="cb-masukan" type="number" id="f-harga-min" name="harga_min" min="0"
                                   value="<?php echo e(request('harga_min')); ?>" placeholder="0">
                        </div>
                        <div class="cb-isian">
                            <label class="cb-label" for="f-harga-max">Harga sampai</label>
                            <input class="cb-masukan" type="number" id="f-harga-max" name="harga_max" min="0"
                                   value="<?php echo e(request('harga_max')); ?>" placeholder="500000">
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex gap-3 flex-wrap">
                    <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--kecil">Terapkan saringan</button>
                    <a class="cb-tombol cb-tombol--polos cb-tombol--kecil"
                       href="<?php echo e($hanyaBaru ? route('produk.baru') : route('produk.index')); ?>">Bersihkan</a>
                </div>
            </div>
        </form>
    </div>

    <section class="cb-bagian cb-bagian--rapat">
        <?php if($produk->count() > 0): ?>
            <div class="cb-kisi">
                <?php $__currentLoopData = $produk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginald18e3842edab56819b3ba4966f0edddd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald18e3842edab56819b3ba4966f0edddd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu-produk','data' => ['produk' => $satu]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu-produk'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['produk' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($satu)]); ?>
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

            <div class="mt-5">
                <?php echo e($produk->links()); ?>

            </div>
        <?php else: ?>
            <div class="cb-kosong">
                <h2>Produk tidak ditemukan</h2>
                <p>Coba ubah kata kunci atau bersihkan saringan yang sedang dipakai.</p>
                <a class="cb-tombol cb-tombol--utama" href="<?php echo e(route('produk.index')); ?>">Lihat semua produk</a>
            </div>
        <?php endif; ?>
    </section>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/produk/index.blade.php ENDPATH**/ ?>