<?php $__env->startSection('judul', 'Masuk ke Akun'); ?>

<?php $__env->startSection('isi'); ?>
    <div class="cb-wadah">
        <div class="row justify-content-center cb-bagian">
            <div class="col-lg-5 col-md-7">
                <h1 class="cb-judul-halaman">Masuk ke Akun</h1>
                <p class="cb-isian__bantu">
                    Masuk untuk menyelesaikan pembayaran, melihat riwayat pesanan, menjadwalkan
                    donasi, dan mengajukan retur.
                </p>

                <form class="cb-borang mt-4" action="<?php echo e(route('login.proses')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="cb-isian">
                        <label class="cb-label" for="email">Email</label>
                        <input class="cb-masukan" type="email" id="email" name="email"
                               value="<?php echo e(old('email')); ?>" autocomplete="email" required>
                    </div>

                    <div class="cb-isian">
                        <label class="cb-label" for="password">Kata sandi</label>
                        <input class="cb-masukan" type="password" id="password" name="password"
                               autocomplete="current-password" required>
                    </div>

                    <div class="cb-isian">
                        <label class="cb-pilihan__label" for="ingat">
                            <input type="checkbox" id="ingat" name="ingat" value="1"
                                   <?php if(old('ingat')): ?> checked <?php endif; ?>>
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="cb-tombol cb-tombol--utama cb-tombol--penuh">Masuk</button>
                </form>

                <p class="mt-3" style="font-size: var(--cb-kecil)">
                    <a href="<?php echo e(route('password.request')); ?>">Lupa kata sandi?</a>
                </p>

                <hr>

                <p class="text-center mb-0" style="font-size: var(--cb-kecil)">
                    Belum punya akun Colorbox?
                    <a href="<?php echo e(route('register')); ?>">Daftar sekarang</a>
                </p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\colorbox_pelanggan\resources\views/auth/masuk.blade.php ENDPATH**/ ?>