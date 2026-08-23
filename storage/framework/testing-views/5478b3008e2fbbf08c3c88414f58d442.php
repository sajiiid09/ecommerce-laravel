<div x-data="{ open: false }">
    <button type="button" class="inline-flex h-10 items-center gap-2 rounded-control border border-store-blue bg-white px-4 text-sm font-bold text-store-blue lg:hidden" @click="open = true" aria-controls="account-navigation" :aria-expanded="open.toString()"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'bars-3','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bars-3','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?>Account menu</button>
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-store-ink/45 lg:hidden" @click="open = false" aria-hidden="true"></div>
    <aside id="account-navigation" x-cloak x-show="open || window.innerWidth >= 1024" x-transition class="fixed inset-y-0 left-0 z-50 w-[min(88vw,20rem)] overflow-y-auto rounded-none border-r border-store-border bg-white p-3 shadow-2xl lg:static lg:block lg:w-auto lg:rounded-card lg:border lg:shadow-none" @keydown.escape.window="open = false">
        <div class="mb-3 flex items-center justify-between lg:hidden"><span class="font-bold text-store-ink">My account</span><button type="button" class="grid size-11 place-items-center rounded-control text-store-ink hover:bg-store-soft" @click="open = false" aria-label="Close account menu"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'x-mark','class' => 'size-5 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-5 !text-current']); ?>
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
<?php endif; ?></button></div>
        <a href="<?php echo e(route('account.dashboard')); ?>" wire:navigate class="block rounded-control bg-store-blue-soft px-3 py-3 text-sm font-bold text-store-blue">Account Overview</a>
        <a href="<?php echo e(route('account.orders')); ?>" wire:navigate class="mt-1 block rounded-control px-3 py-3 text-sm text-store-text hover:bg-store-soft">My Orders</a>
        <a href="<?php echo e(route('store.wishlist')); ?>" wire:navigate class="mt-1 block rounded-control px-3 py-3 text-sm text-store-text hover:bg-store-soft">Wishlist</a>
        <a href="<?php echo e(route('account.dashboard')); ?>#addresses" wire:navigate class="mt-1 block rounded-control px-3 py-3 text-sm text-store-text hover:bg-store-soft">Addresses</a>
    </aside>
</div>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/store/account/sidebar.blade.php ENDPATH**/ ?>