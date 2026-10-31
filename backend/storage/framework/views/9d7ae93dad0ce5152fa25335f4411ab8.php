<?php if(!request()->is('admin*') && !request()->is('assistant')): ?>
<a href="<?php echo e(route('assistant')); ?>"
   class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full brand-gradient text-white shadow-lg transition-transform hover:scale-105 hover:shadow-xl"
   aria-label="<?php echo e($lang === 'en' ? 'Ask the LandFocus assistant' : 'Uliza msaidizi wa LandFocus'); ?>">
    <span class="text-2xl">🤖</span>
    <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-accent-foreground">AS</span>
</a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\lendfocus_flutter\backend\resources\views/components/chat-fab.blade.php ENDPATH**/ ?>