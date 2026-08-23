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
<?php if (isset($component)) { $__componentOriginal7c853b27601b446c3f313440617f97bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c853b27601b446c3f313440617f97bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'heroicons::components.solid.star','data' => ['dataSlot' => $dataSlot,'class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicons::solid.star'); ?>
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
<?php if (isset($__attributesOriginal7c853b27601b446c3f313440617f97bd)): ?>
<?php $attributes = $__attributesOriginal7c853b27601b446c3f313440617f97bd; ?>
<?php unset($__attributesOriginal7c853b27601b446c3f313440617f97bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c853b27601b446c3f313440617f97bd)): ?>
<?php $component = $__componentOriginal7c853b27601b446c3f313440617f97bd; ?>
<?php unset($__componentOriginal7c853b27601b446c3f313440617f97bd); ?>
<?php endif; ?><?php /**PATH storage/framework/testing-views/30c450c805d3399a4739a13ee9a19f0d.blade.php ENDPATH**/ ?>