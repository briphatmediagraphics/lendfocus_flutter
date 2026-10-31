

<?php $__env->startSection('title', 'Miradi Zinazopatikana — LandFocus'); ?>

<?php $__env->startSection('content'); ?>
<section class="bg-secondary/40 py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <h1 class="text-3xl font-bold text-foreground sm:text-4xl">Miradi Zinazopatikana</h1>
        <p class="mt-2 text-muted-foreground">Chagua aina ya mradi unayohitaji</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php $filters = [null => 'Zote', 'land' => 'Ardhi', 'house' => 'Nyumba', 'farm' => 'Mashamba', 'office' => 'Ofisi']; ?>
            <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($value ? route('properties', ['type' => $value]) : route('properties')); ?>"
                   class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium <?php echo e(($type ?? null) === $value || (!$type && !$value) ? 'bg-primary text-primary-foreground' : 'border border-border bg-background hover:bg-muted'); ?>"><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <?php if($properties->isEmpty()): ?>
        <div class="rounded-2xl border border-dashed border-border p-16 text-center text-muted-foreground">
            Hakuna miradi zilizopatikana kwa kichujio hiki. Jaribu chujio lingine au wasiliana nasi.
        </div>
    <?php else: ?>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('components.property-card', ['p' => $property], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/public/properties.blade.php ENDPATH**/ ?>