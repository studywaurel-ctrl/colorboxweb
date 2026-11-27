<?php $__env->startSection('judul', 'Tap Try and Collect'); ?>

<?php $__env->startPush('gaya'); ?>
<style>
    /* Daftar produk dibuat bergulir supaya formulirnya tidak terlalu panjang */
    .ttc-daftar-produk { max-height: 420px; overflow-y: auto; padding-right: var(--cb-s2); }
    .ttc-produk-baris { display: flex; gap: var(--cb-s3); padding-block: var(--cb-s3); border-bottom: 1px solid var(--cb-garis); }
    .ttc-produk-baris img { width: 64px; height: 84px; object-fit: cover; border-radius: var(--cb-sudut); }
    .ttc-jam { display: grid; gap: var(--cb-s2); grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('isi'); ?>
<div class="cb-wadah">

    <div class="cb-judul-halaman">
        <ol class="cb-remah">
            <li><a href="<?php echo e(route('beranda')); ?>">Beranda</a></li>
            <li>Tap Try and Collect</li>
        </ol>
        <h1>Tap Try and Collect</h1>
        <p style="color: var(--cb-tinta-lembut); max-width: 64ch">
            Pilih produk yang ingin Anda coba, tentukan toko dan jam kunjungan, lalu staf toko
            menyiapkannya sebelum Anda datang. Produk hanya dibayar bila Anda jadi membelinya.
        </p>
    </div>

    <section class="cb-bagian cb-bagian--rapat">
        <div class="cb-papan">
            <div class="cb-papan__butir">
                <h3>1. Pilih produk</h3>
                <p>Tandai warna dan ukuran yang ingin dicoba. Boleh lebih dari satu produk.</p>
            </div>
            <div class="cb-papan__butir">
                <h3>2. Tentukan toko dan jam</h3>
                <p>Hanya toko yang melayani Tap Try and Collect yang muncul pada daftar.</p>
            </div>
            <div class="cb-papan__butir">
                <h3>3. Coba di toko</h3>
                <p>Tunjukkan QR pada rincian booking kepada staf toko saat Anda tiba.</p>
            </div>
        </div>
    </section>

    <?php if($toko->count() < 1): ?>
        <div class="cb-kosong">
            <h2>Belum ada toko yang melayani</h2>
            <p>Layanan Tap Try and Collect belum tersedia. Silakan periksa kembali nanti.</p>
            <a class="cb-tombol cb-tombol--utama" href="<?php echo e(route('toko.index')); ?>">Lihat daftar toko</a>
        </div>
    <?php else: ?>
        <form action="<?php echo e(route('ttc.store')); ?>" method="POST" class="cb-borang cb-bagian cb-bagian--rapat">
            <?php echo csrf_field(); ?>

            
            <section>
                <h2 style="font-size: var(--cb-h3)">1. Produk yang ingin dicoba</h2>
                <p style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    Tandai warna dan ukuran yang ingin Anda coba di toko.
                </p>

                <div class="cb-panel ttc-daftar-produk">
                    <?php $__empty_1 = true; $__currentLoopData = $produk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="ttc-produk-baris">
                            <img src="<?php echo e(gambar_produk($satu->gambarUtama() ? $satu->gambarUtama()->path : null)); ?>"
                                 alt="<?php echo e($satu->name); ?>">
                            <div>
                                <div style="font-weight: 600"><?php echo e($satu->name); ?></div>
                                <div style="font-size: var(--cb-mini); color: var(--cb-tinta-lembut)">
                                    <?php echo e($satu->kategori ? $satu->kategori->name : 'Produk'); ?> &middot; <?php echo e(rupiah($satu->hargaJual())); ?>

                                </div>
                                <div class="cb-pilihan__daftar mt-2">
                                    <?php $__currentLoopData = $satu->varian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $varian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="cb-pilih">
                                            <input type="checkbox" name="product_variant_id[]" value="<?php echo e($varian->id); ?>"
                                                   <?php if($varian->id === $varianTerpilih): ?> checked <?php endif; ?>>
                                            <span><?php echo e($varian->label()); ?></span>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="mb-0" style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                            Belum ada produk yang bisa dicoba di toko.
                        </p>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="mt-5">
                <h2 style="font-size: var(--cb-h3)">2. Toko dan tanggal kunjungan</h2>

                <div class="cb-isian mt-3">
                    <label class="cb-label" for="ttc-toko">Toko tujuan</label>
                    <select class="cb-masukan" id="ttc-toko" name="toko">
                        <?php $__currentLoopData = $toko; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($satu->id); ?>">
                                <?php echo e($satu->name); ?> — <?php echo e($satu->city); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <span class="cb-isian__bantu">Hanya toko yang melayani Tap Try and Collect yang ditampilkan.</span>
                </div>

                <div class="cb-isian">
                    <span class="cb-label">Tanggal kunjungan</span>
                    <div class="cb-pilihan__daftar" id="ttc-tanggal">
                        <?php $__currentLoopData = $tanggal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nomor => $satu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="cb-pilih">
                                <input type="radio" name="tanggal" value="<?php echo e($satu); ?>" <?php if($nomor === 0): ?> checked <?php endif; ?>>
                                <span><?php echo e(tanggal_id($satu, true)); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

            
            <section class="mt-5">
                <h2 style="font-size: var(--cb-h3)">3. Jam kunjungan</h2>
                <p style="font-size: var(--cb-kecil); color: var(--cb-tinta-lembut)">
                    Sisa kuota setiap jam ditulis apa adanya. Jam yang penuh tidak dapat dipilih.
                </p>

                
                <div id="ttc-daftar-jam" class="ttc-jam" data-alamat="<?php echo e(route('ttc.slot')); ?>" aria-live="polite">
                    <p class="cb-catatan">Sedang mengambil daftar jam...</p>
                </div>
            </section>

            <div class="mt-5">
                <?php if(auth()->guard()->check()): ?>
                    <button type="submit" class="cb-tombol cb-tombol--utama">Buat jadwal Tap Try and Collect</button>
                <?php else: ?>
                    <p class="cb-catatan cb-catatan--ingat">
                        Anda perlu masuk ke akun terlebih dahulu untuk membuat jadwal.
                    </p>
                    <a class="cb-tombol cb-tombol--utama" href="<?php echo e(route('login')); ?>">Masuk ke akun</a>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('skrip'); ?>
<script>
// Mengambil daftar jam Tap Try and Collect sesuai toko dan tanggal yang dipilih.
// Slot donasi memakai halaman dan endpoint yang berbeda, jadi tidak dicampur di sini.
document.addEventListener('DOMContentLoaded', function () {
    var wadah = document.getElementById('ttc-daftar-jam');
    var pilihToko = document.getElementById('ttc-toko');
    var daftarTanggal = document.getElementById('ttc-tanggal');
    if (!wadah || !pilihToko || !daftarTanggal) { return; }

    function tanggalTerpilih() {
        var pilihan = daftarTanggal.querySelector('input[name="tanggal"]:checked');
        return pilihan ? pilihan.value : '';
    }

    function tulisPesan(teks) {
        wadah.innerHTML = '';
        var p = document.createElement('p');
        p.className = 'cb-catatan';
        p.textContent = teks;
        wadah.appendChild(p);
    }

    function ambilJam() {
        var tanggal = tanggalTerpilih();
        if (!pilihToko.value || !tanggal) { return; }

        tulisPesan('Sedang mengambil daftar jam...');

        fetch(wadah.dataset.alamat + '?toko=' + encodeURIComponent(pilihToko.value) + '&tanggal=' + encodeURIComponent(tanggal), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (jawaban) { return jawaban.json(); })
        .then(function (data) {
            if (!data.slot || data.slot.length < 1) {
                tulisPesan('Belum ada jam layanan pada tanggal tersebut. Silakan pilih tanggal lainnya.');
                return;
            }

            wadah.innerHTML = '';

            data.slot.forEach(function (slot) {
                var label = document.createElement('label');
                label.className = 'cb-kartu-pilih';

                var masukan = document.createElement('input');
                masukan.type = 'radio';
                masukan.name = 'tap_try_collect_slot_id';
                masukan.value = slot.id;
                masukan.disabled = slot.penuh;

                var isi = document.createElement('span');
                isi.className = 'cb-kartu-pilih__isi';

                var tanda = document.createElement('span');
                tanda.className = 'cb-kartu-pilih__tanda';

                var teks = document.createElement('span');

                var judul = document.createElement('span');
                judul.className = 'cb-kartu-pilih__judul';
                judul.textContent = slot.jam;

                var ket = document.createElement('span');
                ket.className = 'cb-kartu-pilih__ket d-block';
                // Tulisannya jelas, tidak hanya mengandalkan warna
                ket.textContent = slot.penuh ? 'Penuh' : (slot.sisa + ' slot tersisa');

                teks.appendChild(judul);
                teks.appendChild(ket);
                isi.appendChild(tanda);
                isi.appendChild(teks);
                label.appendChild(masukan);
                label.appendChild(isi);

                if (slot.penuh) {
                    label.style.opacity = '0.55';
                    label.style.cursor = 'not-allowed';
                }

                wadah.appendChild(label);
            });
        })
        .catch(function () {
            tulisPesan('Daftar jam gagal diambil. Silakan coba lagi.');
        });
    }

    pilihToko.addEventListener('change', ambilJam);
    daftarTanggal.addEventListener('change', ambilJam);
    ambilJam();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/ttc/index.blade.php ENDPATH**/ ?>