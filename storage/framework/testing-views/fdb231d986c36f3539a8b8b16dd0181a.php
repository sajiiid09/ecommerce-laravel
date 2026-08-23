<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'quote', 'rating' => 5]));

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

foreach (array_filter((['name', 'quote', 'rating' => 5]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article <?php echo e($attributes->class('h-full rounded-card border border-store-border bg-white p-5')); ?> aria-roledescription="slide">
    <div class="flex items-start justify-between gap-3"><div><h3 class="text-base font-bold text-store-ink"><?php echo e($name); ?></h3><?php if (isset($component)) { $__componentOriginal2204ff34acdbc9251ea7783560d4d3e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.rating','data' => ['rating' => $rating,'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rating),'class' => 'mt-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2)): ?>
<?php $attributes = $__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2; ?>
<?php unset($__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2204ff34acdbc9251ea7783560d4d3e2)): ?>
<?php $component = $__componentOriginal2204ff34acdbc9251ea7783560d4d3e2; ?>
<?php unset($__componentOriginal2204ff34acdbc9251ea7783560d4d3e2); ?>
<?php endif; ?></div><span class="text-3xl font-bold leading-none text-store-border" aria-hidden="true">”</span></div>
    <p class="mt-4 text-sm leading-6 text-store-muted"><?php echo e($quote); ?></p>
</article>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/store/ui/testimonial-card.blade.php ENDPATH**/ ?>