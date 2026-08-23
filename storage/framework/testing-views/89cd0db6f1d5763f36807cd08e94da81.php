<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'StoreZ']));

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

foreach (array_filter((['title' => 'StoreZ']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $cartItems = \App\Support\StorefrontDemoData::cartItems();
    $categories = \App\Support\StorefrontDemoData::categories();
    $trustItems = \App\Support\StorefrontDemoData::trustItems();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <link rel="icon" type="image/png" href="<?php echo e(asset('images/brand/favicon.png')); ?>">
        <title><?php echo e($title); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body
        x-data="{
            cartOpen: false,
            mobileMenuOpen: false,
            showScrollTop: false,
            cart: <?php echo \Illuminate\Support\Js::from($cartItems)->toHtml() ?>,
            wishlist: <?php echo \Illuminate\Support\Js::from(\App\Support\StorefrontDemoData::wishlistIds())->toHtml() ?>,
            notify(content, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { content, type, duration: 3200 }
                }));
            },
            addToCart(product, quantityToAdd = 1) {
                const item = this.cart.find((cartItem) => cartItem.id === product.id);
                item ? item.quantity += quantityToAdd : this.cart.push({ ...product, quantity: quantityToAdd });
                this.cartOpen = true;
                this.notify(`${product.name} added to cart`);
            },
            toggleWishlist(productId) {
                const isSaved = this.wishlist.includes(productId);
                this.wishlist = isSaved
                    ? this.wishlist.filter((id) => id !== productId)
                    : [...this.wishlist, productId];
                this.notify(isSaved ? 'Removed from wishlist' : 'Added to wishlist', isSaved ? 'info' : 'success');
            },
            changeCartQuantity(item, amount) {
                item.quantity = Math.max(1, item.quantity + amount);
            },
            removeFromCart(productId, productName) {
                this.cart = this.cart.filter((item) => item.id !== productId);
                this.notify(`${productName} removed from cart`, 'info');
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', cartOpen)"
        @keydown.escape.window="cartOpen = false; mobileMenuOpen = false"
        @scroll.window="showScrollTop = window.scrollY > 400"
    >
        <?php if (isset($component)) { $__componentOriginalf0b0fdfcd43cb7443b3900a4af10b338 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0b0fdfcd43cb7443b3900a4af10b338 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.layout.promo-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout.promo-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0b0fdfcd43cb7443b3900a4af10b338)): ?>
