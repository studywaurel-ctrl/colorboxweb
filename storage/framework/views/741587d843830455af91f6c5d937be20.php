<?php if(session('berhasil') || session('ingat') || session('gagal') || $errors->any()): ?>
    <div class="cb-wadah" style="padding-top: var(--cb-s4)">
        <?php if(session('berhasil')): ?>
            <p class="cb-catatan cb-catatan--berhasil"><?php echo e(session('berhasil')); ?></p>
        <?php endif; ?>
        <?php if(session('ingat')): ?>
            <p class="cb-catatan cb-catatan--ingat"><?php echo e(session('ingat')); ?></p>
        <?php endif; ?>
        <?php if(session('gagal')): ?>
            <p class="cb-catatan"><?php echo e(session('gagal')); ?></p>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="cb-catatan">
                <ul class="mb-0 ps-3">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pesan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($pesan); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\colorbox_pelanggan\resources\views/partials/pesan.blade.php ENDPATH**/ ?>