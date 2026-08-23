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
<?php if (isset($component)) { $__componentOriginal784cf8123c4466ca68f00142598610ae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal784cf8123c4466ca68f00142598610ae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'heroicons::components.outline.map-pin','data' => ['dataSlot' => $dataSlot,'class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicons::outline.map-pin'); ?>
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
<?php if (isset($__attributesOriginal784cf8123c4466ca68f00142598610ae)): ?>
<?php $attributes = $__attributesOriginal784cf8123c4466ca68f00142598610ae; ?>
<?php unset($__attributesOriginal784cf8123c4466ca68f00142598610ae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal784cf8123c4466ca68f00142598610ae)): ?>
<?php $component = $__componentOriginal784cf8123c4466ca68f00142598610ae; ?>
<?php unset($__componentOriginal784cf8123c4466ca68f00142598610ae); ?>
<?php endif; ?><?php /**PATH storage/framework/testing-views/c66e7e2c2b783ebdca02d01fd08920fd.blade.php ENDPATH**/ ?>