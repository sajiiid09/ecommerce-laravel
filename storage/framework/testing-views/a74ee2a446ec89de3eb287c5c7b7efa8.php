<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['price', 'oldPrice' => null, 'size' => 'default']));

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

foreach (array_filter((['price', 'oldPrice' => null, 'size' => 'default']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizes = [
        'sm' => 'text-sm',
        'default' => 'text-lg md:text-xl',
        'lg' => 'text-2xl md:text-3xl',
    ];
?>

<div <?php echo e($attributes->class('flex flex-wrap items-baseline gap-x-2 gap-y-1')); ?>>
    <span class="<?php echo e($sizes[$size] ?? $sizes['default']); ?> font-extrabold text-store-red">৳<?php echo e(number_format($price)); ?></span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($oldPrice): ?>
        <span class="text-xs font-medium text-store-placeholder line-through">৳<?php echo e(number_format($oldPrice)); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/components/store/ui/price.blade.php ENDPATH**/ ?>