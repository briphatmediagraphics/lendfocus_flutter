<?php
    $mapSrc = sprintf(
        'https://www.openstreetmap.org/export/embed.html?bbox=%s%%2C%s%%2C%s%%2C%s&layer=mapnik&marker=%s%%2C%s',
        $company['lng'] - 0.02,
        $company['lat'] - 0.02,
        $company['lng'] + 0.02,
        $company['lat'] + 0.02,
        $company['lat'],
        $company['lng']
    );
?>

<footer class="mt-16 border-t border-border bg-secondary/50">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4">
        <div>
            <h3 class="text-lg font-bold text-foreground">Land<span class="text-primary">Focus</span></h3>
            <p class="mt-2 text-sm text-muted-foreground">
                <?php echo e($lang === 'en'
                    ? 'Your trusted partner in land, houses, farms and project services across Tanzania.'
                    : 'Mwenzio wa kuaminika katika biashara ya ardhi, nyumba, mashamba na huduma za miradi nchini Tanzania.'); ?>

            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php $__currentLoopData = $social; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $href): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($href); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo e(ucfirst($label)); ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-background text-xs font-bold text-muted-foreground transition hover:border-primary/40 hover:text-primary"><?php echo e(strtoupper(substr($label, 0, 1))); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div>
            <h4 class="font-semibold text-foreground"><?php echo e($lang === 'en' ? 'Links' : 'Viungo'); ?></h4>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="<?php echo e(route('home')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'Home' : 'Mwanzo'); ?></a></li>
                <li><a href="<?php echo e(route('services')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'Services' : 'Huduma'); ?></a></li>
                <li><a href="<?php echo e(route('properties')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'Projects' : 'Miradi'); ?></a></li>
                <li><a href="<?php echo e(route('about')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'About' : 'Kuhusu sisi'); ?></a></li>
                <li><a href="<?php echo e(route('history')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'History' : 'Historia'); ?></a></li>
                <li><a href="<?php echo e(route('contact')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'Contact' : 'Mawasiliano'); ?></a></li>
                <li><a href="<?php echo e(route('terms')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'Terms' : 'Masharti'); ?></a></li>
                <li><a href="<?php echo e(route('assistant')); ?>" class="text-muted-foreground hover:text-primary"><?php echo e($lang === 'en' ? 'LandFocus Assistant' : 'Msaidizi wa LandFocus'); ?></a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-foreground"><?php echo e($lang === 'en' ? 'Property Types' : 'Aina za Miradi'); ?></h4>
            <ul class="mt-3 space-y-2 text-sm">
                <?php $__currentLoopData = ['land', 'house', 'farm', 'office']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><a href="<?php echo e(route('properties', ['type' => $type])); ?>" class="text-muted-foreground hover:text-primary"><?php echo e(config("landfocus.property_types.{$type}.{$lang}")); ?></a></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <h4 class="mt-6 font-semibold text-foreground"><?php echo e($lang === 'en' ? 'Contact' : 'Wasiliana'); ?></h4>
            <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                <li>📞 <?php echo e($company['phone']); ?></li>
                <li>✉ <?php echo e($company['email']); ?></li>
                <li>📍 <?php echo e($company['address']); ?></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-foreground"><?php echo e($lang === 'en' ? 'Our Location' : 'Eneo Letu'); ?></h4>
            <div class="mt-3 overflow-hidden rounded-xl border border-border">
                <iframe title="<?php echo e($lang === 'en' ? 'LandFocus office map' : 'Ramani ya ofisi ya LandFocus'); ?>" src="<?php echo e($mapSrc); ?>" class="h-48 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <a href="https://www.google.com/maps?q=<?php echo e($company['lat']); ?>,<?php echo e($company['lng']); ?>" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs text-primary hover:underline">
                <?php echo e($lang === 'en' ? 'Open in Google Maps' : 'Fungua kwenye Google Maps'); ?>

            </a>
        </div>
    </div>
    <div class="border-t border-border py-4 text-center text-xs text-muted-foreground">
        © <?php echo e(date('Y')); ?> LandFocus. <?php echo e($lang === 'en' ? 'All rights reserved.' : 'Haki zote zimehifadhiwa.'); ?>

    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/components/footer.blade.php ENDPATH**/ ?>