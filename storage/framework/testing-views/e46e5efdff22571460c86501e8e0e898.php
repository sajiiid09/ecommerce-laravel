<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product', 'compact' => false]));

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

foreach (array_filter((['product', 'compact' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article class="group relative flex min-h-full flex-col overflow-hidden rounded-card border border-store-border bg-white transition duration-200 hover:-translate-y-0.5 hover:shadow-store-soft">
    <div class="relative <?php echo e($compact ? 'p-2 sm:p-3' : 'p-4'); ?>">
        <?php if (isset($component)) { $__componentOriginal24e77558fbc76ecf180764476508205f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24e77558fbc76ecf180764476508205f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.discount-badge','data' => ['discount' => $product['discount'],'class' => 'absolute left-2 top-2 z-10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['discount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['discount']),'class' => 'absolute left-2 top-2 z-10']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal24e77558fbc76ecf180764476508205f)): ?>
<?php $attributes = $__attributesOriginal24e77558fbc76ecf180764476508205f; ?>
<?php unset($__attributesOriginal24e77558fbc76ecf180764476508205f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal24e77558fbc76ecf180764476508205f)): ?>
<?php $component = $__componentOriginal24e77558fbc76ecf180764476508205f; ?>
<?php unset($__componentOriginal24e77558fbc76ecf180764476508205f); ?>
<?php endif; ?>
        <button type="button" class="absolute right-2 top-2 z-10 grid size-9 place-items-center rounded-full bg-white/90 text-store-muted transition hover:text-store-red" @click="toggleWishlist(<?php echo e($product['id']); ?>)" :aria-label="wishlist.includes(<?php echo e($product['id']); ?>) ? 'Remove from wishlist' : 'Add to wishlist'" :class="wishlist.includes(<?php echo e($product['id']); ?>) && 'text-store-red'"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'heart','class' => 'size-5 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart','class' => 'size-5 !text-current']); ?>
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
<?php endif; ?><span class="sr-only" x-text="wishlist.includes(<?php echo e($product['id']); ?>) ? 'Saved' : 'Save'" /></button>
        <a href="<?php echo e(route('store.product', ['slug' => $product['slug']])); ?>" wire:navigate class="block aspect-square"><img src="<?php echo e(asset(ltrim($product['image'], '/'))); ?>" alt="<?php echo e($product['name']); ?>" loading="lazy" class="size-full object-contain transition duration-200 group-hover:scale-[1.03]"></a>
    </div>
    <div class="flex flex-1 flex-col px-3 pb-3 sm:px-4 sm:pb-4">
        <p class="text-[11px] text-store-muted"><?php echo e($product['brand']); ?></p>
        <a href="<?php echo e(route('store.product', ['slug' => $product['slug']])); ?>" wire:navigate class="mt-1 line-clamp-2 min-h-10 text-sm font-semibold leading-5 text-store-ink hover:text-store-blue"><?php echo e($product['name']); ?></a>
        <?php if (isset($component)) { $__componentOriginala9c2fcf5cbbd09be7a2e058ff30341d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9c2fcf5cbbd09be7a2e058ff30341d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.price','data' => ['price' => $product['price'],'oldPrice' => $product['oldPrice'],'size' => 'sm','class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['price']),'old-price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['oldPrice']),'size' => 'sm','class' => 'mt-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala9c2fcf5cbbd09be7a2e058ff30341d8)): ?>
<?php $attributes = $__attributesOriginala9c2fcf5cbbd09be7a2e058ff30341d8; ?>
<?php unset($__attributesOriginala9c2fcf5cbbd09be7a2e058ff30341d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala9c2fcf5cbbd09be7a2e058ff30341d8)): ?>
<?php $component = $__componentOriginala9c2fcf5cbbd09be7a2e058ff30341d8; ?>
<?php unset($__componentOriginala9c2fcf5cbbd09be7a2e058ff30341d8); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal2204ff34acdbc9251ea7783560d4d3e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.rating','data' => ['rating' => $product['rating'],'reviews' => $product['reviews'],'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['rating']),'reviews' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['reviews']),'class' => 'mt-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2)): ?>
<?php $attributes = $__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2; ?>
<?php unset($__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2204ff34acdbc9251ea7783560d4d3e2)): ?>
<?php $component = $__componentOriginal2204ff34acdbc9251ea7783560d4d3e2; ?>
<?php unset($__componentOriginal2204ff34acdbc9251ea7783560d4d3e2); ?>
<?php endif; ?>
        <button type="button" class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-control bg-store-blue px-3 text-sm font-bold text-white transition hover:bg-store-blue-dark" @click="addToCart(<?php echo \Illuminate\Support\Js::from($product)->toHtml() ?>)"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-cart','class' => 'size-4 !text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'size-4 !text-white']); ?>
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
<?php endif; ?>Add to Cart</button>
    </div>
</article>
<?php /**PATH D:\projects\laravel\storez\resources\views/components/store/catalog/product-card.blade.php ENDPATH**/ ?>