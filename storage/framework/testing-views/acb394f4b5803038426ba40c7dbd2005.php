<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['dataSlot','class']));

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

foreach (array_filter((['dataSlot','class']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php if (isset($component)) { $__componentOriginal7b8008642300702436db015e4d952856 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b8008642300702436db015e4d952856 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'heroicons::components.outline.chevron-left','data' => ['dataSlot' => $dataSlot,'class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicons::outline.chevron-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data-slot' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dataSlot),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b8008642300702436db015e4d952856)): ?>
<?php $attributes = $__attributesOriginal7b8008642300702436db015e4d952856; ?>
<?php unset($__attributesOriginal7b8008642300702436db015e4d952856); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b8008642300702436db015e4d952856)): ?>
<?php $component = $__componentOriginal7b8008642300702436db015e4d952856; ?>
<?php unset($__componentOriginal7b8008642300702436db015e4d952856); ?>
<?php endif; ?><?php /**PATH storage/framework/testing-views/dfb960aa4e0ef6cf7d95d12eee424234.blade.php ENDPATH**/ ?>