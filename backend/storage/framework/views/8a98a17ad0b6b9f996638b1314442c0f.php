<?php
    $nav = [
        ['url' => route('home'), 'label' => $t['nav']['home'] ?? 'Mwanzo'],
        ['url' => route('services'), 'label' => $t['nav']['services'] ?? 'Huduma'],
        ['url' => route('properties'), 'label' => $t['nav']['properties'] ?? 'Miradi'],
        ['url' => route('book'), 'label' => $t['nav']['book'] ?? 'Ombi la Miradi'],
        ['url' => route('about'), 'label' => $t['nav']['about'] ?? 'Kuhusu'],
        ['url' => route('contact'), 'label' => $t['nav']['contact'] ?? 'Mawasiliano'],
    ];
    $current = request()->path() ?: '/';
?>

<header class="sticky top-0 z-40 border-b border-border/60 bg-background/80 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2">
            <img src="<?php echo e(asset('images/landfocus-logo.png')); ?>" alt="LandFocus" class="h-9 w-9" width="36" height="36">
            <span class="text-lg font-bold text-foreground">Land<span class="text-primary">Focus</span></span>
        </a>

        <nav class="hidden items-center gap-6 md:flex">
            <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $active = rtrim(parse_url($item['url'], PHP_URL_PATH) ?? '', '/') === rtrim($current, '/') || ($current === '/' && $item['url'] === route('home')); ?>
                <a href="<?php echo e($item['url']); ?>" class="text-sm font-medium transition-colors <?php echo e($active ? 'text-primary' : 'text-muted-foreground hover:text-primary'); ?>"><?php echo e($item['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="hidden md:flex items-center gap-2">
            <a href="<?php echo e(route('lang', $lang === 'sw' ? 'en' : 'sw')); ?>" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="Language">
                🌐 <?php echo e(strtoupper($lang)); ?>

            </a>
            <?php if($isAdmin): ?>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"><?php echo e($t['nav']['dashboard'] ?? 'Dashboard'); ?></a>
            <?php else: ?>
                <a href="<?php echo e(route('admin.login')); ?>" class="inline-flex items-center rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium hover:bg-muted"><?php echo e($t['nav']['login'] ?? 'Ingia'); ?></a>
            <?php endif; ?>
        </div>

        <button id="mobile-menu-btn" class="md:hidden rounded-md p-2 text-foreground" aria-label="Menu">☰</button>
    </div>

    <div id="mobile-menu" class="hidden border-t border-border bg-background md:hidden">
        <div class="space-y-1 px-4 py-3">
            <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($item['url']); ?>" class="block rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-muted"><?php echo e($item['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between rounded-md px-3 py-2">
                <span class="text-sm font-medium"><?php echo e($t['lang']['sw'] ?? 'Kiswahili'); ?> / <?php echo e($t['lang']['en'] ?? 'English'); ?></span>
                <a href="<?php echo e(route('lang', $lang === 'sw' ? 'en' : 'sw')); ?>" class="text-sm font-medium text-primary"><?php echo e(strtoupper($lang)); ?></a>
            </div>
            <a href="<?php echo e($isAdmin ? route('admin.dashboard') : route('admin.login')); ?>" class="block rounded-md px-3 py-2 text-sm font-medium text-primary hover:bg-muted">
                <?php echo e($isAdmin ? ($t['nav']['dashboard'] ?? 'Dashboard') : ($t['nav']['login'] ?? 'Ingia')); ?>

            </a>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/components/header.blade.php ENDPATH**/ ?>