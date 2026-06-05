<?php $__env->startSection('has_mobile_header'); ?><?php $__env->stopSection(); ?>
<div class="lg:hidden bg-white border-b border-gray-200 px-4 py-3">
    <div class="flex items-center justify-between">
        <button id="sidebar-open-button" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50" aria-label="Buka menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5" /></svg>
            Menu
        </button>
        <div class="text-center flex-1">
            <?php if(isset($title)): ?>
                <h2 class="text-lg font-bold text-gray-800"><?php echo e($title); ?></h2>
            <?php endif; ?>
        </div>
        <div class="w-10"></div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\MarketManager\resources\views/components/mobile-header.blade.php ENDPATH**/ ?>