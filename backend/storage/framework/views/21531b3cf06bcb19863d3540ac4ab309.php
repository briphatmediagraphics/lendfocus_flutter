<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['service']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['service']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $title = $lang === 'en' ? ($service['title_en'] ?? '') : ($service['title_sw'] ?? '');
    $desc = $lang === 'en' ? ($service['desc_en'] ?? '') : ($service['desc_sw'] ?? '');
?>

<div class="group rounded-2xl border border-border bg-card p-6 shadow-card transition-all hover:-translate-y-1 hover:border-primary/40 hover:shadow-soft">
    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl brand-gradient text-lg text-white">◆</div>
    <h3 class="text-lg font-semibold text-foreground"><?php echo e($title); ?></h3>
    <p class="mt-2 text-sm text-muted-foreground"><?php echo e($desc); ?></p>
</div>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/components/service-card.blade.php ENDPATH**/ ?>