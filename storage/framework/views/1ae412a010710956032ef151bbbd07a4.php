<?php
    // Jumlah kecil di ikon keranjang dan wishlist. Dihitung sederhana di sini
    // supaya setiap halaman memperlihatkan angka terbaru.
    $jumlahKeranjang = 0;
    $jumlahWishlist = 0;
    if (auth()->check()) {
        $keranjang = \App\Models\Cart::where('user_id', auth()->id())->first();
        $jumlahKeranjang = $keranjang ? $keranjang->item()->sum('quantity') : 0;
        $jumlahWishlist = \App\Models\Wishlist::where('user_id', auth()->id())->count();
    }
    $kategoriMenu = \App\Models\Category::whereNull('parent_id')->orderBy('sort_order')->get();
?>

<header class="cb-kepala">
    <div class="cb-pita">
        Gratis ongkos kirim untuk belanja mulai <?php echo e(rupiah(setting('free_shipping_threshold', 350000))); ?>.
        <a href="<?php echo e(route('bantuan.show', 'pengiriman')); ?>">Selengkapnya</a>
    </div>

    <div class="cb-wadah">
        <div class="cb-kepala__baris">
            <a class="cb-merek" href="<?php echo e(route('beranda')); ?>">
                <img src="<?php echo e(asset('images/logo/colorbox-wordmark.png')); ?>" alt="Colorbox" height="26">
            </a>

            <nav class="cb-nav" aria-label="Menu utama">
                <a class="cb-nav__tautan" href="<?php echo e(route('beranda')); ?>" <?php if(request()->routeIs('beranda')): ?> aria-current="page" <?php endif; ?>>Beranda</a>
                <a class="cb-nav__tautan" href="<?php echo e(route('produk.baru')); ?>" <?php if(request()->routeIs('produk.baru')): ?> aria-current="page" <?php endif; ?>>New Arrival</a>
                <a class="cb-nav__tautan" href="<?php echo e(route('produk.index')); ?>" <?php if(request()->routeIs('produk.index')): ?> aria-current="page" <?php endif; ?>>Semua Produk</a>
                <a class="cb-nav__tautan" href="<?php echo e(route('vto.index')); ?>" <?php if(request()->routeIs('vto.*')): ?> aria-current="page" <?php endif; ?>>Virtual Try On</a>
                <a class="cb-nav__tautan" href="<?php echo e(route('ttc.index')); ?>" <?php if(request()->routeIs('ttc.*')): ?> aria-current="page" <?php endif; ?>>Tap Try and Collect</a>
                <a class="cb-nav__tautan" href="<?php echo e(route('donasi.index')); ?>" <?php if(request()->routeIs('donasi.*')): ?> aria-current="page" <?php endif; ?>>Donasi Poin</a>
                <a class="cb-nav__tautan" href="<?php echo e(route('toko.index')); ?>" <?php if(request()->routeIs('toko.*')): ?> aria-current="page" <?php endif; ?>>Daftar Toko</a>
            </nav>

            <div class="cb-aksi">
                <button type="button" class="cb-ikon" id="buka-cari" aria-expanded="false" aria-label="Cari produk">
                    <i class="bi bi-search"></i>
                </button>
                <a class="cb-ikon" href="<?php echo e(route('wishlist.index')); ?>" aria-label="Wishlist">
                    <i class="bi bi-heart"></i>
                    <?php if($jumlahWishlist > 0): ?><span class="cb-lencana-jumlah"><?php echo e($jumlahWishlist); ?></span><?php endif; ?>
                </a>
                <a class="cb-ikon" href="<?php echo e(route('keranjang.index')); ?>" aria-label="Keranjang belanja">
                    <i class="bi bi-bag"></i>
                    <?php if($jumlahKeranjang > 0): ?><span class="cb-lencana-jumlah"><?php echo e($jumlahKeranjang); ?></span><?php endif; ?>
                </a>
                <a class="cb-ikon" href="<?php echo e(auth()->check() ? route('akun.index') : route('login')); ?>" aria-label="Akun saya">
                    <i class="bi bi-person"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="cb-subnav">
        <div class="cb-wadah">
            <div class="cb-subnav__isi">
                <?php $__currentLoopData = $kategoriMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('produk.index', ['kategori' => $kategori->slug])); ?>"><?php echo e($kategori->name); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('produk.index', ['koleksi' => 'Colorbox x Liunic'])); ?>">Kolaborasi</a>
                <a href="<?php echo e(route('bantuan.show', 'panduan-ukuran')); ?>">Panduan Ukuran</a>
                <a href="<?php echo e(route('lacak.index')); ?>">Lacak Pesanan</a>
                <a href="<?php echo e(route('bantuan.index')); ?>">Bantuan</a>
            </div>
        </div>
    </div>

    <div class="cb-cari" id="kotak-cari">
        <div class="cb-wadah">
            <form class="cb-cari__kotak" action="<?php echo e(route('produk.index')); ?>" method="GET">
                <i class="bi bi-search"></i>
                <label class="cb-sr" for="kata-cari">Kata kunci pencarian</label>
                <input type="search" id="kata-cari" name="cari" value="<?php echo e(request('cari')); ?>"
                       placeholder="Cari produk, misalnya kemeja linen" autocomplete="off">
                <button type="submit" class="cb-tombol cb-tombol--kecil cb-tombol--utama">Cari</button>
                <button type="button" class="cb-ikon" id="tutup-cari" aria-label="Tutup pencarian">
                    <i class="bi bi-x-lg"></i>
                </button>
            </form>
            <div class="cb-cari__saran">
                <div>
                    <p class="cb-cari__judul">Kategori</p>
                    <ul class="cb-cari__daftar">
                        <?php $__currentLoopData = $kategoriMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('produk.index', ['kategori' => $kategori->slug])); ?>"><?php echo e($kategori->name); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <div>
                    <p class="cb-cari__judul">Pencarian populer</p>
                    <ul class="cb-cari__daftar">
                        <li><a href="<?php echo e(route('produk.index', ['cari' => 'shirt'])); ?>">Kemeja</a></li>
                        <li><a href="<?php echo e(route('produk.index', ['cari' => 'jeans'])); ?>">Jeans</a></li>
                        <li><a href="<?php echo e(route('produk.index', ['cari' => 'skirt'])); ?>">Rok</a></li>
                        <li><a href="<?php echo e(route('produk.index', ['cari' => 'bag'])); ?>">Tas</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
<?php /**PATH C:\colorbox_pelanggan\resources\views/partials/header.blade.php ENDPATH**/ ?>