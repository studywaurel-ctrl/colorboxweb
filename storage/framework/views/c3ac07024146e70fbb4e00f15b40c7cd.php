<?php $__env->startSection('judul', 'Keranjang Belanja'); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <li>Keranjang Belanja</li>
        </ol>
        <h1>Keranjang Belanja</h1>
        <p style="color: var(--cb-tinta-lembut); max-width: 64ch">
            Centang barang yang ingin dibayar sekarang. Barang yang tidak dicentang tetap tersimpan di keranjang.
        </p>
    </div>

    <?php if($daftar->count() > 0): ?>
        <div class="row g-5">
            <div class="col-lg-8">

                <div class="d-flex gap-2 flex-wrap mb-3">
                    <form action="<?php echo e(route('keranjang.pilih-semua')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pilih" value="1">
                        <button type="submit" class="cb-tombol cb-tombol--garis cb-tombol--kecil">
                            <i class="bi bi-check2-square"></i> Centang semua
                        </button>
                    </form>
                    <form action="<?php echo e(route('keranjang.pilih-semua')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pilih" value="0">
                        <button type="submit" class="cb-tombol cb-tombol--polos cb-tombol--kecil">
                            <i class="bi bi-square"></i> Lepas semua centang
                        </button>
                    </form>
                </div>

                <?php $__currentLoopData = $daftar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $baris): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $varian = $baris->varian;
                        $produk = $varian ? $varian->produk : null;
                        $harga = $produk ? $produk->hargaJual() : 0;
                    ?>
                    <div class="cb-barang">
                        <div class="cb-barang__gambar">
                            <?php if($produk): ?>
                                <a href="<?php echo e(route('produk.show', $produk)); ?>">
                                    <img src="<?php echo e(gambar_produk($produk->gambarUtama()?->path)); ?>" alt="<?php echo e($produk->name); ?>">
                                </a>
                            <?php else: ?>
                                <span class="cb-produk__kosong">Produk tidak tersedia</span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="d-flex gap-2 align-items-start">
                                <form action="<?php echo e(route('keranjang.pilih', $baris)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="cb-tombol cb-tombol--polos cb-tombol--kecil"
                                            aria-label="<?php echo e($baris->selected ? 'Lepas centang barang ini' : 'Centang barang ini'); ?>">
                                        <i class="bi <?php echo e($baris->selected ? 'bi-check-square-fill' : 'bi-square'); ?>"></i>
                                    </button>
                                </form>
                                <div>
                                    <h2 class="cb-barang__nama mb-1">
                                        <?php if($produk): ?>
                                            <a href="<?php echo e(route('produk.show', $produk)); ?>"><?php echo e($produk->name); ?></a>
                                        <?php else: ?>
                                            Produk tidak tersedia
                                        <?php endif; ?>
                                    </h2>
                                    <p class="cb-barang__ket mb-1">
                                        <?php echo e($varian ? $varian->label() : '-'); ?>

                                        <?php if($varian): ?>
                                            · Sisa stok <?php echo e($varian->stock); ?>

                                        <?php endif; ?>
                                    </p>
                                    <p class="mb-0" style="font-weight: 600"><?php echo e(rupiah($harga)); ?></p>
                                </div>
                            </div>

                            <div class="cb-barang__aksi">
                                <form action="<?php echo e(route('keranjang.update', $baris)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <div class="cb-jumlah-atur">
                                        <button type="button" data-jumlah="kurang" data-medan="jml-<?php echo e($baris->id); ?>"
                                                data-kirim="ya" aria-label="Kurangi jumlah">&minus;</button>
                                        <label class="cb-sr" for="jml-<?php echo e($baris->id); ?>">Jumlah</label>
                                        <input type="number" id="jml-<?php echo e($baris->id); ?>" name="quantity"
                                               value="<?php echo e($baris->quantity); ?>" min="1"
                                               max="<?php echo e($varian ? max($varian->stock, 1) : 1); ?>">
                                        <button type="button" data-jumlah="tambah" data-medan="jml-<?php echo e($baris->id); ?>"
                                                data-kirim="ya" aria-label="Tambah jumlah">+</button>
                                    </div>
                                    <button type="submit" class="cb-tombol cb-tombol--polos cb-tombol--kecil mt-2">
                                        Perbarui jumlah
                                    </button>
                                </form>

                                <form action="<?php echo e(route('keranjang.destroy', $baris)); ?>" method="POST"
                                      data-konfirmasi="Keluarkan barang ini dari keranjang?">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="cb-tombol cb-tombol--polos cb-tombol--kecil">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-end" style="font-weight: 600"><?php echo e(rupiah($harga * $baris->quantity)); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <a class="cb-tombol cb-tombol--polos mt-4 d-inline-block" href="<?php echo e(route('produk.index')); ?>">
                    <i class="bi bi-arrow-left"></i> Lanjut berbelanja
                </a>
            </div>

            <div class="col-lg-4">
                <div class="cb-panel cb-menempel">
                    <h2 style="font-size: var(--cb-h4)">Ringkasan Belanja</h2>

                    <ul class="cb-rincian-biaya">
                        <li><span><?php echo e($jumlahDipilih); ?> barang dicentang</span><span><?php echo e(rupiah($subtotal)); ?></span></li>
                        <li class="cb-jumlah"><span>Subtotal</span><span><?php echo e(rupiah($subtotal)); ?></span></li>
                    </ul>

                    <?php if($subtotal >= $batasGratisOngkir): ?>
                        <p class="cb-catatan cb-catatan--berhasil mt-3">
                            Belanja Anda sudah memenuhi syarat gratis ongkos kirim.
                        </p>
                    <?php else: ?>
                        <p class="cb-catatan cb-catatan--ingat mt-3">
                            Tambah belanja <?php echo e(rupiah($batasGratisOngkir - $subtotal)); ?> lagi untuk mendapat gratis ongkos kirim.
                        </p>
                    <?php endif; ?>

                    <?php if($subtotal > 0): ?>
                        <?php if(auth()->guard()->check()): ?>
                            <a class="cb-tombol cb-tombol--utama cb-tombol--penuh mt-4" href="<?php echo e(route('checkout.index')); ?>">
                                Lanjut ke pembayaran
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->guard()->guest()): ?>
                            <a class="cb-tombol cb-tombol--utama cb-tombol--penuh mt-4" href="<?php echo e(route('login')); ?>">
                                Masuk dulu untuk melanjutkan
                            </a>
                            <p class="mt-3 mb-0" style="font-size: var(--cb-mini); color: var(--cb-tinta-samar)">
                                Isi keranjang Anda tetap tersimpan setelah masuk ke akun.
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="cb-catatan mt-4 mb-0">Centang minimal satu barang untuk melanjutkan ke pembayaran.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="cb-kosong">
            <h2>Keranjang masih kosong</h2>
            <p>Belum ada barang di keranjang Anda. Lihat koleksi terbaru Colorbox dan pilih yang Anda suka.</p>
            <a class="cb-tombol cb-tombol--utama" href="<?php echo e(route('produk.index')); ?>">Mulai belanja</a>
        </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/keranjang/index.blade.php ENDPATH**/ ?>