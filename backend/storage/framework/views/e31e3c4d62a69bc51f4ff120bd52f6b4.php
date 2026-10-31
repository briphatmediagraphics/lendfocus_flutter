<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'LandFocus — Ardhi, Nyumba & Mashamba Tanzania'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Karibu LandFocus — Kampuni inayoongoza katika kuuza ardhi, nyumba, mashamba na ofisi Tanzania.'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <?php echo $__env->make('components.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(session('success')): ?>
        <div class="fixed top-20 right-4 z-50 max-w-sm rounded-2xl border border-border bg-card px-4 py-3 text-sm shadow-soft" role="alert">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="fixed top-20 right-4 z-50 max-w-sm rounded-2xl border border-destructive/30 bg-card px-4 py-3 text-sm text-destructive shadow-soft" role="alert">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.chat-fab', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/layouts/app.blade.php ENDPATH**/ ?>