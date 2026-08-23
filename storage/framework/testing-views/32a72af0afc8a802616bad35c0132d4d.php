<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => 'Carousel', 'loop' => false, 'showDots' => true, 'showControls' => true, 'autoplay' => false, 'interval' => 5000]));

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

foreach (array_filter((['label' => 'Carousel', 'loop' => false, 'showDots' => true, 'showControls' => true, 'autoplay' => false, 'interval' => 5000]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="storeCarousel({ loop: <?php echo \Illuminate\Support\Js::from($loop)->toHtml() ?>, autoplay: <?php echo \Illuminate\Support\Js::from($autoplay)->toHtml() ?>, interval: <?php echo \Illuminate\Support\Js::from($interval)->toHtml() ?> })" x-init="init()" <?php echo e($attributes->class('relative')); ?> aria-roledescription="carousel" aria-label="<?php echo e($label); ?>">
    <div class="relative">
        <div class="-mx-1 overflow-hidden <?php echo e($showControls ? 'px-12' : 'px-1'); ?>">
            <div x-ref="viewport" class="overflow-hidden">
                <div x-ref="track" class="flex cursor-grab touch-pan-y gap-3 pb-2 active:cursor-grabbing" tabindex="0" @keydown.left.prevent="previous()" @keydown.right.prevent="next()">
                    <?php echo e($slot); ?>

                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showControls): ?>
        <div x-show="pageCount > 1" class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between px-2">
            <button type="button" class="pointer-events-auto grid size-10 place-items-center rounded-full border border-store-border bg-white/95 text-store-ink shadow-sm transition hover:border-store-blue hover:text-store-blue disabled:cursor-not-allowed disabled:opacity-40" @click="previous()" :disabled="!canPrevious" aria-label="Previous <?php echo e($label); ?>"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'chevron-left','class' => 'size-5 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'size-5 !text-current']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></button>
            <button type="button" class="pointer-events-auto grid size-10 place-items-center rounded-full border border-store-border bg-white/95 text-store-ink shadow-sm transition hover:border-store-blue hover:text-store-blue disabled:cursor-not-allowed disabled:opacity-40" @click="next()" :disabled="!canNext" aria-label="Next <?php echo e($label); ?>"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'chevron-right','class' => 'size-5 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'size-5 !text-current']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></button>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDots): ?>
    <div x-show="pageCount > 1" class="mt-4 flex justify-center gap-1.5" role="tablist" aria-label="<?php echo e($label); ?> pages">
        <template x-for="page in pageCount" :key="page"><button type="button" class="size-2 rounded-full transition-colors" :class="current === page - 1 ? 'bg-store-blue' : 'bg-store-border'" @click="goTo(page - 1)" :aria-label="'Go to <?php echo e($label); ?> page ' + page" :aria-current="current === page - 1 ? 'true' : 'false'"></button></template>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/store/ui/carousel.blade.php ENDPATH**/ ?>