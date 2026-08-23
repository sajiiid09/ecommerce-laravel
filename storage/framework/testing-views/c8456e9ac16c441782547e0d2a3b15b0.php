<?php
    $products = \App\Support\StorefrontDemoData::products();
    $related = collect($products)->reject(fn (array $item) => $item['id'] === $product['id'])->take(6);
?>
<main x-data="{ quantity: 1, wished: false, selectedSize: '5kg' }" class="bg-white py-6 sm:py-8"><?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.breadcrumb','data' => ['items' => [['label' => 'All Products', 'url' => route('store.category')], ['label' => $product['name']]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'All Products', 'url' => route('store.category')], ['label' => $product['name']]])]); ?>
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
<?php endif; ?><div class="grid gap-8 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]"><section class="grid gap-3 sm:grid-cols-[88px_minmax(0,1fr)]"><div class="order-2 flex gap-2 overflow-x-auto sm:order-1 sm:flex-col"><button type="button" class="size-20 shrink-0 rounded-control border-2 border-store-blue bg-white p-2"><img src="<?php echo e(asset(ltrim($product['image'], '/'))); ?>" alt="<?php echo e($product['name']); ?>" class="size-full object-contain"></button><button type="button" class="size-20 shrink-0 rounded-control border border-store-border bg-white p-2"><img src="<?php echo e(asset(ltrim($product['image'], '/'))); ?>" alt="<?php echo e($product['name']); ?>" class="size-full object-contain"></button></div><div class="order-1 flex aspect-square items-center justify-center rounded-card border border-store-border bg-white p-8 sm:order-2"><img src="<?php echo e(asset(ltrim($product['image'], '/'))); ?>" alt="<?php echo e($product['name']); ?>" class="size-full object-contain"></div></section><section><div class="flex items-start justify-between gap-4"><div><p class="text-sm font-semibold text-store-blue"><?php echo e($product['brand']); ?></p><h1 class="mt-1 text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl"><?php echo e($product['name']); ?></h1></div><button type="button" class="grid size-11 shrink-0 place-items-center rounded-full border border-store-border text-store-muted hover:border-store-red hover:text-store-red" @click="wished = !wished" :aria-label="wished ? 'Remove from wishlist' : 'Add to wishlist'"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
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
<?php endif; ?></button></div><div class="mt-3 flex flex-wrap items-center gap-3"><?php if (isset($component)) { $__componentOriginal2204ff34acdbc9251ea7783560d4d3e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2204ff34acdbc9251ea7783560d4d3e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.rating','data' => ['rating' => $product['rating'],'reviews' => $product['reviews']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['rating']),'reviews' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['reviews'])]); ?>
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
<?php endif; ?><span class="text-store-border">|</span><span class="text-sm text-store-muted">10K+ Sold</span></div><div class="mt-5 flex flex-wrap items-center gap-3"><?php if (isset($component)) { $__componentOriginala9c2fcf5cbbd09be7a2e058ff30341d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9c2fcf5cbbd09be7a2e058ff30341d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.price','data' => ['price' => $product['price'],'oldPrice' => $product['oldPrice'],'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['price']),'old-price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['oldPrice']),'size' => 'lg']); ?>
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
<?php endif; ?><?php if (isset($component)) { $__componentOriginal24e77558fbc76ecf180764476508205f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24e77558fbc76ecf180764476508205f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.discount-badge','data' => ['discount' => $product['discount'],'class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['discount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product['discount']),'class' => 'text-xs']); ?>
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
<?php endif; ?></div><p class="mt-2 flex items-center gap-1 text-sm font-semibold text-store-success"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'check-circle','variant' => 'solid','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','variant' => 'solid','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?> Save ৳<?php echo e(number_format(($product['oldPrice'] ?? $product['price']) - $product['price'])); ?> on this product</p><div class="my-5 border-t border-store-border"></div><p class="text-sm font-semibold text-store-ink">Availability: <span class="ml-2 inline-flex items-center gap-1 font-medium text-store-success"><span class="size-2 rounded-full bg-store-success"></span>In Stock</span></p><ul class="mt-4 list-disc space-y-1 pl-5 text-sm leading-6 text-store-text"><li>Extra long grain premium quality</li><li>Naturally aromatic, fluffy and non-sticky</li><li>Perfect for biryani, pulao, fried rice and daily meals</li><li>Carefully packed for retail freshness</li></ul><div class="mt-5"><p class="text-sm font-semibold text-store-ink">Size / Weight: <span class="ml-2 font-normal text-store-muted" x-text="selectedSize"></span></p><div class="mt-2 flex flex-wrap gap-2"><button type="button" class="rounded-full border px-4 py-2 text-sm" :class="selectedSize === '1kg' ? 'border-store-blue bg-store-blue-soft font-bold text-store-blue' : 'border-store-border'" @click="selectedSize = '1kg'">1kg ৳210</button><button type="button" class="rounded-full border px-4 py-2 text-sm" :class="selectedSize === '5kg' ? 'border-store-blue bg-store-blue-soft font-bold text-store-blue' : 'border-store-border'" @click="selectedSize = '5kg'">5kg ৳950</button><button type="button" class="rounded-full border px-4 py-2 text-sm" :class="selectedSize === '10kg' ? 'border-store-blue bg-store-blue-soft font-bold text-store-blue' : 'border-store-border'" @click="selectedSize = '10kg'">10kg ৳1,780</button></div></div><div class="mt-5 flex flex-wrap items-center gap-3"><span class="text-sm font-semibold text-store-ink">Quantity:</span><?php if (isset($component)) { $__componentOriginal3f9e90c5f70701b356639049e6e7a67c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f9e90c5f70701b356639049e6e7a67c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.quantity-stepper','data' => ['model' => 'quantity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.quantity-stepper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'quantity']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3f9e90c5f70701b356639049e6e7a67c)): ?>
<?php $attributes = $__attributesOriginal3f9e90c5f70701b356639049e6e7a67c; ?>
<?php unset($__attributesOriginal3f9e90c5f70701b356639049e6e7a67c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3f9e90c5f70701b356639049e6e7a67c)): ?>
<?php $component = $__componentOriginal3f9e90c5f70701b356639049e6e7a67c; ?>
<?php unset($__componentOriginal3f9e90c5f70701b356639049e6e7a67c); ?>
<?php endif; ?></div><div class="mt-5 grid gap-3 sm:grid-cols-2"><button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-control bg-store-blue text-sm font-bold text-white" @click="const found = cart.find(item => item.id === <?php echo e($product['id']); ?>); found ? found.quantity += quantity : cart.push({ ...<?php echo \Illuminate\Support\Js::from($product)->toHtml() ?>, quantity }); cartOpen = true"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-cart','class' => 'size-5 !text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'size-5 !text-white']); ?>
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
<?php endif; ?>Add to Cart</button><button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-control bg-store-red text-sm font-bold text-white"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'bolt','variant' => 'solid','class' => 'size-5 !text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bolt','variant' => 'solid','class' => 'size-5 !text-white']); ?>
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
<?php endif; ?>Buy Now</button></div><button type="button" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-store-muted hover:text-store-red" @click="wished = !wished"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'heart','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?><span x-text="wished ? 'Saved to Wishlist' : 'Add to Wishlist'"></span></button></section></div><section class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div class="flex items-center gap-3 rounded-card border border-store-border p-4"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'banknotes','class' => 'size-7 !text-store-success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'banknotes','class' => 'size-7 !text-store-success']); ?>
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
<?php endif; ?><div><p class="text-sm font-bold text-store-ink">Cash on Delivery</p><p class="text-xs text-store-muted">Pay when you receive</p></div></div><div class="flex items-center gap-3 rounded-card border border-store-border p-4"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'device-phone-mobile','class' => 'size-7 !text-store-red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'device-phone-mobile','class' => 'size-7 !text-store-red']); ?>
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
<?php endif; ?><div><p class="text-sm font-bold text-store-ink">Pay with bKash / Nagad</p><p class="text-xs text-store-muted">100% secure payment</p></div></div><div class="flex items-center gap-3 rounded-card border border-store-border p-4"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'truck','class' => 'size-7 !text-store-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'size-7 !text-store-blue']); ?>
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
<?php endif; ?><div><p class="text-sm font-bold text-store-ink">Fast Delivery</p><p class="text-xs text-store-muted">24–48 hrs in Dhaka</p></div></div><div class="flex items-center gap-3 rounded-card border border-store-border p-4"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'arrow-path','class' => 'size-7 !text-store-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','class' => 'size-7 !text-store-blue']); ?>
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
<?php endif; ?><div><p class="text-sm font-bold text-store-ink">Easy Returns</p><p class="text-xs text-store-muted">7 days return policy</p></div></div></section><section class="mt-8 rounded-card border border-store-border bg-white"><div class="flex gap-6 overflow-x-auto border-b border-store-border px-5"><button type="button" class="border-b-2 border-store-blue py-4 text-sm font-bold text-store-blue">Description</button><button type="button" class="py-4 text-sm font-semibold text-store-muted">Specifications</button><button type="button" class="py-4 text-sm font-semibold text-store-muted">Reviews (<?php echo e(number_format($product['reviews'])); ?>)</button><button type="button" class="py-4 text-sm font-semibold text-store-muted">Shipping & Returns</button></div><div class="grid gap-6 p-5 text-sm leading-7 text-store-text lg:grid-cols-[1.3fr_1fr]"><div><p><?php echo e($product['name']); ?> is carefully selected from trusted suppliers to bring you dependable quality and excellent value. It is packed for freshness and everyday convenience.</p><ul class="mt-4 list-disc space-y-1 pl-5"><li>Premium quality and carefully packed</li><li>Perfect for everyday meals</li><li>Store in a cool, dry place away from sunlight</li></ul></div><dl class="divide-y divide-store-border rounded-control border border-store-border"><div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">Brand</dt><dd><?php echo e($product['brand']); ?></dd></div><div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">Availability</dt><dd>In Stock</dd></div><div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">SKU</dt><dd>STOREZ-<?php echo e($product['id']); ?></dd></div></dl></div></section><section class="mt-8"><div class="mb-4 flex items-center justify-between"><h2 class="text-xl font-extrabold tracking-tight text-store-ink">You May Also Like</h2><a href="<?php echo e(route('store.category')); ?>" wire:navigate class="text-sm font-semibold text-store-blue">View All →</a></div><div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><?php if (isset($component)) { $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc573f75add0e58aeac1c3f6ba4c16330 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.catalog.product-card','data' => ['product' => $item,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.catalog.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item),'compact' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc573f75add0e58aeac1c3f6ba4c16330)): ?>
<?php $attributes = $__attributesOriginalc573f75add0e58aeac1c3f6ba4c16330; ?>
<?php unset($__attributesOriginalc573f75add0e58aeac1c3f6ba4c16330); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc573f75add0e58aeac1c3f6ba4c16330)): ?>
<?php $component = $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330; ?>
<?php unset($__componentOriginalc573f75add0e58aeac1c3f6ba4c16330); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div></section> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $attributes = $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $component = $__componentOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?></main>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/pages/store/product.blade.php ENDPATH**/ ?>