<footer class="cb-kaki">
    <div class="cb-wadah">
        <div class="cb-kaki__kisi">
            <div>
                <h4>Belanja</h4>
                <ul>
                    <li><a href="<?php echo e(route('produk.baru')); ?>">New Arrival</a></li>
                    <li><a href="<?php echo e(route('produk.index')); ?>">Semua Produk</a></li>
                    <li><a href="<?php echo e(route('produk.index', ['koleksi' => 'Colorbox x Liunic'])); ?>">Koleksi Kolaborasi</a></li>
                    <li><a href="<?php echo e(route('bantuan.show', 'panduan-ukuran')); ?>">Panduan Ukuran</a></li>
                </ul>
            </div>
            <div>
                <h4>Layanan Colorbox</h4>
                <ul>
                    <li><a href="<?php echo e(route('vto.index')); ?>">Virtual Try On</a></li>
                    <li><a href="<?php echo e(route('ttc.index')); ?>">Tap Try and Collect</a></li>
                    <li><a href="<?php echo e(route('donasi.index')); ?>">Donasi Poin</a></li>
                    <li><a href="<?php echo e(route('toko.index')); ?>">Daftar Toko</a></li>
                </ul>
            </div>
            <div>
                <h4>Bantuan</h4>
                <ul>
                    <li><a href="<?php echo e(route('lacak.index')); ?>">Lacak Pesanan</a></li>
                    <li><a href="<?php echo e(route('bantuan.show', 'pengiriman')); ?>">Pengiriman</a></li>
                    <li><a href="<?php echo e(route('retur.index')); ?>">Retur dan Penukaran</a></li>
                    <li><a href="<?php echo e(route('bantuan.index')); ?>">Pusat Bantuan</a></li>
                </ul>
            </div>
            <div>
                <h4>Tentang</h4>
                <ul>
                    <li><a href="<?php echo e(route('donasi.index')); ?>">Program Donasi</a></li>
                    <li><a href="<?php echo e(route('akun.index')); ?>">Akun dan Keanggotaan</a></li>
                    <li><a href="<?php echo e(route('bantuan.show', 'tentang-colorbox')); ?>">Tentang Colorbox</a></li>
                </ul>
            </div>
        </div>
        <div class="cb-kaki__bawah">
            <span>&copy; <?php echo e(date('Y')); ?> Colorbox. Seluruh hak cipta dilindungi.</span>
            <form action="<?php echo e(route('tema.ubah')); ?>" method="POST" class="cb-pilih-kecil">
                <?php echo csrf_field(); ?>
                <button type="submit" name="tema" value="light" aria-pressed="<?php echo e(session('tema', 'light') === 'light' ? 'true' : 'false'); ?>" aria-label="Tema terang">
                    <i class="bi bi-sun"></i>
                </button>
                <button type="submit" name="tema" value="dark" aria-pressed="<?php echo e(session('tema') === 'dark' ? 'true' : 'false'); ?>" aria-label="Tema gelap">
                    <i class="bi bi-moon"></i>
                </button>
            </form>
        </div>
    </div>
</footer>
<?php /**PATH C:\colorbox_pelanggan\resources\views/partials/footer.blade.php ENDPATH**/ ?>