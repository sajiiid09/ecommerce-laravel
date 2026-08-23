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

        <div class="grid gap-5 lg:grid-cols-[240px_1fr]"><?php if (isset($component)) { $__componentOriginalccb51c9588b6e7ec422eeedd2c95e5ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalccb51c9588b6e7ec422eeedd2c95e5ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.account.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.account.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalccb51c9588b6e7ec422eeedd2c95e5ee)): ?>
<?php $attributes = $__attributesOriginalccb51c9588b6e7ec422eeedd2c95e5ee; ?>
<?php unset($__attributesOriginalccb51c9588b6e7ec422eeedd2c95e5ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalccb51c9588b6e7ec422eeedd2c95e5ee)): ?>
<?php $component = $__componentOriginalccb51c9588b6e7ec422eeedd2c95e5ee; ?>
<?php unset($__componentOriginalccb51c9588b6e7ec422eeedd2c95e5ee); ?>
<?php endif; ?>
            <div>
                <h1 class="text-2xl font-extrabold text-store-ink sm:text-3xl">My Orders</h1>
                <p class="mt-1 text-sm text-store-muted">View and track your StoreZ orders.</p>
                <div class="mt-6 space-y-4">
                    <article class="rounded-card border border-store-border bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-store-ink">Order #SZ-100248</p>
                                <p class="mt-1 text-xs text-store-muted">Placed August 20, 2026 · 2 items · ৳5,180</p>
                            </div><?php if (isset($component)) { $__componentOriginal756bb0dc222e9d9d77c2d621d89b7cdf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal756bb0dc222e9d9d77c2d621d89b7cdf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.status-badge','data' => ['status' => 'out_for_delivery']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => 'out_for_delivery']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal756bb0dc222e9d9d77c2d621d89b7cdf)): ?>
<?php $attributes = $__attributesOriginal756bb0dc222e9d9d77c2d621d89b7cdf; ?>
<?php unset($__attributesOriginal756bb0dc222e9d9d77c2d621d89b7cdf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal756bb0dc222e9d9d77c2d621d89b7cdf)): ?>
<?php $component = $__componentOriginal756bb0dc222e9d9d77c2d621d89b7cdf; ?>
<?php unset($__componentOriginal756bb0dc222e9d9d77c2d621d89b7cdf); ?>
<?php endif; ?>
                        </div>
                        <div
                            class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-store-border pt-4">
                            <p class="text-sm text-store-muted">Expected delivery: <strong class="text-store-ink">August
                                    24, 2026</strong></p><a
                                href="<?php echo e(route('account.tracking', ['order' => 'SZ-100248'])); ?>" wire:navigate
                                class="inline-flex h-10 items-center rounded-control bg-store-blue px-4 text-sm font-bold text-white">Track
                                Order</a>
                        </div>
                    </article>
                </div>
            </div>
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
<?php /**PATH D:\projects\laravel\storez\resources\views/pages/account/orders-content.blade.php ENDPATH**/ ?>