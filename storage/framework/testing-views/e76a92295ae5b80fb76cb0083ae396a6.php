<?php foreach (([
    'checkIcon' => 'check',
    'checkIconClass' => '',
    'searchable' => false,
    'allowCustomSlots' => false
]) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => null,
    'label' => null,
    'searchLabel' => null,
    'icon' => null,
    'iconClass' => null,
    'disabled' => false,
    'allowCustomSlots' => false
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
    'value' => null,
    'label' => null,
    'searchLabel' => null,
    'icon' => null,
    'iconClass' => null,
    'disabled' => false,
    'allowCustomSlots' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if (!$allowCustomSlots) {
        $value = filled($value) ? $value : $slot->__toString();
        $label = filled($label) ? $label : $slot->__toString();
        $searchLabel = filled($searchLabel) ? $searchLabel : $label;
    } else {
        if (blank($label)) {
            throw new RuntimeException("A string label prop is required for custom slots");
        }

        $value = filled($value) ? $value : $label;
        $searchLabel = filled($searchLabel) ? $searchLabel : $label;
    }

    $classes = [
        "rounded-[calc(var(--popup-round)-var(--popup-padding))] group self-center gap-x-2 items-center",
        "focus:bg-neutral-100 focus:dark:bg-neutral-700 px-3 py-0.5 w-full text-[1rem]",
        "data-active:bg-neutral-800/5 dark:data-active:bg-white/5",
        "col-span-full grid grid-cols-subgrid" => !$allowCustomSlots,
        'col-span-full' => $allowCustomSlots,
        '[&[aria-disabled=true]]:pointer-events-none [&[aria-disabled=true]]:opacity-50',
        '[ul:is([data-loading])>&]:hidden'
    ];
?>

<li 
    data-search="<?php echo e($searchLabel); ?>"
    data-label="<?php echo e($label); ?>"
    value="<?php echo e($value); ?>"
    data-slot="option"
    x-rover:option
    
     
    wire:ignore.self
    
    <?php if($disabled): ?> disabled aria-disabled="true" <?php endif; ?>

    <?php echo e($attributes->class($classes)); ?>

>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$allowCustomSlots): ?>
        
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => $checkIcon,'class' => \Illuminate\Support\Arr::toCssClasses([
                'z-10 place-self-center opacity-0 group-data-selected:opacity-100 size-[1.15rem]',
                $checkIconClass,
            ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($checkIcon),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses([
                'z-10 place-self-center opacity-0 group-data-selected:opacity-100 size-[1.15rem]',
                $checkIconClass,
            ]))]); ?>
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
<?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($icon)): ?>
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => $icon,'class' => \Illuminate\Support\Arr::toCssClasses([
                    'z-10 pl-1.5',
                    $iconClass
                ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses([
                    'z-10 pl-1.5',
                    $iconClass
                ]))]); ?>
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
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <span class="col-start-3 text-start text-neutral-950 dark:text-neutral-50">
            <?php echo e($slot); ?>

        </span>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</li>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/ui/select/option.blade.php ENDPATH**/ ?>