<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['produk', 'disukai' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['produk', 'disukai' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Kartu produk dipakai di beranda, katalog, wishlist, dan halaman toko.
    // Penanda wishlist dihitung di sini bila tidak dikirim dari luar.
    if ($disukai === null) {
        $disukai = auth()->check()
            ? \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $produk->id)->exists()
            : false;
    }

    $gambarUtama = $produk->gambarUtama();
    $adaDiskon = $produk->discount_price && $produk->discount_price < $produk->price;
    $hemat = $adaDiskon ? (int) round(($produk->price - $produk->discount_price) / $produk->price * 100) : 0;
    $stok = $produk->totalStok();
?>

<article class="cb-produk">
    <form action="<?php echo e(route('wishlist.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?php echo e($produk->id); ?>">
        <button type="submit" class="cb-suka" aria-pressed="<?php echo e($disukai ? 'true' : 'false'); ?>"
                aria-label="Simpan <?php echo e($produk->name); ?> ke wishlist">
            <i class="bi <?php echo e($disukai ? 'bi-heart-fill' : 'bi-heart'); ?>"></i>
        </button>
    </form>

    <?php if($adaDiskon): ?>
        <span class="cb-lencana cb-lencana--diskon cb-lencana--tempel">Hemat <?php echo e($hemat); ?>%</span>
    <?php elseif($produk->is_new): ?>
        <span class="cb-lencana cb-lencana--baru cb-lencana--tempel">Baru</span>
    <?php endif; ?>

    <a href="<?php echo e(route('produk.show', $produk)); ?>" aria-label="<?php echo e($produk->name); ?>">
        <span class="cb-produk__gambar">
            <img src="<?php echo e(gambar_produk($gambarUtama ? $gambarUtama->path : null)); ?>" alt="<?php echo e($produk->name); ?>" loading="lazy">
            <?php if($stok < 1): ?>
                <span class="cb-produk__kosong">Stok habis</span>
            <?php endif; ?>
        </span>
    </a>

    <p class="cb-produk__kategori"><?php echo e($produk->kategori ? $produk->kategori->name : 'Colorbox'); ?></p>
    <h3 class="cb-produk__nama"><a href="<?php echo e(route('produk.show', $produk)); ?>"><?php echo e($produk->name); ?></a></h3>
    <p class="cb-produk__harga">
        <span><?php echo e(rupiah($produk->hargaJual())); ?></span>
        <?php if($adaDiskon): ?>
            <span class="cb-produk__coret"><?php echo e(rupiah($produk->price)); ?></span>
        <?php endif; ?>
    </p>

    <?php if($produk->varian->count() > 0): ?>
        <div class="cb-produk__warna">
            <?php $__currentLoopData = $produk->varian->unique('color')->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $varian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="cb-titik-warna" style="background: <?php echo e($varian->color_hex ?: '#e8e4de'); ?>" title="<?php echo e($varian->color); ?>"></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</article>
<?php /**PATH C:\colorbox_pelanggan\resources\views/components/kartu-produk.blade.php ENDPATH**/ ?>