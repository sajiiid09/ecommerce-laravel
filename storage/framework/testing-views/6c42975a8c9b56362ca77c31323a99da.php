<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

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

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $states = [
        'delivered' => ['Delivered', 'bg-green-100 text-green-700'],
        'processing' => ['Processing', 'bg-orange-100 text-orange-700'],
        'shipped' => ['Shipped', 'bg-blue-100 text-blue-700'],
        'out_for_delivery' => ['Out for delivery', 'bg-blue-100 text-blue-700'],
        'cancelled' => ['Cancelled', 'bg-red-100 text-red-700'],
        'returned' => ['Returned', 'bg-slate-100 text-slate-700'],
    ];
    [$label, $classes] = $states[$status] ?? [str($status)->headline(), 'bg-slate-100 text-slate-700'];
?>

<span <?php echo e($attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold', $classes])); ?>><?php echo e($label); ?></span>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/components/store/ui/status-badge.blade.php ENDPATH**/ ?>