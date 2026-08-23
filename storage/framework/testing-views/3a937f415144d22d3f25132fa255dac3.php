<div id="cart-drawer" x-cloak x-show="cartOpen" class="relative z-50" aria-live="polite">
    <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 bg-store-ink/45" @click="cartOpen = false" aria-hidden="true"></div>
    <aside x-show="cartOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title">
        <div class="flex items-center justify-between border-b border-store-border px-5 py-4"><div><h2 id="cart-drawer-title" class="text-lg font-extrabold text-store-ink">Shopping Cart</h2><p class="text-xs text-store-muted" x-text="`${cart.reduce((total, item) => total + item.quantity, 0)} items in your cart`"></p></div><button type="button" class="grid size-11 place-items-center rounded-control text-store-muted hover:bg-store-soft hover:text-store-ink" @click="cartOpen = false"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'x-mark','class' => 'size-6 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-6 !text-current']); ?>
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
<?php endif; ?><span class="sr-only">Close cart</span></button></div>
        <div class="flex-1 overflow-y-auto p-5">
            <template x-if="cart.length === 0"><div class="grid min-h-56 place-items-center text-center"><div><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-bag','class' => 'mx-auto size-10 !text-store-placeholder']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'mx-auto size-10 !text-store-placeholder']); ?>
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
<?php endif; ?><p class="mt-3 font-bold text-store-ink">Your cart is empty</p><p class="mt-1 text-sm text-store-muted">Add something you’ll love.</p></div></div></template>
            <template x-for="item in cart" :key="item.id"><article class="flex gap-3 border-b border-store-border py-4 first:pt-0"><img :src="item.image" :alt="item.name" class="size-18 rounded-control border border-store-border object-contain"><div class="min-w-0 flex-1"><h3 class="line-clamp-2 text-sm font-semibold text-store-ink" x-text="item.name"></h3><p class="mt-1 text-xs text-store-muted" x-text="item.brand"></p><p class="mt-1 text-base font-extrabold text-store-red" x-text="`৳${(item.price * item.quantity).toLocaleString()}`"></p><div class="mt-2 flex items-center justify-between"><div class="flex h-8 items-center rounded-control border border-store-border"><button type="button" class="grid size-8 place-items-center text-store-ink hover:bg-store-soft" @click="item.quantity = Math.max(1, item.quantity - 1)" :aria-label="`Decrease ${item.name} quantity`"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'minus','class' => 'size-3 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','class' => 'size-3 !text-current']); ?>
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
<?php endif; ?></button><span class="w-7 text-center text-sm font-semibold" x-text="item.quantity"></span><button type="button" class="grid size-8 place-items-center text-store-ink hover:bg-store-soft" @click="item.quantity++" :aria-label="`Increase ${item.name} quantity`"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'plus','class' => 'size-3 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'size-3 !text-current']); ?>
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
<?php endif; ?></button></div><button type="button" class="text-xs font-semibold text-store-error hover:underline" @click="cart = cart.filter(cartItem => cartItem.id !== item.id)">Remove</button></div></div></article></template>
        </div>
        <div class="border-t border-store-border p-5"><div class="flex items-center justify-between text-base font-bold text-store-ink"><span>Subtotal</span><span x-text="`৳${cart.reduce((total, item) => total + item.price * item.quantity, 0).toLocaleString()}`"></span></div><p class="mt-1 text-xs text-store-muted">Delivery charges calculated at checkout.</p><div class="mt-4 grid grid-cols-2 gap-3"><a href="#cart" class="inline-flex h-11 items-center justify-center rounded-control border border-store-blue text-sm font-bold text-store-blue hover:bg-store-blue-soft">View Cart</a><a href="#checkout" class="inline-flex h-11 items-center justify-center rounded-control bg-store-red text-sm font-bold text-white hover:bg-red-700">Checkout</a></div></div>
    </aside>
</div>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/components/store/checkout/cart-drawer.blade.php ENDPATH**/ ?>