<?php $__env->startSection('judul', 'Virtual Try On '.$produk->name); ?>

<?php $__env->startPush('gaya'); ?>
<style>
    /* Dua gambar berdampingan pada hasil pratinjau */
    .vto-banding { display: grid; gap: var(--cb-s3); grid-template-columns: 1fr; }
    @media (min-width: 576px) {
        .vto-banding { grid-template-columns: 1fr 1fr; }
    }
    .vto-banding figure { margin: 0; }
    .vto-banding img {
        width: 100%;
        aspect-ratio: 3 / 4;
        object-fit: cover;
        border-radius: var(--cb-sudut);
        background: var(--cb-latar-lembut);
    }
    .vto-banding figcaption {
        font-size: var(--cb-mini);
        color: var(--cb-tinta-lembut);
        margin-top: var(--cb-s2);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <li><a href="<?php echo e(route('vto.index')); ?>">Virtual Try On</a></li>
            <li><?php echo e($produk->name); ?></li>
        </ol>
        <h1>Virtual Try On</h1>
        <p style="color: var(--cb-tinta-lembut); max-width: 64ch">
            Berdiri menghadap kamera, masukkan tubuh Anda ke dalam bingkai panduan, lalu tekan "Ambil Gambar".
        </p>
    </div>

    <div class="cb-vto">

        
        <div>
            <div class="cb-panel">
                <h2 style="font-size: var(--cb-h4)">Produk yang dicoba</h2>

                <div class="cb-barang">
                    <div class="cb-barang__gambar">
                        <img src="<?php echo e(gambar_produk($produk->gambarUtama() ? $produk->gambarUtama()->path : null)); ?>"
                             alt="<?php echo e($produk->name); ?>">
                    </div>
                    <div>
                        <div class="cb-barang__nama"><?php echo e($produk->name); ?></div>
                        <div class="cb-barang__ket">
                            Warna <?php echo e($sesi->color ?: '-'); ?> &middot; <?php echo e(rupiah($produk->hargaJual())); ?>

                        </div>
                        <a class="cb-tombol cb-tombol--polos cb-tombol--kecil" href="<?php echo e(route('produk.show', $produk)); ?>">
                            Buka halaman produk
                        </a>
                    </div>
                </div>

                
                <div class="cb-pilihan mt-4" id="vto-ukuran">
                    <div class="cb-pilihan__kepala">
                        <span class="cb-pilihan__label">Ukuran</span>
                        <a class="cb-tautan-lanjut" href="<?php echo e(route('bantuan.show', 'panduan-ukuran')); ?>">Panduan ukuran</a>
                    </div>
                    <div class="cb-pilihan__daftar">
                        <?php
                            // Hanya ukuran pada warna yang sedang dicoba
                            $varianWarna = $produk->varian->where('color', $sesi->color);
                            if ($varianWarna->count() < 1) {
                                $varianWarna = $produk->varian;
                            }
                        ?>
                        <?php $__currentLoopData = $varianWarna; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nomor => $varian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="cb-pilih">
                                <input type="radio" name="ukuran_pilihan" value="<?php echo e($varian->size); ?>"
                                       data-varian="<?php echo e($varian->id); ?>"
                                       <?php if($sesi->size === $varian->size || ($nomor === 0 && ! $sesi->size)): ?> checked <?php endif; ?>>
                                <span><?php echo e($varian->size); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <p class="cb-isian__bantu">Ukuran yang dipilih ikut ditulis pada hasil pratinjau.</p>
                </div>
            </div>

            
            <?php if($sesi->photo_path): ?>
                <div class="cb-panel cb-vto__hasil mt-4">
                    <h2 style="font-size: var(--cb-h4)">Hasil pratinjau</h2>

                    <div class="vto-banding">
                        <figure>
                            <img src="<?php echo e(asset($sesi->photo_path)); ?>" alt="Foto tangkapan kamera Anda">
                            <figcaption>Foto Anda</figcaption>
                        </figure>
                        <figure>
                            <img src="<?php echo e(gambar_produk($produk->gambarUtama() ? $produk->gambarUtama()->path : null)); ?>"
                                 alt="<?php echo e($produk->name); ?>">
                            <figcaption>
                                <?php echo e($produk->name); ?> &middot; warna <?php echo e($sesi->color ?: '-'); ?>

                                &middot; ukuran <?php echo e($sesi->size ?: 'belum dipilih'); ?>

                            </figcaption>
                        </figure>
                    </div>

                    <p class="cb-catatan mt-3">
                        <?php echo e($sesi->note ?: 'Pratinjau lokal: foto Anda disandingkan dengan foto produk.'); ?>

                    </p>

                    <div class="d-grid gap-2 mt-3">
                        <form action="<?php echo e(route('keranjang.store')); ?>" method="POST" id="borang-keranjang">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_variant_id" id="keranjang-varian" value="">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--penuh">
                                <i class="bi bi-bag"></i> Tambah ke Keranjang
                            </button>
                        </form>

                        <?php if(auth()->guard()->check()): ?>
                            <form action="<?php echo e(route('wishlist.store')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo e($produk->id); ?>">
                                <button type="submit" class="cb-tombol cb-tombol--polos cb-tombol--penuh">
                                    <i class="bi bi-heart"></i> Simpan ke Wishlist
                                </button>
                            </form>
                        <?php else: ?>
                            <a class="cb-tombol cb-tombol--polos cb-tombol--penuh" href="<?php echo e(route('login')); ?>">
                                <i class="bi bi-heart"></i> Masuk untuk menyimpan ke wishlist
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-4" action="<?php echo e(route('vto.destroy', $sesi)); ?>" method="POST"
                  data-konfirmasi="Hapus sesi Virtual Try On ini beserta gambarnya?">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="cb-tombol cb-tombol--garis cb-tombol--kecil">
                    <i class="bi bi-trash"></i> Hapus sesi ini
                </button>
            </form>
        </div>

        
        <div>
            <div class="cb-vto__panggung" id="vto-panggung">
                <video id="vto-video" playsinline muted hidden></video>
                <img id="vto-tangkapan" alt="Hasil tangkapan kamera" hidden>
                <div class="cb-bingkai" id="vto-bingkai">
                    <span class="cb-bingkai__ket" id="vto-ket-bingkai">Posisikan tubuh di dalam frame</span>
                </div>
            </div>

            <canvas id="vto-kanvas" hidden></canvas>

            <div class="d-flex gap-2 flex-wrap mt-5">
                <button type="button" class="cb-tombol cb-tombol--utama cb-tombol--kecil" id="vto-nyalakan">
                    <i class="bi bi-camera-video"></i> Nyalakan kamera
                </button>
                <button type="button" class="cb-tombol cb-tombol--garis cb-tombol--kecil" id="vto-siap" hidden>
                    Saya sudah siap
                </button>
                <button type="button" class="cb-tombol cb-tombol--garis cb-tombol--kecil" id="vto-ambil" hidden>
                    <i class="bi bi-camera"></i> Ambil Gambar
                </button>
            </div>

            <p class="cb-catatan mt-3" id="vto-keadaan">
                Kamera belum menyala. Tekan "Nyalakan kamera", lalu izinkan pemakaian kamera pada peramban Anda.
            </p>

            
            <form action="<?php echo e(route('vto.proses', $sesi)); ?>" method="POST" id="borang-tangkapan" hidden>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="gambar" id="vto-data-gambar" value="">
                <input type="hidden" name="ukuran" id="vto-ukuran-tangkapan" value="">
            </form>

            
            <div class="cb-panel mt-4" id="vto-cadangan">
                <h2 style="font-size: var(--cb-h4)">Tidak bisa memakai kamera?</h2>
                <p style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    Unggah foto seluruh badan dari galeri Anda. Foto berdiri tegak menghadap kamera memberi
                    hasil pratinjau yang paling rapi.
                </p>
                <form action="<?php echo e(route('vto.proses', $sesi)); ?>" method="POST" enctype="multipart/form-data" class="cb-borang">
                    <?php echo csrf_field(); ?>
                    <div class="cb-isian">
                        <label class="cb-label" for="vto-berkas">Foto Anda (JPG atau PNG)</label>
                        <input class="cb-masukan" type="file" id="vto-berkas" name="berkas" accept="image/jpeg,image/png">
                        <span class="cb-isian__bantu">Ukuran paling besar <?php echo e(number_format($maksKb / 1024, 0, ',', '.')); ?> MB.</span>
                    </div>
                    <input type="hidden" name="ukuran" id="vto-ukuran-unggah" value="">
                    <button type="submit" class="cb-tombol cb-tombol--garis cb-tombol--kecil">Unggah foto</button>
                </form>
            </div>

            <p class="cb-catatan cb-catatan--aksen mt-4">
                Halaman ini tidak memakai kecerdasan buatan. Pencocokan pakaian pada tubuh masih berupa pratinjau
                lokal dan siap disambungkan ke layanan virtual try-on lewat pengaturan
                <code>colorbox.try_on.mode</code> (preview atau api). Mode sekarang: <strong><?php echo e($mode); ?></strong>.
            </p>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('skrip'); ?>
<script>
// Kamera, bingkai panduan posisi tubuh, dan pengambilan gambar Virtual Try On.
document.addEventListener('DOMContentLoaded', function () {
    var panggung = document.getElementById('vto-panggung');
    var video = document.getElementById('vto-video');
    var tangkapan = document.getElementById('vto-tangkapan');
    var kanvas = document.getElementById('vto-kanvas');
    var bingkai = document.getElementById('vto-bingkai');
    var ketBingkai = document.getElementById('vto-ket-bingkai');
    var tombolNyalakan = document.getElementById('vto-nyalakan');
    var tombolSiap = document.getElementById('vto-siap');
    var tombolAmbil = document.getElementById('vto-ambil');
    var keadaan = document.getElementById('vto-keadaan');
    var borangTangkapan = document.getElementById('borang-tangkapan');
    var dataGambar = document.getElementById('vto-data-gambar');

    if (!panggung || !video || !tombolNyalakan) { return; }

    // Ukuran yang dipilih disalin ke seluruh formulir di halaman ini
    var kotakUkuran = document.getElementById('vto-ukuran');
    var ukuranTangkapan = document.getElementById('vto-ukuran-tangkapan');
    var ukuranUnggah = document.getElementById('vto-ukuran-unggah');
    var keranjangVarian = document.getElementById('keranjang-varian');

    function salinUkuran() {
        if (!kotakUkuran) { return; }
        var pilihan = kotakUkuran.querySelector('input[name="ukuran_pilihan"]:checked');
        if (!pilihan) { return; }

        if (ukuranTangkapan) { ukuranTangkapan.value = pilihan.value; }
        if (ukuranUnggah) { ukuranUnggah.value = pilihan.value; }
        if (keranjangVarian) { keranjangVarian.value = pilihan.dataset.varian; }
    }

    if (kotakUkuran) {
        kotakUkuran.addEventListener('change', salinUkuran);
    }
    salinUkuran();

    function tulisKeadaan(teks) {
        if (keadaan) { keadaan.textContent = teks; }
    }

    function posisiSudahSesuai() {
        bingkai.classList.add('pas');
        ketBingkai.textContent = 'Posisi sudah sesuai';
        if (tombolSiap) { tombolSiap.hidden = true; }
    }

    tombolNyalakan.addEventListener('click', function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            tulisKeadaan('Peramban ini belum mendukung kamera. Silakan unggah foto pada bagian di bawah.');
            return;
        }

        tulisKeadaan('Menunggu izin kamera dari peramban Anda...');

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function (aliran) {
                video.srcObject = aliran;
                video.hidden = false;
                if (tangkapan) { tangkapan.hidden = true; }
                video.play();

                tombolNyalakan.hidden = true;
                if (tombolSiap) { tombolSiap.hidden = false; }
                if (tombolAmbil) { tombolAmbil.hidden = false; }

                tulisKeadaan('Kamera menyala. Berdiri tegak sampai tubuh Anda masuk ke dalam bingkai.');

                // Setelah beberapa detik bingkai dianggap sudah pas, atau pengguna menekan tombol siap
                window.setTimeout(posisiSudahSesuai, 4000);
            })
            .catch(function () {
                tulisKeadaan('Izin kamera ditolak atau kamera tidak tersedia. Silakan unggah foto pada bagian di bawah.');
            });
    });

    if (tombolSiap) {
        tombolSiap.addEventListener('click', posisiSudahSesuai);
    }

    if (tombolAmbil) {
        tombolAmbil.addEventListener('click', function () {
            if (!video.videoWidth) {
                tulisKeadaan('Kamera belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }

            // Frame video digambar ke canvas, lalu dikirim sebagai data URL
            kanvas.width = video.videoWidth;
            kanvas.height = video.videoHeight;
            kanvas.getContext('2d').drawImage(video, 0, 0, kanvas.width, kanvas.height);

            if (tangkapan) {
                tangkapan.src = kanvas.toDataURL('image/png');
                tangkapan.hidden = false;
            }
            video.hidden = true;

            if (video.srcObject) {
                video.srcObject.getTracks().forEach(function (jalur) { jalur.stop(); });
            }

            tombolAmbil.hidden = true;
            tulisKeadaan('Sedang diproses...');

            salinUkuran();
            dataGambar.value = kanvas.toDataURL('image/png');
            borangTangkapan.submit();
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/vto/show.blade.php ENDPATH**/ ?>