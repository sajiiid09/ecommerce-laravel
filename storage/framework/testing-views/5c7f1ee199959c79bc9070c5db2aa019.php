<?php foreach (([
    'searchable' => false,
    'search' => null,
    'empty' => 'no results found',
    'loading' => null,
    'label' => null,
    'preventLoading' => false,
]) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'checkIcon' => 'check',
    'empty' => 'no results found'
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
    'checkIcon' => 'check',
    'empty' => 'no results found'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div 
    class="absolute z-50 bg-white [:where(&)]:w-full dark:bg-neutral-800 mt-1 backdrop-blur-xl border dark:border-neutral-700 border-neutral-200 rounded-(--popup-round) shadow-lg py-(--popup-padding)"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-150 pointer-events-none"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    x-on:click.away="handleClickAway($event.target)"
    x-show="__isOpen"
    x-anchor="$refs.trigger"
    x-cloak  
>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($searchable): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search instanceof \Illuminate\View\ComponentSlot): ?>
            <?php echo e($search); ?>

        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginale6a7d7b3d590952010afee3c35a6a767 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale6a7d7b3d590952010afee3c35a6a767 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select.search','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select.search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale6a7d7b3d590952010afee3c35a6a767)): ?>
<?php $attributes = $__attributesOriginale6a7d7b3d590952010afee3c35a6a767; ?>
<?php unset($__attributesOriginale6a7d7b3d590952010afee3c35a6a767); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale6a7d7b3d590952010afee3c35a6a767)): ?>
<?php $component = $__componentOriginale6a7d7b3d590952010afee3c35a6a767; ?>
<?php unset($__componentOriginale6a7d7b3d590952010afee3c35a6a767); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <ul  
        class="grid grid-cols-[auto_auto_1fr] gap-y-1 relative overflow-y-auto data-loading:h-24 px-(--popup-padding) max-h-60"
        x-bind:aria-multiselectable="__isMultiple ? 'true' : 'false'"
        <?php echo e($attributes->whereStartsWith('wire:target')); ?> 
        x-bind:aria-label="<?php echo \Illuminate\Support\Js::from($label ?? 'Options')->toHtml() ?>"
        <?php if(!$preventLoading): ?>
            wire:loading.attr="data-loading"
        <?php endif; ?>
        x-rover:options
    >
        <?php echo e($slot); ?>

        
        
        <li 
            class="col-span-full [ul:is([data-loading])_&]:hidden"
            x-rover:empty 
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empty instanceof \Illuminate\View\ComponentSlot): ?>
                <?php echo e($empty); ?>

            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal47a99b571099212b0c284f0080dfbeaa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal47a99b571099212b0c284f0080dfbeaa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.text','data' => ['class' => 'h-14 flex items-center justify-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-14 flex items-center justify-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e($empty); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal47a99b571099212b0c284f0080dfbeaa)): ?>
<?php $attributes = $__attributesOriginal47a99b571099212b0c284f0080dfbeaa; ?>
<?php unset($__attributesOriginal47a99b571099212b0c284f0080dfbeaa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal47a99b571099212b0c284f0080dfbeaa)): ?>
<?php $component = $__componentOriginal47a99b571099212b0c284f0080dfbeaa; ?>
<?php unset($__componentOriginal47a99b571099212b0c284f0080dfbeaa); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </li>

        
        <?php if (isset($component)) { $__componentOriginal2187921d308ea0cb3c0cc24d7f803919 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2187921d308ea0cb3c0cc24d7f803919 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select.loading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select.loading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if( $loading instanceof \Illuminate\View\ComponentSlot): ?>
                <?php echo e($loading); ?>

            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginalf0fb8d54e448565cc9b0ecc2e4078cb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0fb8d54e448565cc9b0ecc2e4078cb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.loading','data' => ['class' => 'opacity-50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon.loading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'opacity-50']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0fb8d54e448565cc9b0ecc2e4078cb1)): ?>
<?php $attributes = $__attributesOriginalf0fb8d54e448565cc9b0ecc2e4078cb1; ?>
<?php unset($__attributesOriginalf0fb8d54e448565cc9b0ecc2e4078cb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0fb8d54e448565cc9b0ecc2e4078cb1)): ?>
<?php $component = $__componentOriginalf0fb8d54e448565cc9b0ecc2e4078cb1; ?>
<?php unset($__componentOriginalf0fb8d54e448565cc9b0ecc2e4078cb1); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2187921d308ea0cb3c0cc24d7f803919)): ?>
<?php $attributes = $__attributesOriginal2187921d308ea0cb3c0cc24d7f803919; ?>
<?php unset($__attributesOriginal2187921d308ea0cb3c0cc24d7f803919); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2187921d308ea0cb3c0cc24d7f803919)): ?>
<?php $component = $__componentOriginal2187921d308ea0cb3c0cc24d7f803919; ?>
<?php unset($__componentOriginal2187921d308ea0cb3c0cc24d7f803919); ?>
<?php endif; ?>
    </ul>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/ui/select/options.blade.php ENDPATH**/ ?>