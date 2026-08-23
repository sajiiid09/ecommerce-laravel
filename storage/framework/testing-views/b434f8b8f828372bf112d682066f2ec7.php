<div class="bg-store-red text-white">
    <?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.container','data' => ['class' => 'flex min-h-8 items-center justify-center gap-x-4 gap-y-1 py-1 text-center text-xs font-medium sm:justify-between sm:py-1.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex min-h-8 items-center justify-center gap-x-4 gap-y-1 py-1 text-center text-xs font-medium sm:justify-between sm:py-1.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <span class="hidden sm:inline">🚚 Fast Delivery in 24–48 hrs in Dhaka</span>
        <span>Free Delivery on orders over ৳699</span>
        <span class="hidden md:inline">Pay with bKash, Nagad or COD</span>
        <a href="<?php echo e(route('store.offers')); ?>" class="hidden rounded border border-white/60 px-2 py-0.5 text-[11px] font-semibold hover:bg-white/10 lg:inline">Save More with StoreZ Offers!</a>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $attributes = $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $component = $__componentOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/store/layout/promo-bar.blade.php ENDPATH**/ ?>