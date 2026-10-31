<?php if(!empty($events) && count($events) > 0): ?>
<section class="bg-secondary/30 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mb-8 text-center">
            <span class="mb-3 inline-flex rounded-full bg-secondary px-3 py-1 text-xs font-semibold"><?php echo e($lang === 'en' ? 'Official' : 'Rasmi'); ?></span>
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">
                <?php echo e($lang === 'en' ? 'Official Events & Announcements' : 'Matukio Rasmi na Matangazo'); ?>

            </h2>
            <p class="mt-2 text-muted-foreground">
                <?php echo e($lang === 'en' ? 'Upcoming activities and announcements from LandFocus' : 'Shughuli na matangazo yajayo kutoka LandFocus'); ?>

            </p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $events->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="rounded-2xl border border-border bg-card p-5 shadow-card transition hover:border-primary/30">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-foreground"><?php echo e($event->title); ?></h3>
                        <span class="shrink-0 rounded-full bg-secondary px-2 py-0.5 text-[10px] font-semibold"><?php echo e($lang === 'en' ? 'Official' : 'Rasmi'); ?></span>
                    </div>
                    <p class="mt-3 flex items-center gap-2 text-sm text-muted-foreground">📅 <?php echo e($event->start_at->format($lang === 'en' ? 'M j, Y H:i' : 'd M Y H:i')); ?></p>
                    <?php if($event->location): ?>
                        <p class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">📍 <?php echo e($event->location); ?></p>
                    <?php endif; ?>
                    <?php if($event->description): ?>
                        <p class="mt-3 text-sm text-muted-foreground"><?php echo e(\Illuminate\Support\Str::limit($event->description, 120)); ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/components/official-events.blade.php ENDPATH**/ ?>