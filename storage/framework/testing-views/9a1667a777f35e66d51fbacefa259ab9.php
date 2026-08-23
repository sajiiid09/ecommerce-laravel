<?php
    $allProducts = \App\Support\StorefrontDemoData::products();
    $queryLower = strtolower($query);
    $products =
        $query === ''
            ? $allProducts
            : array_values(
                array_filter(
                    $allProducts,
                    fn(array $product) => str_contains(
                        strtolower($product['name'] . ' ' . $product['brand']),
                        $queryLower,
                    ),
                ),
            );
    $brands = collect($allProducts)->pluck('brand')->unique()->values()->all();
    $selectedBrands = (array) request('brand', []);
    if ($selectedBrands) {
        $products = array_values(
            array_filter($products, fn($product) => in_array($product['brand'], $selectedBrands, true)),
        );
    }
    if (request()->boolean('in_stock')) {
        $products = array_values(array_filter($products, fn($product) => $product['inStock']));
    }
    $sort = request('sort', 'relevance');
    if ($sort === 'price_asc') {
        usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
    } elseif ($sort === 'price_desc') {
        usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
    } elseif ($sort === 'rating') {
        usort($products, fn($a, $b) => $b['rating'] <=> $a['rating']);
    }
    $page = max(1, (int) request('page', 1));
    $pageCount = max(1, (int) ceil(count($products) / 12));
?>

<main x-data="{ filtersOpen: false }" class="bg-store-soft py-6 sm:py-8">
    <?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.breadcrumb','data' => ['items' => [['label' => 'Search']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Search']])]); ?>
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
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">
                    <?php echo e($query ? 'Search results for “' . $query . '”' : 'Search all products'); ?></h1>
                <p class="mt-1 text-sm text-store-muted"><?php echo e(count($products)); ?> products found</p>
            </div><button type="button"
                class="inline-flex h-10 items-center gap-2 rounded-control border border-store-blue bg-white px-4 text-sm font-bold text-store-blue lg:hidden"
                @click="filtersOpen = true" aria-controls="search-filters"
                :aria-expanded="filtersOpen.toString()"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'adjustments-horizontal','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'adjustments-horizontal','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?>Filters</button>
        </div>
        <form method="GET" class="mt-6 grid gap-5 lg:grid-cols-[240px_minmax(0,1fr)]">
            <div x-cloak x-show="filtersOpen" x-transition.opacity class="fixed inset-0 z-40 bg-store-ink/45 lg:hidden"
                @click="filtersOpen = false" aria-hidden="true"></div>
            <aside id="search-filters" x-cloak x-show="filtersOpen || window.innerWidth >= 1024" x-transition
                class="fixed inset-y-0 left-0 z-50 h-auto w-[min(88vw,22rem)] overflow-y-auto rounded-none border-r border-store-border bg-white p-4 shadow-2xl lg:static lg:block lg:h-fit lg:w-auto lg:rounded-card lg:border lg:shadow-none"
                @keydown.escape.window="filtersOpen = false">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-store-ink">Filter results</h2>
                    <div class="flex items-center gap-3"><a href="<?php echo e(route('store.search', ['q' => $query])); ?>"
                            class="text-xs font-semibold text-store-blue hover:underline">Clear all</a><button
                            type="button" class="lg:hidden" @click="filtersOpen = false"
                            aria-label="Close filters"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
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
                </div><input type="hidden" name="q" value="<?php echo e($query); ?>">
                <div class="mt-5 space-y-5"><?php if (isset($component)) { $__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.filter-group','data' => ['title' => 'Brands','name' => 'brand','options' => $brands,'selected' => $selectedBrands]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.filter-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Brands','name' => 'brand','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brands),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedBrands)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c)): ?>
<?php $attributes = $__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c; ?>
<?php unset($__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c)): ?>
<?php $component = $__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c; ?>
<?php unset($__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.filter-group','data' => ['title' => 'Availability','name' => 'in_stock','options' => ['In stock only'],'selected' => request('in_stock') ? ['In stock only'] : []]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.filter-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Availability','name' => 'in_stock','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['In stock only']),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('in_stock') ? ['In stock only'] : [])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c)): ?>
<?php $attributes = $__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c; ?>
<?php unset($__attributesOriginaleadf2e85c5e8c348d9a095e7163fe50c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c)): ?>
<?php $component = $__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c; ?>
<?php unset($__componentOriginaleadf2e85c5e8c348d9a095e7163fe50c); ?>
<?php endif; ?></div><button
                    class="mt-5 h-10 w-full rounded-control bg-store-blue text-sm font-bold text-white"
                    @click="filtersOpen = false">Apply filters</button>
            </aside>
            <section>
                <div
                    class="flex flex-wrap items-center justify-between gap-3 rounded-card border border-store-border bg-white p-3">
                    <p class="text-sm text-store-muted">Showing <span
                            class="font-bold text-store-ink"><?php echo e(count($products)); ?></span> results</p>
                    <div class="flex items-center gap-2"><label for="search-sort"
                            class="text-xs font-semibold text-store-muted">Sort by</label><select id="search-sort"
                            name="sort" onchange="this.form.submit()"
                            class="h-10 rounded-control border border-store-border bg-white px-3 text-sm text-store-ink outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10">
                            <option value="relevance" <?php if($sort === 'relevance'): echo 'selected'; endif; ?>>Relevance</option>
                            <option value="price_asc" <?php if($sort === 'price_asc'): echo 'selected'; endif; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php if($sort === 'price_desc'): echo 'selected'; endif; ?>>Price: High to Low</option>
                            <option value="rating" <?php if($sort === 'rating'): echo 'selected'; endif; ?>>Rating</option>
                        </select></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($products)): ?>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($products, ($page - 1) * 12, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc573f75add0e58aeac1c3f6ba4c16330 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.catalog.product-card','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.catalog.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
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
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php if (isset($component)) { $__componentOriginalaace2f446995b0a7ee6075fadb75f884 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaace2f446995b0a7ee6075fadb75f884 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.pagination','data' => ['current' => $page,'total' => $pageCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page),'total' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pageCount)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaace2f446995b0a7ee6075fadb75f884)): ?>
<?php $attributes = $__attributesOriginalaace2f446995b0a7ee6075fadb75f884; ?>
<?php unset($__attributesOriginalaace2f446995b0a7ee6075fadb75f884); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaace2f446995b0a7ee6075fadb75f884)): ?>
<?php $component = $__componentOriginalaace2f446995b0a7ee6075fadb75f884; ?>
<?php unset($__componentOriginalaace2f446995b0a7ee6075fadb75f884); ?>
<?php endif; ?><?php else: ?><div
                        class="mt-4 rounded-card border border-store-border bg-white p-12 text-center"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'magnifying-glass','class' => 'mx-auto size-10 !text-store-placeholder']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'mx-auto size-10 !text-store-placeholder']); ?>
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
                        <h2 class="mt-4 text-lg font-bold text-store-ink">No products found</h2>
                        <p class="mt-1 text-sm text-store-muted">Try a different product, brand, or category.</p><a
                            href="<?php echo e(route('store.home')); ?>" wire:navigate
                            class="mt-5 inline-flex h-10 items-center rounded-control bg-store-blue px-4 py-2 text-sm font-bold text-white">Continue
                            Shopping</a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        </form>
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
</main>
<?php /**PATH D:\projects\laravel\storez\resources\views/pages/store/search-content.blade.php ENDPATH**/ ?>