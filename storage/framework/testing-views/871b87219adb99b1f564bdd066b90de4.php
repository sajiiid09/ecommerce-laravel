<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['size' => 'default']));

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

foreach (array_filter((['size' => 'default']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $width = match ($size) {
        'narrow' => 'max-w-2xl',
        'content' => 'max-w-6xl',
        default => 'max-w-8xl',
    };
?>

<div <?php echo e($attributes->class([$width, 'mx-auto w-full px-4 sm:px-5 lg:px-6'])); ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/components/store/ui/container.blade.php ENDPATH**/ ?>