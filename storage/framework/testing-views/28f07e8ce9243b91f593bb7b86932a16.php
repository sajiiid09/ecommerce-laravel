<?php foreach (([
    'icon' => '',
    'iconAfter' => 'chevron-up-down',
    'disabled' => false,
    'clearable' => false,
    'searchable' => false,
    'triggerClass' => '',
    'invalid' => false,
    'trigger' => null,
    'size' => 'default',
    'placeholder' => 'select...',
]) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php
    $classes = [
        'border-red-600/30 border-2 data-open:border-red-600/30 data-open:ring-red-600/20 dark:border-red-400/30 dark:data-open:border-red-400/30 dark:data-open:ring-red-400/20' => $invalid,
        'border-black/10 data-open:border-black/15 data-open:ring-neutral-900/15 dark:border-white/15 dark:data-open:border-white/20 dark:data-open:ring-neutral-100/15' => !$invalid,
        'border bg-white border-gray-300 dark:bg-white dark:border-gray-300 dark:text-store-ink rounded-box text-start',
        'data-open:ring-2 data-open:ring-offset-0 data-open:outline-none',
        'col-span-4 col-start-1 row-start-1 justify-self-stretch',
        'disabled:opacity-60 flex items-center disabled:cursor-auto cursor-pointer',
        'overflow-hidden whitespace-nowrap truncate',
        match($size) {
            'sm' => 'min-h-8 p-0.5 text-sm',
            default => 'min-h-10 p-1 pl-2'
        }
    ];
?>

<div    
    x-ref="trigger"
    x-on:click="handleButtonClick"
    
    data-slot="trigger" 
    <?php echo e($attributes->class([
        'relative grid place-items-center grid-cols-[40px_1fr_auto]',
        '[&>[data-slot=icon]+[data-slot=select-control]]:pl-10',
        '[&_[data-slot=icon]]:opacity-40 [&_[data-slot=icon]]:cursor-auto' => $disabled,
    ])); ?>

>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($icon)): ?>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => $icon,'class' => 'col-span-1 col-start-1 row-start-1 h-full w-full flex items-center justify-center z-10 !size-[1.10rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'col-span-1 col-start-1 row-start-1 h-full w-full flex items-center justify-center z-10 !size-[1.10rem]']); ?>
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

    <button 
        type="button"
        aria-haspopup="listbox"
        x-bind:aria-expanded="__isOpen ? 'true' : 'false'"
        x-bind:aria-controls="$id('rover-options')"
        x-bind:aria-activedescendant="$rover.getActiveItemId() || false"
        x-bind:aria-multiselectable="__isMultiple || false"
        aria-label="<?php echo e($placeholder); ?>"
        x-bind:data-open="__isOpen"
        data-slot="select-control"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([...$classes, $triggerClass]); ?>"
        <?php if($disabled): echo 'disabled'; endif; ?>
    >
        <span wire:ignore class="truncate self-center w-full">
            <span x-ref:trigger-value><?php echo e($placeholder); ?></span>
        </span>
    </button>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($iconAfter)): ?>
        <div
            data-slot="icon"
            <?php if($clearable): ?>
                x-show="!hasSelection"
            <?php endif; ?>
            x-cloak
            tabindex="0"
            class="col-start-3 row-start-1 pr-1 justify-self-center"
        >
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => $iconAfter,'class' => \Illuminate\Support\Arr::toCssClasses([
                    'rounded-md dark:hover:bg-white/5 hover:bg-neutral-800/5',
                    match($size) {
                        'sm' => 'size-6 p-0.75',
                        default => 'size-8 p-1',
                    }
                ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconAfter),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses([
                    'rounded-md dark:hover:bg-white/5 hover:bg-neutral-800/5',
                    match($size) {
                        'sm' => 'size-6 p-0.75',
                        default => 'size-8 p-1',
                    }
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
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clearable): ?>
        <div
            data-slot="select-clear"
            role="button"
            tabindex="0"
            aria-label="Clear selection"
            x-show="hasSelection"
            x-cloak
            x-on:click.stop="clear()"
            x-on:keydown.enter.stop="clear"
            x-on:keydown.space.prevent.stop="clear"
            x-bind:aria-disabled="!hasSelection"
            class="col-start-3 row-start-1 pr-1 justify-self-center"
        >
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'x-mark','class' => \Illuminate\Support\Arr::toCssClasses([
                    'rounded-md dark:hover:bg-white/5 hover:bg-neutral-800/5',
                    match($size) {
                        'sm' => 'size-6 p-0.75',
                        default => 'size-8 p-1',
                    }
                ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses([
                    'rounded-md dark:hover:bg-white/5 hover:bg-neutral-800/5',
                    match($size) {
                        'sm' => 'size-6 p-0.75',
                        default => 'size-8 p-1',
                    }
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
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/ui/select/trigger.blade.php ENDPATH**/ ?>