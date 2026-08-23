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
<?php if (isset($component)) { $__componentOriginal046e2a92b0ff4ce617b49d2a1d3791f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal046e2a92b0ff4ce617b49d2a1d3791f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'heroicons::components.outline.beaker','data' => ['dataSlot' => $dataSlot,'class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicons::outline.beaker'); ?>
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
<?php if (isset($__attributesOriginal046e2a92b0ff4ce617b49d2a1d3791f0)): ?>
<?php $attributes = $__attributesOriginal046e2a92b0ff4ce617b49d2a1d3791f0; ?>
<?php unset($__attributesOriginal046e2a92b0ff4ce617b49d2a1d3791f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal046e2a92b0ff4ce617b49d2a1d3791f0)): ?>
<?php $component = $__componentOriginal046e2a92b0ff4ce617b49d2a1d3791f0; ?>
<?php unset($__componentOriginal046e2a92b0ff4ce617b49d2a1d3791f0); ?>
<?php endif; ?><?php /**PATH storage/framework/testing-views/57a250394d65577e630c27b7e3289c55.blade.php ENDPATH**/ ?>