<?php $__env->startSection('judul', $produk->name); ?>

<?php $__env->startPush('gaya'); ?>
<style>
    /* Penanda warna yang sedang dipilih pada halaman produk */
    [data-warna][aria-pressed='true'] span:first-child {
        border-color: var(--cb-tinta);
        border-width: 2px;
    }
    [data-warna] { border: 0; background: transparent; padding: 0; cursor: pointer; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <?php if($produk->kategori && $produk->kategori->induk): ?>
                <li><a href="<?php echo e(route('produk.index', ['kategori' => $produk->kategori->induk->slug])); ?>"><?php echo e($produk->kategori->induk->name); ?></a></li>
            <?php endif; ?>
            <?php if($produk->kategori): ?>
                <li><a href="<?php echo e(route('produk.index', ['kategori' => $produk->kategori->slug])); ?>"><?php echo e($produk->kategori->name); ?></a></li>
            <?php endif; ?>
            <li><?php echo e($produk->name); ?></li>
        </ol>
    </div>

    <div class="cb-produk-utama">

        
        <div class="cb-galeri" id="galeri-produk">
            <?php $__empty_1 = true; $__currentLoopData = $gambar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gbr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <figure data-gambar-warna="<?php echo e($gbr->color); ?>" <?php if($gbr->color !== $warnaAwal): ?> hidden <?php endif; ?>>
                    <img src="<?php echo e(gambar_produk($gbr->path)); ?>" alt="<?php echo e($produk->name); ?> warna <?php echo e($gbr->color); ?>">
                </figure>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <figure>
                    <img src="<?php echo e(gambar_produk(null)); ?>" alt="<?php echo e($produk->name); ?>">
                </figure>
            <?php endif; ?>
        </div>

        <div class="cb-rincian">
            <h1 style="font-size: var(--cb-h2)"><?php echo e($produk->name); ?></h1>
            <p style="color: var(--cb-tinta-samar); font-size: var(--cb-mini)"><?php echo e($produk->sku); ?></p>

            <div class="cb-rincian__harga">
                <span><?php echo e(rupiah($produk->hargaJual())); ?></span>
                <?php if($produk->discount_price && $produk->discount_price < $produk->price): ?>
                    <span class="cb-produk__coret"><?php echo e(rupiah($produk->price)); ?></span>
                    <span class="cb-lencana cb-lencana--diskon">
                        Hemat <?php echo e((int) round(($produk->price - $produk->discount_price) / $produk->price * 100)); ?>%
                    </span>
                <?php endif; ?>
            </div>

            <p style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                <?php if($produk->ulasan->count() > 0): ?>
                    <i class="bi bi-star-fill" style="color: var(--cb-aksen)"></i>
                    <?php echo e(number_format($rataRating, 1, ',', '.')); ?> dari 5
                    <span style="color: var(--cb-tinta-samar)">(<?php echo e($produk->ulasan->count()); ?> ulasan)</span>
                <?php else: ?>
                    Belum ada ulasan untuk produk ini.
                <?php endif; ?>
                <?php if($produk->collection): ?>
                    &middot; <a href="<?php echo e(route('produk.index', ['koleksi' => $produk->collection])); ?>"><?php echo e($produk->collection); ?></a>
                <?php endif; ?>
            </p>

            
            <form action="<?php echo e(route('keranjang.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="warna" id="warna-terpilih" value="<?php echo e($warnaAwal); ?>">

                <?php if($warna->count() > 0): ?>
                    <div class="cb-pilihan">
                        <div class="cb-pilihan__kepala">
                            <span class="cb-pilihan__label">Warna</span>
                        </div>
                        <div class="cb-pilihan__daftar">
                            <?php $__currentLoopData = $warna; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="cb-pilih cb-pilih--warna" data-warna="<?php echo e($w->color); ?>"
                                        aria-pressed="<?php echo e($w->color === $warnaAwal ? 'true' : 'false'); ?>"
                                        title="<?php echo e($w->color); ?>" aria-label="Warna <?php echo e($w->color); ?>">
                                    <span style="background: <?php echo e($w->color_hex ?: '#e8e4de'); ?>"></span>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="cb-pilihan">
                    <div class="cb-pilihan__kepala">
                        <span class="cb-pilihan__label">Ukuran</span>
                        <a class="cb-tautan-lanjut" href="<?php echo e(route('bantuan.show', 'panduan-ukuran')); ?>">Panduan ukuran</a>
                    </div>
                    <?php
                        // Varian pertama yang stoknya masih ada pada warna awal dipilih lebih dulu
                        $varianAwal = $produk->varian->first(function ($v) use ($warnaAwal) {
                            return $v->color === $warnaAwal && $v->stock > 0;
                        });
                    ?>
                    <div class="cb-pilihan__daftar">
                        <?php $__currentLoopData = $produk->varian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $varian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="cb-pilih" data-ukuran-warna="<?php echo e($varian->color); ?>"
                                   <?php if($varian->color !== $warnaAwal): ?> hidden <?php endif; ?>>
                                <input type="radio" name="product_variant_id" value="<?php echo e($varian->id); ?>"
                                       <?php if($varian->stock < 1): ?> disabled <?php endif; ?>
                                       <?php if($varianAwal && $varianAwal->id === $varian->id): ?> checked <?php endif; ?>>
                                <span><?php echo e($varian->size); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <p class="cb-isian__bantu">Ukuran yang dicoret berarti stoknya sedang habis.</p>
                </div>

                <div class="cb-pilihan">
                    <div class="cb-pilihan__kepala">
                        <span class="cb-pilihan__label">Jumlah</span>
                    </div>
                    <div class="cb-jumlah-atur">
                        <button type="button" data-jumlah="kurang" data-medan="jumlah-produk" aria-label="Kurangi jumlah">&minus;</button>
                        <label class="cb-sr" for="jumlah-produk">Jumlah pembelian</label>
                        <input type="number" id="jumlah-produk" name="quantity" value="1" min="1" max="10">
                        <button type="button" data-jumlah="tambah" data-medan="jumlah-produk" aria-label="Tambah jumlah">+</button>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--penuh"
                            <?php if($produk->totalStok() < 1): ?> disabled <?php endif; ?>>
                        Tambah ke Keranjang
                    </button>
                </div>
            </form>

            <div class="d-grid gap-2 mt-2">
                <a class="cb-tombol cb-tombol--garis cb-tombol--penuh"
                   href="<?php echo e(route('vto.index', ['produk' => $produk->slug])); ?>">
                    <i class="bi bi-camera"></i> Coba dengan Virtual Try On
                </a>

                <form action="<?php echo e(route('wishlist.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" value="<?php echo e($produk->id); ?>">
                    <button type="submit" class="cb-tombol cb-tombol--polos cb-tombol--penuh">
                        <i class="bi bi-heart"></i> Simpan ke Wishlist
                    </button>
                </form>
            </div>

            
            <div class="cb-panel mt-4">
                <h3 style="font-size: var(--cb-h4)">Cek stok di toko</h3>
                <p style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    Pilih warna dan ukuran terlebih dahulu, lalu tekan tombol di bawah untuk melihat stok di setiap toko Colorbox.
                </p>
                <button type="button" class="cb-tombol cb-tombol--garis cb-tombol--kecil" id="cek-stok"
                        data-alamat="<?php echo e(route('produk.stok', $produk)); ?>">
                    Cek stok di toko
                </button>
                <div id="hasil-stok" class="mt-3"></div>
            </div>
        </div>
    </div>

    
    <section class="cb-bagian">
        <div class="cb-kisi cb-kisi--tiga">
            <div>
                <h3 style="font-size: var(--cb-h4)">Deskripsi</h3>
                <p style="color: var(--cb-tinta-lembut); font-size: var(--cb-kecil)">
                    <?php echo e($produk->description ?: 'Keterangan produk belum tersedia.'); ?>

                </p>
            </div>
            <div>
                <h3 style="font-size: var(--cb-h4)">Bahan</h3>
                <p style="color: var(--cb-tinta-lembut); font-size: var(--cb-kecil)">
                    <?php echo e($produk->material ?: 'Keterangan bahan belum tersedia.'); ?>

                </p>
            </div>
            <div>
                <h3 style="font-size: var(--cb-h4)">Perawatan</h3>
                <p style="color: var(--cb-tinta-lembut); font-size: var(--cb-kecil)">
                    <?php echo e($produk->care ?: 'Keterangan perawatan belum tersedia.'); ?>

                </p>
            </div>
        </div>
    </section>

    
    <section class="cb-bagian cb-bagian--rapat">
        <div class="cb-kepala-bagian">
            <div>
                <h2 style="font-size: var(--cb-h3)">Stok Setiap Varian</h2>
                <p>Jumlah stok yang siap dikirim dari gudang Colorbox.</p>
            </div>
        </div>
        <div class="cb-gulir-datar">
            <table class="cb-tabel cb-tabel--angka">
                <thead>
                <tr>
                    <th>Warna</th>
                    <th>Ukuran</th>
                    <th>Kode</th>
                    <th>Stok</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $produk->varian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $varian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <span class="cb-titik-warna" style="background: <?php echo e($varian->color_hex ?: '#e8e4de'); ?>"></span>
                            <?php echo e($varian->color); ?>

                        </td>
                        <td><?php echo e($varian->size); ?></td>
                        <td><?php echo e($varian->sku); ?></td>
                        <td>
                            <?php if($varian->stock > 0): ?>
                                <?php echo e($varian->stock); ?>

                            <?php else: ?>
                                <span class="cb-lencana">Habis</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4">Varian produk belum tersedia.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    
    <section class="cb-bagian cb-bagian--rapat">
        <div class="cb-kepala-bagian">
            <div>
                <h2 style="font-size: var(--cb-h3)">Ulasan Pelanggan</h2>
                <p>
                    <?php if($produk->ulasan->count() > 0): ?>
                        Rata-rata <?php echo e(number_format($rataRating, 1, ',', '.')); ?> dari 5 berdasarkan <?php echo e($produk->ulasan->count()); ?> ulasan.
                    <?php else: ?>
                        Ulasan ditulis pelanggan setelah pesanannya selesai.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $produk->ulasan->sortByDesc('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ulasan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="padding-block: var(--cb-s4); border-bottom: 1px solid var(--cb-garis)">
                <p class="mb-1" style="font-weight: 600; font-size: var(--cb-kecil)">
                    <?php echo e($ulasan->pengguna ? $ulasan->pengguna->name : 'Pelanggan Colorbox'); ?>

                    <span style="color: var(--cb-aksen)">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?php echo e($i <= $ulasan->rating ? 'bi-star-fill' : 'bi-star'); ?>"></i>
                        <?php endfor; ?>
                    </span>
                </p>
                <p class="mb-1" style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    <?php echo e($ulasan->comment ?: 'Pelanggan tidak menuliskan komentar.'); ?>

                </p>
                <p class="mb-0" style="font-size: var(--cb-mini); color: var(--cb-tinta-samar)">
                    <?php echo e(tanggal_id($ulasan->created_at, true)); ?>

                </p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="cb-catatan">Belum ada ulasan untuk produk ini.</p>
        <?php endif; ?>
    </section>

    
    <?php if($terkait->count() > 0): ?>
        <section class="cb-bagian">
            <div class="cb-kepala-bagian">
                <div>
                    <h2 style="font-size: var(--cb-h3)">Produk Terkait</h2>
                    <p>Pilihan lain dari kategori <?php echo e($produk->kategori ? $produk->kategori->name : 'yang sama'); ?>.</p>
                </div>
            </div>
            <div class="cb-kisi">
                <?php $__currentLoopData = $terkait; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginald18e3842edab56819b3ba4966f0edddd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald18e3842edab56819b3ba4966f0edddd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu-produk','data' => ['produk' => $lain]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu-produk'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['produk' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lain)]); ?>
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
        </section>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('skrip'); ?>