<?php $attributes = $__attributesOriginalf0b0fdfcd43cb7443b3900a4af10b338; ?>
<?php unset($__attributesOriginalf0b0fdfcd43cb7443b3900a4af10b338); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0b0fdfcd43cb7443b3900a4af10b338)): ?>
<?php $component = $__componentOriginalf0b0fdfcd43cb7443b3900a4af10b338; ?>
<?php unset($__componentOriginalf0b0fdfcd43cb7443b3900a4af10b338); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalb0dbcc300904605d184ad38a0e5ef47a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb0dbcc300904605d184ad38a0e5ef47a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.layout.header','data' => ['categories' => $categories]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb0dbcc300904605d184ad38a0e5ef47a)): ?>
<?php $attributes = $__attributesOriginalb0dbcc300904605d184ad38a0e5ef47a; ?>
<?php unset($__attributesOriginalb0dbcc300904605d184ad38a0e5ef47a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb0dbcc300904605d184ad38a0e5ef47a)): ?>
<?php $component = $__componentOriginalb0dbcc300904605d184ad38a0e5ef47a; ?>
<?php unset($__componentOriginalb0dbcc300904605d184ad38a0e5ef47a); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal3135b0e9b411be9515ed49273380e418 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3135b0e9b411be9515ed49273380e418 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.layout.desktop-nav','data' => ['categories' => $categories]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout.desktop-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3135b0e9b411be9515ed49273380e418)): ?>
<?php $attributes = $__attributesOriginal3135b0e9b411be9515ed49273380e418; ?>
<?php unset($__attributesOriginal3135b0e9b411be9515ed49273380e418); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3135b0e9b411be9515ed49273380e418)): ?>
<?php $component = $__componentOriginal3135b0e9b411be9515ed49273380e418; ?>
<?php unset($__componentOriginal3135b0e9b411be9515ed49273380e418); ?>
<?php endif; ?>

        <?php echo e($slot); ?>


        <?php if (isset($component)) { $__componentOriginal14588e5249fb292d725b1795ffd6ea35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal14588e5249fb292d725b1795ffd6ea35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.layout.trust-strip','data' => ['items' => $trustItems]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout.trust-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trustItems)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal14588e5249fb292d725b1795ffd6ea35)): ?>
<?php $attributes = $__attributesOriginal14588e5249fb292d725b1795ffd6ea35; ?>
<?php unset($__attributesOriginal14588e5249fb292d725b1795ffd6ea35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal14588e5249fb292d725b1795ffd6ea35)): ?>
<?php $component = $__componentOriginal14588e5249fb292d725b1795ffd6ea35; ?>
<?php unset($__componentOriginal14588e5249fb292d725b1795ffd6ea35); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalc46fdda4c60b86e934125f4096788780 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc46fdda4c60b86e934125f4096788780 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.layout.footer','data' => ['categories' => $categories]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc46fdda4c60b86e934125f4096788780)): ?>
<?php $attributes = $__attributesOriginalc46fdda4c60b86e934125f4096788780; ?>
<?php unset($__attributesOriginalc46fdda4c60b86e934125f4096788780); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc46fdda4c60b86e934125f4096788780)): ?>
<?php $component = $__componentOriginalc46fdda4c60b86e934125f4096788780; ?>
<?php unset($__componentOriginalc46fdda4c60b86e934125f4096788780); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal44b76759fe7255d2aaf57752be4a76b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal44b76759fe7255d2aaf57752be4a76b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout.cart-drawer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout.cart-drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal44b76759fe7255d2aaf57752be4a76b4)): ?>
<?php $attributes = $__attributesOriginal44b76759fe7255d2aaf57752be4a76b4; ?>
<?php unset($__attributesOriginal44b76759fe7255d2aaf57752be4a76b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal44b76759fe7255d2aaf57752be4a76b4)): ?>
<?php $component = $__componentOriginal44b76759fe7255d2aaf57752be4a76b4; ?>
<?php unset($__componentOriginal44b76759fe7255d2aaf57752be4a76b4); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal339c7fedf680433726dbafc2f156956f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal339c7fedf680433726dbafc2f156956f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.toast.index','data' => ['position' => 'top-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['position' => 'top-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $attributes = $__attributesOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__attributesOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $component = $__componentOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__componentOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>

        <div class="fixed bottom-5 right-5 z-40 size-12">
            <a href="https://wa.me/8801700000000?text=Hello%20StoreZ" target="_blank" rel="noopener noreferrer"
                class="absolute bottom-0 left-0 grid size-12 place-items-center rounded-full bg-[#25D366] text-white shadow-lg transition duration-300 ease-out hover:scale-105 hover:bg-[#1ebe5d]"
                :class="showScrollTop ? '-translate-y-15' : 'translate-y-0'"
                aria-label="Chat with StoreZ on WhatsApp">
                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'phone','class' => 'size-6 !text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'phone','class' => 'size-6 !text-white']); ?>
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
            </a>
            <button type="button" x-cloak x-show="showScrollTop" x-transition
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="absolute bottom-0 left-0 grid size-12 place-items-center rounded-full bg-store-blue text-white shadow-lg transition hover:scale-105 hover:bg-store-blue-dark"
                aria-label="Scroll to top">
                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'arrow-up','class' => 'size-6 !text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up','class' => 'size-6 !text-white']); ?>
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
            </button>
        </div>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scriptConfig(); ?>

    </body>
</html>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>