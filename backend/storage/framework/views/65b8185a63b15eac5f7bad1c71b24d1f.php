<?php if(!empty($gallery) && count($gallery) > 0): ?>
<section class="bg-secondary/10 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl"><?php echo e($lang === 'en' ? 'Event Gallery' : 'Gallery ya Tukio'); ?></h2>
            <p class="mt-2 text-muted-foreground"><?php echo e($lang === 'en' ? 'Photos and videos from our latest company events.' : 'Picha na video kutoka matukio yetu ya kampuni.'); ?></p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $gallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="overflow-hidden rounded-3xl border border-border bg-card shadow-soft">
                    <?php if($media['type'] === 'video'): ?>
                        <video controls class="h-72 w-full object-cover" preload="metadata">
                            <source src="<?php echo e($media['url']); ?>" type="video/<?php echo e(pathinfo($media['url'], PATHINFO_EXTENSION)); ?>">
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <img src="<?php echo e($media['url']); ?>" alt="<?php echo e($media['type']); ?>" class="h-72 w-full object-cover" loading="lazy" />
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/components/event-gallery.blade.php ENDPATH**/ ?>