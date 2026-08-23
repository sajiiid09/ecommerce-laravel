<?php ($placeholder = asset('images/placeholders/no-image.svg')); ?>
<main class="bg-store-soft py-6 sm:py-8"><?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.container','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php if (isset($component)) { $__componentOriginal42499c0bfae1b2331ba7bd5cfbddde36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42499c0bfae1b2331ba7bd5cfbddde36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.breadcrumb','data' => ['items' => [['label' => 'Shopping Cart']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Shopping Cart']])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42499c0bfae1b2331ba7bd5cfbddde36)): ?>
<?php $attributes = $__attributesOriginal42499c0bfae1b2331ba7bd5cfbddde36; ?>
<?php unset($__attributesOriginal42499c0bfae1b2331ba7bd5cfbddde36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42499c0bfae1b2331ba7bd5cfbddde36)): ?>
<?php $component = $__componentOriginal42499c0bfae1b2331ba7bd5cfbddde36; ?>
<?php unset($__componentOriginal42499c0bfae1b2331ba7bd5cfbddde36); ?>
<?php endif; ?>
        <div class="flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">Shopping Cart</h1>
                <p class="mt-1 text-sm text-store-muted"
                    x-text="`${cart.reduce((total, item) => total + item.quantity, 0)} items ready for checkout`"></p>
            </div><a href="<?php echo e(route('store.category')); ?>" wire:navigate
                class="hidden text-sm font-semibold text-store-blue hover:underline sm:inline">Continue Shopping →</a>
        </div>
        <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
            <section class="rounded-card border border-store-border bg-white">
                <div
                    class="hidden grid-cols-[minmax(0,1fr)_130px_130px_40px] gap-4 border-b border-store-border px-5 py-3 text-xs font-bold uppercase tracking-wide text-store-muted sm:grid">
                    <span>Product</span><span>Quantity</span><span class="text-right">Subtotal</span><span></span></div>
                <template x-if="cart.length === 0">
                    <div class="p-12 text-center"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-cart','class' => 'mx-auto size-10 !text-store-placeholder']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'mx-auto size-10 !text-store-placeholder']); ?>
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
                        <h2 class="mt-4 font-bold text-store-ink">Your cart is empty</h2><a
                            href="<?php echo e(route('store.category')); ?>" wire:navigate
                            class="mt-5 inline-flex h-10 items-center rounded-control bg-store-blue px-4 text-sm font-bold text-white">Start
                            Shopping</a>
                    </div>
                </template><template x-for="item in cart" :key="item.id">
                    <article
                        class="grid gap-3 border-b border-store-border p-4 last:border-0 sm:grid-cols-[minmax(0,1fr)_130px_130px_40px] sm:items-center sm:gap-4 sm:px-5">
                        <div class="flex min-w-0 items-center gap-3"><img src="<?php echo e($placeholder); ?>"
                                :alt="item.name"
                                class="size-20 shrink-0 rounded-control border border-store-border object-contain">
                            <div class="min-w-0">
                                <p class="text-xs text-store-muted" x-text="item.brand"></p>
                                <h2 class="line-clamp-2 text-sm font-bold text-store-ink" x-text="item.name"></h2>
                                <p class="mt-1 text-sm font-extrabold text-store-red"
                                    x-text="`৳${item.price.toLocaleString()}`"></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:block"><span
                                class="text-xs font-semibold text-store-muted sm:hidden">Quantity</span>
                            <div class="inline-flex h-9 items-center rounded-control border border-store-border"><button
                                    type="button" class="grid size-9 place-items-center"
                                    @click="item.quantity = Math.max(1, item.quantity - 1)"
                                    aria-label="Decrease quantity"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
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
<?php endif; ?></button><span
                                    class="w-8 text-center text-sm font-bold" x-text="item.quantity"></span><button
                                    type="button" class="grid size-9 place-items-center" @click="item.quantity++"
                                    aria-label="Increase quantity"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
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
<?php endif; ?></button></div>
                        </div>
                        <div class="flex items-center justify-between sm:block sm:text-right"><span
                                class="text-xs font-semibold text-store-muted sm:hidden">Subtotal</span><span
                                class="text-sm font-extrabold text-store-red"
                                x-text="`৳${(item.price * item.quantity).toLocaleString()}`"></span></div><button
                            type="button"
                            class="absolute right-4 top-4 text-store-muted hover:text-store-red sm:static"
                            @click="cart = cart.filter(cartItem => cartItem.id !== item.id)"
                            aria-label="Remove item"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'trash','class' => 'size-5 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'size-5 !text-current']); ?>
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
                    </article>
                </template>
            </section>
            <aside class="h-fit rounded-card border border-store-border bg-white p-5 lg:sticky lg:top-5">
                <h2 class="text-lg font-extrabold text-store-ink">Order Summary</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-store-muted">Subtotal</span><span
                            class="font-semibold"
                            x-text="`৳${cart.reduce((total, item) => total + item.price * item.quantity, 0).toLocaleString()}`"></span>
                    </div>
                    <div class="flex justify-between"><span class="text-store-muted">Delivery fee</span><span
                            class="font-semibold text-store-success">Free</span></div>
                    <div class="flex justify-between"><span class="text-store-muted">You save</span><span
                            class="font-semibold text-store-success">৳230</span></div>
                </div>
                <div class="my-5 border-t border-store-border"></div>
                <div class="flex justify-between text-lg font-extrabold text-store-ink"><span>Total</span><span
                        x-text="`৳${cart.reduce((total, item) => total + item.price * item.quantity, 0).toLocaleString()}`"></span>
                </div><a href="<?php echo e(route('store.checkout')); ?>" wire:navigate
                    class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-control bg-store-red text-sm font-bold text-white hover:bg-red-700">Proceed
                    to Checkout</a>
                <p class="mt-3 text-center text-xs text-store-muted">Secure checkout · Cash on Delivery available</p>
            </aside>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $attributes = $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $component = $__componentOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?></main>
<?php /**PATH D:\projects\laravel\storez\resources\views/pages/store/cart-content.blade.php ENDPATH**/ ?>