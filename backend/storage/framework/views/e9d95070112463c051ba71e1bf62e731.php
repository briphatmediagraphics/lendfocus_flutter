

<?php $__env->startSection('title', 'Dashboard — LandFocus Admin'); ?>

<?php $__env->startSection('content'); ?>
<h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
<p class="mt-1 text-muted-foreground">Simamia miradi, maombi, timu na ripoti kutoka kituo kimoja.</p>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php $__currentLoopData = [
        ['label' => 'Miradi', 'value' => $stats['properties'], 'url' => route('admin.properties')],
        ['label' => 'Imeuzwa', 'value' => $stats['sold'], 'url' => route('admin.properties')],
        ['label' => 'Maombi', 'value' => $stats['inquiries'], 'url' => route('admin.inquiries')],
        ['label' => 'Maombi Mapya', 'value' => $stats['newInquiries'], 'url' => route('admin.inquiries')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($card['url']); ?>" class="rounded-2xl border border-border bg-card p-5 shadow-card transition hover:border-primary/30">
            <p class="text-sm text-muted-foreground"><?php echo e($card['label']); ?></p>
            <p class="mt-2 text-3xl font-bold text-primary"><?php echo e($card['value']); ?></p>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php $__currentLoopData = [
        ['title' => 'Miradi', 'desc' => 'Simamia orodha, upatikanaji na miradi maalum.', 'url' => route('admin.properties')],
        ['title' => 'Maombi', 'desc' => 'Kagua ujumbe wa wateja na badilisha kuwa leads.', 'url' => route('admin.inquiries')],
        ['title' => 'Kalenda', 'desc' => 'Matukio rasmi na ratiba za shughuli.', 'url' => route('admin.calendar')],
        ['title' => 'Mfuatilio wa Kazi', 'desc' => 'Ratibu kazi za ziara na ukaguzi.', 'url' => route('admin.work-orders')],
        ['title' => 'Timu', 'desc' => 'Simamia wafanyakazi na majukumu.', 'url' => route('admin.team')],
        ['title' => 'Ripoti', 'desc' => 'Tazama takwimu za shughuli na miradi.', 'url' => route('admin.reports')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
            <h3 class="font-semibold text-foreground"><?php echo e($mod['title']); ?></h3>
            <p class="mt-2 text-sm text-muted-foreground"><?php echo e($mod['desc']); ?></p>
            <a href="<?php echo e($mod['url']); ?>" class="mt-4 inline-flex rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">Fungua</a>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>