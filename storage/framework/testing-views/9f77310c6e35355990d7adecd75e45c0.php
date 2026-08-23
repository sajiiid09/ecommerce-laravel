<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'position' => 'bottom-right',
    'maxToasts' => 5,
    'progressBarVariant' => 'full',
    'progressBarAlignment' => 'bottom'
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
    'position' => 'bottom-right',
    'maxToasts' => 5,
    'progressBarVariant' => 'full',
    'progressBarAlignment' => 'bottom'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $placement = match($position) {
        'bottom-right' => 'bottom-0 right-0 pr-4 pb-4',
        'bottom-left' => 'bottom-0 left-0 pl-4 pb-4',
        'top-right' => 'top-0 right-0 pr-4 pt-4',
        'top-left' => 'top-0 left-0 pl-4 pt-4',
        'top-center' => 'top-0 left-1/2 -translate-x-1/2 pt-4',
        default => 'bottom-0 right-0 pr-4 pb-4'
    };
    
    $sessionToast = session()->pull('notify');

    $isAlignmentsToBottom = Str::startsWith($position, 'bottom');
?>

<div
    x-data="{
        toasts: [],
        maxToasts: <?php echo \Illuminate\Support\Js::from($maxToasts)->toHtml() ?>,
        pausedToastIds: new Set(),

       typeConfig: {
            info: {
                textColor: 'text-gray-600 dark:text-gray-400', // we're using color-mix for making variants color solid and not transparent
                background: 'dark:bg-[color-mix(in_oklab,_var(--color-gray-600)_10%,_var(--color-neutral-900)_90%)] bg-[color-mix(in_oklab,_var(--color-gray-500)_20%,_var(--color-white)_80%)]',
                borderColor: 'border-gray-500/55',
                ariaLabel: 'Information',
            },
            success: {
                textColor: 'text-green-600 dark:text-green-400',
                background: 'dark:bg-[color-mix(in_oklab,_var(--color-green-600)_10%,_var(--color-neutral-900)_90%)] bg-[color-mix(in_oklab,_var(--color-green-500)_20%,_var(--color-white)_80%)]',
                borderColor: 'border-green-500/55',
                ariaLabel: 'Success',
            },
            error: {
                textColor: 'text-red-600 dark:text-red-400',
                background: 'dark:bg-[color-mix(in_oklab,_var(--color-red-600)_10%,_var(--color-neutral-900)_90%)] bg-[color-mix(in_oklab,_var(--color-red-500)_25%,_var(--color-white)_75%)]',
                borderColor: 'border-red-500/55',
                ariaLabel: 'Error',
            },
            warning: {
                textColor: 'text-yellow-600 dark:text-yellow-400',
                background: 'dark:bg-[color-mix(in_oklab,_var(--color-yellow-600)_10%,_var(--color-neutral-900)_90%)] bg-[color-mix(in_oklab,_var(--color-yellow-500)_25%,_var(--color-white)_75%)]',
                borderColor: 'border-yellow-500/55',
                ariaLabel: 'Warning',
            },
        },

        init() {
            // used for toasts used after redirect..., any backend toast. 
            if(<?php echo \Illuminate\Support\Js::from(filled($sessionToast))->toHtml() ?>){
                const toast = <?php echo \Illuminate\Support\Js::from($sessionToast)->toHtml() ?>;
                this.addToast(toast);
            }
        },

        addToast(details) {
            if (!details?.content) return;

            const toast = {
                id: Date.now() + Math.random(),
                type: details.type || 'info',
                content: details.content,
                duration: details.duration || 4000,
                showProgress: details.showProgress !== false
            };

            this.toasts.unshift(toast); 

            // Limit number of toasts
            if (this.toasts.length > this.maxToasts) {
                this.toasts = this.toasts.slice(0, this.maxToasts);
            }
        },

        removeToast(id) {
            this.toasts = this.toasts.filter(toast => toast.id !== id);
            this.pausedToastIds.delete(id);
        },

        pauseFromToast(targetId) {
            const targetIndex = this.toasts.findIndex(toast => toast.id === targetId);
            
            if (targetIndex === -1) return;
            
            // Pause the target toast and all toasts above it (index 0 to targetIndex)
            this.pausedToastIds.clear();

            for (let i = 0; i <= targetIndex; i++) {
                this.pausedToastIds.add(this.toasts[i].id);
            }
        },

        resumeAllToasts() {
            this.pausedToastIds.clear();
        },

        isToastPaused(id) {
            return this.pausedToastIds.has(id);
        },

        getToastClasses(type) {
            const config = this.typeConfig[type] || this.typeConfig.info;
            return `${config.background} ${config.borderColor}`;
        },

        getIconColor(type) {
            const config = this.typeConfig[type] || this.typeConfig.info;
            return config.textColor;
        },

        getAriaLabel(type) {
            const config = this.typeConfig[type] || this.typeConfig.info;
            return config.ariaLabel;
        }
    }"
    x-on:notify.window="addToast($event.detail)"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'fixed flex w-full max-w-xs gap-4 sm:justify-start z-50',
        'flex-col-reverse' => $isAlignmentsToBottom,
        'flex-col' => !$isAlignmentsToBottom,
        $placement
    ]); ?>"
    role="status"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <?php if (isset($component)) { $__componentOriginal76f386dd678289c12770c7910ae6936f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76f386dd678289c12770c7910ae6936f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.toast.item','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.toast.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76f386dd678289c12770c7910ae6936f)): ?>
<?php $attributes = $__attributesOriginal76f386dd678289c12770c7910ae6936f; ?>
<?php unset($__attributesOriginal76f386dd678289c12770c7910ae6936f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76f386dd678289c12770c7910ae6936f)): ?>
<?php $component = $__componentOriginal76f386dd678289c12770c7910ae6936f; ?>
<?php unset($__componentOriginal76f386dd678289c12770c7910ae6936f); ?>
<?php endif; ?>
    </template>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/ui/toast/index.blade.php ENDPATH**/ ?>