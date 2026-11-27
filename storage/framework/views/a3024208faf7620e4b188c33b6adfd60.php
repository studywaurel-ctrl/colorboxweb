<?php $__env->startSection('judul', 'Daftar Akun Baru'); ?>

<?php $__env->startSection('isi'); ?>
    <div class="cb-wadah">
        <div class="row justify-content-center cb-bagian">
            <div class="col-lg-5 col-md-7">
                <h1 class="cb-judul-halaman">Daftar Akun Baru</h1>
                <p class="cb-isian__bantu">
                    Buat akun Colorbox untuk menyimpan alamat pengiriman, mengumpulkan poin donasi,
                    dan memesan layanan Tap Try and Collect.
                </p>

                <form class="cb-borang mt-4" action="<?php echo e(route('register.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="cb-isian">
                        <label class="cb-label" for="name">Nama lengkap</label>
                        <input class="cb-masukan" type="text" id="name" name="name"
                               value="<?php echo e(old('name')); ?>" autocomplete="name" required>
                    </div>

                    <div class="cb-isian">
                        <label class="cb-label" for="email">Email</label>
                        <input class="cb-masukan" type="email" id="email" name="email"
                               value="<?php echo e(old('email')); ?>" autocomplete="email" required>
                    </div>

                    <div class="cb-isian">
                        <label class="cb-label" for="phone">Nomor telepon (boleh dikosongkan)</label>
                        <input class="cb-masukan" type="tel" id="phone" name="phone"
                               value="<?php echo e(old('phone')); ?>" autocomplete="tel" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="cb-isian">
                        <label class="cb-label" for="password">Kata sandi</label>
                        <input class="cb-masukan" type="password" id="password" name="password"
                               autocomplete="new-password" required>
                        <span class="cb-isian__bantu">Paling sedikit 8 karakter.</span>
                    </div>

                    <div class="cb-isian">
                        <label class="cb-label" for="password_confirmation">Ulangi kata sandi</label>
                        <input class="cb-masukan" type="password" id="password_confirmation"
                               name="password_confirmation" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--penuh">Daftar</button>
                </form>

                <hr>

                <p class="text-center mb-0" style="font-size: var(--cb-kecil)">
                    Sudah punya akun? <a href="<?php echo e(route('login')); ?>">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/auth/daftar.blade.php ENDPATH**/ ?>