<script>
// Mengambil stok tiap toko untuk varian yang sedang dipilih.
document.addEventListener('DOMContentLoaded', function () {
    var tombol = document.getElementById('cek-stok');
    var hasil = document.getElementById('hasil-stok');
    if (!tombol || !hasil) { return; }

    tombol.addEventListener('click', function () {
        var pilihan = document.querySelector('input[name="product_variant_id"]:checked');
        if (!pilihan) {
            hasil.innerHTML = '<p class="cb-catatan">Pilih warna dan ukuran terlebih dahulu.</p>';
            return;
        }

        hasil.innerHTML = '<p class="cb-catatan">Sedang mengambil data stok toko...</p>';

        fetch(tombol.dataset.alamat + '?product_variant_id=' + pilihan.value, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (jawaban) { return jawaban.json(); })
        .then(function (data) {
            if (data.pesan) {
                hasil.innerHTML = '<p class="cb-catatan">' + data.pesan + '</p>';
                return;
            }

            hasil.innerHTML = '';

            var judul = document.createElement('p');
            judul.className = 'cb-catatan';
            judul.textContent = 'Stok untuk varian ' + data.varian + '.';
            hasil.appendChild(judul);

            data.toko.forEach(function (toko) {
                var baris = document.createElement('div');
                baris.className = 'cb-toko';

                var kiri = document.createElement('div');

                var nama = document.createElement('div');
                nama.className = 'cb-toko__nama';
                nama.textContent = toko.nama;
                kiri.appendChild(nama);

                var ket = document.createElement('div');
                ket.className = 'cb-toko__ket';
                ket.textContent = toko.kota + ' · ' + toko.lantai + ' · ' + toko.jam;
                kiri.appendChild(ket);

                if (toko.ttc) {
                    var layanan = document.createElement('div');
                    layanan.className = 'cb-toko__layanan';
                    var tanda = document.createElement('span');
                    tanda.className = 'cb-lencana';
                    tanda.textContent = 'Tap Try and Collect';
                    layanan.appendChild(tanda);
                    kiri.appendChild(layanan);
                }

                var kanan = document.createElement('span');
                kanan.className = toko.stok > 0 ? 'cb-lencana cb-lencana--berhasil' : 'cb-lencana';
                kanan.textContent = toko.keterangan;

                baris.appendChild(kiri);
                baris.appendChild(kanan);
                hasil.appendChild(baris);
            });
        })
        .catch(function () {
            hasil.innerHTML = '<p class="cb-catatan">Stok toko gagal diambil. Silakan coba lagi.</p>';
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/produk/show.blade.php ENDPATH**/ ?>