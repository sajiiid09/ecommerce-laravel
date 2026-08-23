<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['categories' => []]));

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

foreach (array_filter((['categories' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<footer class="bg-store-navy text-slate-200">
    <?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.container','data' => ['class' => 'py-10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'py-10']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
            <div><h2 class="text-base font-bold text-white">About StoreZ</h2><p class="mt-3 text-sm leading-6 text-blue-100">Your trusted online shopping destination in Bangladesh.</p><div class="mt-4 flex gap-3"><a href="#facebook" aria-label="Facebook" class="text-blue-100 hover:text-white">Facebook</a><a href="#instagram" aria-label="Instagram" class="text-blue-100 hover:text-white">Instagram</a></div></div>
            <div><h2 class="text-base font-bold text-white">Customer Service</h2><ul class="mt-3 space-y-2 text-sm"><li><a href="#help" class="hover:text-white">Help Center</a></li><li><a href="#returns" class="hover:text-white">Returns & Refunds</a></li><li><a href="#shipping" class="hover:text-white">Shipping Info</a></li><li><a href="#contact" class="hover:text-white">Contact Us</a></li></ul></div>
            <div><h2 class="text-base font-bold text-white">My Account</h2><ul class="mt-3 space-y-2 text-sm"><li><a href="#orders" class="hover:text-white">My Orders</a></li><li><a href="#wishlist" class="hover:text-white">Wishlist</a></li><li><a href="#track" class="hover:text-white">Track Order</a></li><li><a href="#settings" class="hover:text-white">Account Settings</a></li></ul></div>
            <div><h2 class="text-base font-bold text-white">Popular Categories</h2><ul class="mt-3 space-y-2 text-sm"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($categories, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><li><a href="#<?php echo e($category['slug']); ?>" class="hover:text-white"><?php echo e($category['name']); ?></a></li><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></ul></div>
            <div><h2 class="text-base font-bold text-white">Stay in the loop</h2><p class="mt-3 text-sm leading-6">Get offers and product updates in your inbox.</p><form class="mt-4 flex" action="#newsletter"><label for="newsletter-email" class="sr-only">Email address</label><input id="newsletter-email" type="email" placeholder="Your email" class="min-w-0 flex-1 rounded-l-control border-0 px-3 py-2 text-sm text-store-ink outline-none"><button class="rounded-r-control bg-store-blue px-4 text-sm font-semibold text-white hover:bg-store-blue-dark">Subscribe</button></form></div>
        </div>
        <div class="mt-10 flex flex-col gap-3 border-t border-white/15 pt-5 text-xs sm:flex-row sm:items-center sm:justify-between"><p>© <?php echo e(now()->year); ?> StoreZ. All rights reserved.</p><div class="flex gap-4"><a href="#terms" class="hover:text-white">Terms & Conditions</a><a href="#privacy" class="hover:text-white">Privacy Policy</a></div><span class="font-semibold text-white">Visa · Mastercard · bKash · Nagad · COD</span></div>
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
</footer>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/components/store/layout/footer.blade.php ENDPATH**/ ?>