<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['force' => false, 'align' => 'left', 'sizeClass' => 'text-xs', 'extra' => '']));

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

foreach (array_filter((['force' => false, 'align' => 'left', 'sizeClass' => 'text-xs', 'extra' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $base = 'px-3 py-2 md:px-6 md:py-3 ' . $sizeClass . ' font-medium text-gray-500 uppercase tracking-wider';
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left');
    $forceClass = $force ? 'hidden force-md' : '';
    $classes = trim("{$base} {$alignClass} {$forceClass} {$extra}");
?>
<th <?php echo e($attributes->merge(['class' => $classes])); ?>><?php echo e($slot); ?></th>
<?php /**PATH C:\xampp\htdocs\MarketManager\resources\views/components/table-th.blade.php ENDPATH**/ ?>