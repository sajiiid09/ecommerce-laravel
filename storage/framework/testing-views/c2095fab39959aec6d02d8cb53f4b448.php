<?php
    $products = \App\Support\StorefrontDemoData::products();
    $categories = \App\Support\StorefrontDemoData::categories();
    $homepageCategories = \App\Support\StorefrontDemoData::homepageCategories();
    $categoryIcons = array_column($homepageCategories, 'icon');
    $categoryNames = array_column($homepageCategories, 'name');
    $brands = array_map(fn($brand) => strtoupper($brand['name']), \App\Support\StorefrontDemoData::brands());
    $combos = \App\Support\StorefrontDemoData::combos();
?>

<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'StoreZ — Shop Smarter']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'StoreZ — Shop Smarter']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <main class="bg-white pb-10">
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

            <div class="grid items-stretch gap-4 pt-5 lg:grid-cols-[220px_minmax(0,1fr)_280px]">
                <aside class="hidden h-full rounded-card border border-store-border bg-white p-3 lg:block">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categoryNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e(route('store.category')); ?>" wire:navigate
                            class="flex items-center justify-between border-b border-store-border px-2 py-3 text-sm last:border-0 hover:text-store-blue"><span
                                class="flex min-w-0 items-center gap-2"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => $categoryIcons[$index],'class' => 'size-4 shrink-0 !text-store-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categoryIcons[$index]),'class' => 'size-4 shrink-0 !text-store-blue']); ?>
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
<?php endif; ?><span
                                    class="truncate"><?php echo e($name); ?></span></span><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'chevron-right','class' => 'size-4 !text-store-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'size-4 !text-store-muted']); ?>
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
<?php endif; ?></a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </aside>

                <?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'autoplay' => true,'label' => 'Featured promotions','showDots' => false,'showControls' => false,'class' => 'h-full min-w-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'autoplay' => true,'label' => 'Featured promotions','show-dots' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'show-controls' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'h-full min-w-0']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <article
                        class="relative h-full min-h-[340px] w-full shrink-0 overflow-hidden rounded-card bg-gradient-to-br from-[#0e55a8] via-[#063875] to-[#052b5d] p-7 text-white sm:p-10">
                        <div class="relative z-10 max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">StoreZ everyday value
                            </p>
                            <h1 class="mt-3 text-4xl font-black leading-[1.05] sm:text-5xl">Back to<br>Better
                                Deals<br><span class="text-yellow-300">Every Day!</span></h1>
                            <p class="mt-4 max-w-md text-base text-blue-100">Groceries, fashion, electronics & more at
                                unbeatable prices.</p><a href="<?php echo e(route('store.offers')); ?>" wire:navigate
                                class="mt-6 inline-flex rounded-full bg-white px-6 py-3 text-sm font-bold text-store-navy transition hover:bg-blue-50">Shop
                                Now</a>
                        </div>
                        <div
                            class="pointer-events-none absolute -right-12 -bottom-20 size-64 rounded-full bg-store-blue/70 blur-2xl sm:size-80">
                        </div><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-bag','class' => 'pointer-events-none absolute right-10 top-10 size-32 !text-blue-200/30 sm:size-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'pointer-events-none absolute right-10 top-10 size-32 !text-blue-200/30 sm:size-40']); ?>
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
                    </article>
                    <article
                        class="relative min-h-[340px] w-full shrink-0 overflow-hidden rounded-card bg-gradient-to-br from-[#b91c1c] via-[#dc2626] to-[#7f1d1d] p-7 text-white sm:p-10">
                        <div class="relative z-10 max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-100">StoreZ fashion festival
                            </p>
                            <h1 class="mt-3 text-4xl font-black leading-[1.05] sm:text-5xl">Fresh Looks<br><span
                                    class="text-yellow-300">Big Savings!</span></h1>
                            <p class="mt-4 max-w-md text-base text-red-100">Trending styles and everyday essentials at
                                prices you’ll love.</p><a href="#offers"
                                class="mt-6 inline-flex rounded-full bg-white px-6 py-3 text-sm font-bold text-red-700 transition hover:bg-red-50">Shop
                                Fashion</a>
                        </div>
                        
                    </article>
                    <article
                        class="relative min-h-[340px] w-full shrink-0 overflow-hidden rounded-card bg-gradient-to-br from-[#047857] via-[#059669] to-[#064e3b] p-7 text-white sm:p-10">
                        <div class="relative z-10 max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">StoreZ fresh picks
                            </p>
                            <h1 class="mt-3 text-4xl font-black leading-[1.05] sm:text-5xl">Everyday<br><span
                                    class="text-yellow-300">Essentials, Less!</span></h1>
                            <p class="mt-4 max-w-md text-base text-emerald-100">Stock up on groceries and home favorites
                                with dependable delivery.</p><a href="#offers"
                                class="mt-6 inline-flex rounded-full bg-white px-6 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50">Shop
                                Groceries</a>
                        </div><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-cart','class' => 'pointer-events-none absolute right-10 top-10 size-32 !text-emerald-100/30 sm:size-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'pointer-events-none absolute right-10 top-10 size-32 !text-emerald-100/30 sm:size-40']); ?>
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
                    </article>
                    
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>

                <div class="grid h-full gap-3 lg:min-w-0">
                    <article class="h-full rounded-card border border-store-border bg-blue-50 p-5">
                        <h2 class="text-xl font-extrabold text-store-blue">Up to 40% Off<br>On Laptops</h2>
                        <p class="mt-2 text-xs text-store-muted">Top Brands | Warranty</p><a href="#offers"
                            class="mt-4 inline-flex rounded-control bg-store-blue px-4 py-2 text-xs font-bold text-white hover:bg-store-blue-dark">Shop
                            Now</a>
                    </article>
                    <article class="h-full rounded-card border border-store-border bg-red-50 p-5">
                        <h2 class="text-xl font-extrabold text-store-red">Fashion Fest<br>Up to 60% Off</h2>
                        <p class="mt-2 text-xs text-store-muted">Trendy Looks, Lowest Prices</p><a href="#offers"
                            class="mt-4 inline-flex rounded-control bg-store-red px-4 py-2 text-xs font-bold text-white hover:bg-red-700">Shop
                            Now</a>
                    </article>
                </div>
            </div>

            <section id="category" class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold tracking-tight text-store-ink">Shop by Category</h2><a
                        href="#categories" class="text-sm font-semibold text-store-blue hover:underline">View All →</a>
                </div>
                <?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'label' => 'Shop by category','showDots' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'label' => 'Shop by category','show-dots' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categoryNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="w-[calc((100%-0.75rem)/2)] shrink-0 sm:w-[calc((100%-1.5rem)/3)] md:w-[calc((100%-2.25rem)/4)] xl:w-[calc((100%-3.75rem)/6)]">
                            <a href="#category"
                                class="block rounded-card border border-store-border bg-white p-3 text-center transition hover:-translate-y-0.5 hover:shadow-store-soft"><span
                                    class="mx-auto flex aspect-square w-full items-center justify-center rounded-control bg-store-soft px-2 text-center"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => $categoryIcons[$index],'class' => 'size-8 !text-store-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categoryIcons[$index]),'class' => 'size-8 !text-store-blue']); ?>
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
<?php endif; ?></span><span
                                    class="mt-2 block text-xs font-semibold text-store-ink"><?php echo e($name); ?></span></a>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
            </section>

            <section id="offers" class="mt-8">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-extrabold tracking-tight text-store-ink">Flash Sale</h2><span
                            class="rounded bg-store-red/10 px-2 py-1 font-mono text-sm font-bold text-store-red">02 : 45
                            : 30</span><span class="hidden text-xs text-store-muted sm:inline">Hurry! Limited time
                            offers</span>
                    </div><a href="#offers" class="text-sm font-semibold text-store-blue hover:underline">View All Deals
                        →</a>
                </div>
                <?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'label' => 'Flash sale products','showDots' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'label' => 'Flash sale products','show-dots' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($products, 3, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="w-[calc((100%-0.75rem)/2)] shrink-0 md:w-[calc((100%-1.5rem)/3)] xl:w-[calc((100%-3rem)/5)]"><?php if (isset($component)) { $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330 = $component; } ?>
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
<?php endif; ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
            </section>

            <section
                class="mt-8 rounded-card border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-100 p-4 sm:p-5">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-extrabold text-store-ink">Smart Combo Deals</h2>
                        <p class="text-xs text-store-muted">More Items, More Savings!</p>
                    </div><a href="#offers" class="text-sm font-semibold text-store-blue hover:underline">View All
                        Combos →</a>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $combos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $combo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="flex items-center gap-3 rounded-card border border-store-border bg-white p-3">
                            <img src="<?php echo e(asset('images/placeholders/no-image.svg')); ?>" alt=""
                                class="size-20 object-contain">
                            <div>
                                <h3 class="text-sm font-bold text-store-ink"><?php echo e($combo['name']); ?></h3>
                                <p class="mt-1 font-extrabold text-store-red">৳<?php echo e(number_format($combo['price'])); ?></p>
                                <p class="text-xs font-bold text-store-success">Save
                                    ৳<?php echo e(number_format($combo['save'])); ?></p><button type="button"
                                    class="mt-2 rounded bg-store-blue px-3 py-1.5 text-[11px] font-bold text-white hover:bg-store-blue-dark"
                                    @click="cartOpen = true">Add to Cart</button>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>

            <section class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold tracking-tight text-store-ink">Best Sellers</h2><a
                        href="#category" class="text-sm font-semibold text-store-blue hover:underline">View All →</a>
                </div>
                <?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'label' => 'Best seller products','showDots' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'label' => 'Best seller products','show-dots' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($products, 9, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="w-[calc((100%-0.75rem)/2)] shrink-0 md:w-[calc((100%-1.5rem)/3)] xl:w-[calc((100%-3rem)/5)]"><?php if (isset($component)) { $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330 = $component; } ?>
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
<?php endif; ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
            </section>

            <section id="brands" class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold tracking-tight text-store-ink">Top Brands You Trust</h2><a
                        href="#brands" class="text-sm font-semibold text-store-blue hover:underline">View All Brands
                        &rarr;</a>
                </div><?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'label' => 'Top brands']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'label' => 'Top brands']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article
                            class="w-[calc((100%-0.75rem)/2)] shrink-0 sm:w-[calc((100%-2.25rem)/4)] lg:w-[calc((100%-3.75rem)/6)]">
                            <a href="#brands"
                                class="grid min-h-16 h-full place-items-center rounded-card border border-store-border bg-white p-3 text-center text-sm font-black text-store-blue transition hover:border-store-blue"><?php echo e($brand); ?></a>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
            </section>

            <section class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-store-ink">Fresh Picks for You</h2>
                        <p class="text-xs text-store-muted">Handpicked trending items</p>
                    </div><a href="#category" class="text-sm font-semibold text-store-blue hover:underline">View All
                        →</a>
                </div>
                <?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'label' => 'Fresh picks','showDots' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'label' => 'Fresh picks','show-dots' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($products, 3, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="w-[calc((100%-0.75rem)/2)] shrink-0 md:w-[calc((100%-1.5rem)/3)] xl:w-[calc((100%-3rem)/5)]"><?php if (isset($component)) { $__componentOriginalc573f75add0e58aeac1c3f6ba4c16330 = $component; } ?>
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
<?php endif; ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
            </section>

            <section class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold tracking-tight text-store-ink">What Our Customers Say</h2><span
                        class="text-sm text-store-muted">Real feedback from StoreZ shoppers</span>
                </div><?php if (isset($component)) { $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.carousel','data' => ['loop' => true,'label' => 'Customer testimonials']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loop' => true,'label' => 'Customer testimonials']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php if (isset($component)) { $__componentOriginalfc16553613f0db2469293dd3b5061f24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc16553613f0db2469293dd3b5061f24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.testimonial-card','data' => ['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Nusrat Jahan','quote' => 'Great experience! Fast delivery and products were exactly as described. StoreZ is my new go-to shop.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.testimonial-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Nusrat Jahan','quote' => 'Great experience! Fast delivery and products were exactly as described. StoreZ is my new go-to shop.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $attributes = $__attributesOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__attributesOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $component = $__componentOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__componentOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginalfc16553613f0db2469293dd3b5061f24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc16553613f0db2469293dd3b5061f24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.testimonial-card','data' => ['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Rafiq Ahmed','quote' => 'Love the combo deals and COD option. Very convenient and trustworthy platform.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.testimonial-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Rafiq Ahmed','quote' => 'Love the combo deals and COD option. Very convenient and trustworthy platform.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $attributes = $__attributesOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__attributesOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $component = $__componentOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__componentOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginalfc16553613f0db2469293dd3b5061f24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc16553613f0db2469293dd3b5061f24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.testimonial-card','data' => ['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Tania Rahman','quote' => 'Quality products, best prices and excellent customer service. Highly recommended.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.testimonial-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Tania Rahman','quote' => 'Quality products, best prices and excellent customer service. Highly recommended.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $attributes = $__attributesOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__attributesOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $component = $__componentOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__componentOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginalfc16553613f0db2469293dd3b5061f24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc16553613f0db2469293dd3b5061f24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.testimonial-card','data' => ['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Farhan Kabir','quote' => 'The product selection is excellent and delivery updates made every order easy to follow.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.testimonial-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Farhan Kabir','quote' => 'The product selection is excellent and delivery updates made every order easy to follow.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $attributes = $__attributesOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__attributesOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $component = $__componentOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__componentOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginalfc16553613f0db2469293dd3b5061f24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc16553613f0db2469293dd3b5061f24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.testimonial-card','data' => ['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Maliha Sultana','quote' => 'I found exactly what I needed at a fair price, and the checkout was quick and simple.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.testimonial-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Maliha Sultana','quote' => 'I found exactly what I needed at a fair price, and the checkout was quick and simple.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $attributes = $__attributesOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__attributesOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $component = $__componentOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__componentOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginalfc16553613f0db2469293dd3b5061f24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc16553613f0db2469293dd3b5061f24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.testimonial-card','data' => ['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Imran Hossain','quote' => 'Fast service, helpful support, and reliable packaging. I’ll definitely shop from StoreZ again.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.testimonial-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[calc((100%-0rem)/1)] shrink-0 snap-start sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]','name' => 'Imran Hossain','quote' => 'Fast service, helpful support, and reliable packaging. I’ll definitely shop from StoreZ again.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $attributes = $__attributesOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__attributesOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc16553613f0db2469293dd3b5061f24)): ?>
<?php $component = $__componentOriginalfc16553613f0db2469293dd3b5061f24; ?>
<?php unset($__componentOriginalfc16553613f0db2469293dd3b5061f24); ?>
<?php endif; ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $attributes = $__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__attributesOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a)): ?>
<?php $component = $__componentOriginal6d7f575da35feab3b8b3c3ef261c150a; ?>
<?php unset($__componentOriginal6d7f575da35feab3b8b3c3ef261c150a); ?>
<?php endif; ?>
            </section>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH D:\projects\laravel\storez\resources\views/pages/store/home.blade.php ENDPATH**/ ?>