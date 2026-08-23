<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => $attributes->whereStartsWith('wire:model')->first() ?? $attributes->whereStartsWith('x-model')->first(),
    'label' => null,
    'triggerLabel' => null,
    'placeholder' => 'select...',
    'searchable' => false,
    'search' => null,
    'empty' => null,
    'multiple' => false,
    'clearable' => false,
    'disabled' => false,
    'pillbox' => false,
    'icon' => null,
    'iconAfter' => 'chevron-up-down',
    'checkIcon' => 'check',
    'checkIconClass' => null,
    'invalid' => null,
    'triggerClass' => null,
    'maxSelection' => null,
    'minSelection' => null,
    'size' => 'default',
    'disableUnSelectedOptionWhenReachingMax' => false
]));

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

foreach (array_filter(([
    'name' => $attributes->whereStartsWith('wire:model')->first() ?? $attributes->whereStartsWith('x-model')->first(),
    'label' => null,
    'triggerLabel' => null,
    'placeholder' => 'select...',
    'searchable' => false,
    'search' => null,
    'empty' => null,
    'multiple' => false,
    'clearable' => false,
    'disabled' => false,
    'pillbox' => false,
    'icon' => null,
    'iconAfter' => 'chevron-up-down',
    'checkIcon' => 'check',
    'checkIconClass' => null,
    'invalid' => null,
    'triggerClass' => null,
    'maxSelection' => null,
    'minSelection' => null,
    'size' => 'default',
    'disableUnSelectedOptionWhenReachingMax' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    // Detect if the component is bound to a Livewire model
    $modelAttrs = collect($attributes->getAttributes())->keys()->first(fn($key) => str_starts_with($key, 'wire:model'));

    $model = $modelAttrs ? $attributes->get($modelAttrs) : null;

    // Detect if model binding uses `.live` modifier (for real-time syncing)
    $isLive = $modelAttrs && str_contains($modelAttrs, '.live');

    $livewireId = isset($__livewire) ? $__livewire->getId() : null;
?>

<div 
    x-data="selectComponent({
        model: <?php echo \Illuminate\Support\Js::from($model)->toHtml() ?>,
        livewire: <?php echo \Illuminate\Support\Js::from(isset($livewireId))->toHtml() ?> ? window.Livewire.find(<?php echo \Illuminate\Support\Js::from($livewireId)->toHtml() ?>) : null,
        livewireId: <?php echo \Illuminate\Support\Js::from($livewireId)->toHtml() ?>,
        placeholder: <?php echo \Illuminate\Support\Js::from($placeholder)->toHtml() ?>,
        isLive: <?php echo \Illuminate\Support\Js::from($isLive)->toHtml() ?>,
        isMultiple: <?php echo \Illuminate\Support\Js::from($multiple)->toHtml() ?>,
        isDisabled: <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>,
        minSelection: <?php echo \Illuminate\Support\Js::from($minSelection)->toHtml() ?>,
        maxSelection: <?php echo \Illuminate\Support\Js::from($maxSelection)->toHtml() ?>,
        searchable: <?php echo \Illuminate\Support\Js::from($searchable)->toHtml() ?>,
        disableUnSelectedOptionWhenReachingMax:<?php echo \Illuminate\Support\Js::from($disableUnSelectedOptionWhenReachingMax)->toHtml() ?>,
    })"
    <?php if($disabled): ?> aria-disabled <?php endif; ?> 
    <?php if($invalid): ?> aria-invalid <?php endif; ?> 
    x-bind:aria-expanded="__isOpen"
    aria-haspopup="listbox"
    role="listbox"
    <?php echo e($attributes->class([
        'relative [--popup-round:var(--radius-box)] [--popup-padding:--spacing(1)]',
        'dark:border-red-400! dark:shadow-red-400 text-red-400! placeholder:text-red-400!' => $invalid,
        ])); ?>

    x-rover
>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($name): ?>
        <input 
            type="hidden" 
            name="<?php echo e($name); ?>" 
            x-bind:value="__isMultiple ? __state?.join(',') : __state"
        />
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($multiple && $pillbox): ?>
            <?php if (isset($component)) { $__componentOriginal7a450f9556c0a8a1c3ed5bdf212a30b5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7a450f9556c0a8a1c3ed5bdf212a30b5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select.pillbox-trigger','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select.pillbox-trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7a450f9556c0a8a1c3ed5bdf212a30b5)): ?>
<?php $attributes = $__attributesOriginal7a450f9556c0a8a1c3ed5bdf212a30b5; ?>
<?php unset($__attributesOriginal7a450f9556c0a8a1c3ed5bdf212a30b5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7a450f9556c0a8a1c3ed5bdf212a30b5)): ?>
<?php $component = $__componentOriginal7a450f9556c0a8a1c3ed5bdf212a30b5; ?>
<?php unset($__componentOriginal7a450f9556c0a8a1c3ed5bdf212a30b5); ?>
<?php endif; ?>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal63c551af7d88724f4efb8665304a97dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal63c551af7d88724f4efb8665304a97dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select.trigger','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal63c551af7d88724f4efb8665304a97dc)): ?>
<?php $attributes = $__attributesOriginal63c551af7d88724f4efb8665304a97dc; ?>
<?php unset($__attributesOriginal63c551af7d88724f4efb8665304a97dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal63c551af7d88724f4efb8665304a97dc)): ?>
<?php $component = $__componentOriginal63c551af7d88724f4efb8665304a97dc; ?>
<?php unset($__componentOriginal63c551af7d88724f4efb8665304a97dc); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalfa3eae50b2ed1c2b7aa861ecb3f28421 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa3eae50b2ed1c2b7aa861ecb3f28421 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select.options','data' => ['checkIconClass' => $checkIconClass,'checkIcon' => $checkIcon]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select.options'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['checkIconClass' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($checkIconClass),'checkIcon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($checkIcon)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e($slot); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa3eae50b2ed1c2b7aa861ecb3f28421)): ?>
<?php $attributes = $__attributesOriginalfa3eae50b2ed1c2b7aa861ecb3f28421; ?>
<?php unset($__attributesOriginalfa3eae50b2ed1c2b7aa861ecb3f28421); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa3eae50b2ed1c2b7aa861ecb3f28421)): ?>
<?php $component = $__componentOriginalfa3eae50b2ed1c2b7aa861ecb3f28421; ?>
<?php unset($__componentOriginalfa3eae50b2ed1c2b7aa861ecb3f28421); ?>
<?php endif; ?>
    </div>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/ui/select/index.blade.php ENDPATH**/ ?>