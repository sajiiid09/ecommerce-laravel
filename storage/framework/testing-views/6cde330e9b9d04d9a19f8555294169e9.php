<?php ($products = \App\Support\StorefrontDemoData::products()); ?>
<main class="bg-store-soft pb-12"><?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
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
<div class="py-8"><p class="text-sm font-semibold text-store-blue">Home / Offers & Deals</p><h1 class="mt-2 text-3xl font-black text-store-ink">Offers & Deals</h1><p class="mt-2 text-store-muted">Save more on limited-time StoreZ promotions.</p></div><section class="rounded-card bg-gradient-to-r from-store-navy to-store-blue p-6 text-white sm:p-10"><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">Weekend mega sale</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">Big deals. Better everyday value.</h2><p class="mt-3 max-w-xl text-blue-100">Explore flash discounts across groceries, electronics, fashion and home essentials.</p><a href="#limited" class="mt-6 inline-flex rounded-control bg-white px-5 py-3 text-sm font-bold text-store-navy">Shop limited deals</a></section><div class="mt-8 flex flex-wrap gap-2"><span class="rounded-full bg-store-blue px-4 py-2 text-sm font-semibold text-white">All Offers</span><span class="rounded-full border border-store-border bg-white px-4 py-2 text-sm text-store-text">Flash Sale</span><span class="rounded-full border border-store-border bg-white px-4 py-2 text-sm text-store-text">Combo Deals</span><span class="rounded-full border border-store-border bg-white px-4 py-2 text-sm text-store-text">Bank Offers</span></div><section id="limited" class="mt-8"><div class="flex items-center justify-between"><?php if (isset($component)) { $__componentOriginal15d95ade1f98e19ae7b13afc718a1837 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15d95ade1f98e19ae7b13afc718a1837 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.section-heading','data' => ['title' => 'Limited-time products']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Limited-time products']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal15d95ade1f98e19ae7b13afc718a1837)): ?>
<?php $attributes = $__attributesOriginal15d95ade1f98e19ae7b13afc718a1837; ?>
<?php unset($__attributesOriginal15d95ade1f98e19ae7b13afc718a1837); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal15d95ade1f98e19ae7b13afc718a1837)): ?>
<?php $component = $__componentOriginal15d95ade1f98e19ae7b13afc718a1837; ?>
<?php unset($__componentOriginal15d95ade1f98e19ae7b13afc718a1837); ?>
<?php endif; ?><span class="font-mono text-sm font-bold text-store-red">02 : 45 : 30</span></div><div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($products, 0, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><?php if (isset($component)) { $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc573f75add0e58aeac1c3f6ba4c16330 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.catalog.product-card','data' => ['product' => $product,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.catalog.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'compact' => true]); ?>
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
<?php /**PATH C:\xampp\htdocs\storez\resources\views/pages/store/offers-content.blade.php ENDPATH**/ ?>