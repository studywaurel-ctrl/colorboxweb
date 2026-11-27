<!doctype html>
<html lang="id" data-tema="<?php echo e(session('tema', 'light')); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('judul', 'Colorbox'); ?> — Colorbox</title>
    <link rel="icon" href="<?php echo e(asset('images/logo/favicon.png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap-icons/bootstrap-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php echo $__env->yieldPushContent('gaya'); ?>
</head>
<body>
<a class="cb-lompat" href="#isi">Lompat ke isi utama</a>

<?php echo $__env->make('partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main id="isi">
    <?php echo $__env->make('partials.pesan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldContent('isi'); ?>
</main>

<?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script src="<?php echo e(asset('vendor/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/app.js')); ?>"></script>
<?php echo $__env->yieldPushContent('skrip'); ?>
</body>
</html>
<?php /**PATH C:\colorbox_pelanggan\resources\views/layouts/app.blade.php ENDPATH**/ ?>