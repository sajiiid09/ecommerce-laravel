<main class="bg-store-soft py-12 sm:py-20"><?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.container','data' => ['size' => 'narrow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'narrow']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <section class="rounded-card border border-store-border bg-white p-8 text-center shadow-store-soft sm:p-12"><span
                class="mx-auto grid size-16 place-items-center rounded-full bg-green-100 text-store-success"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'check','class' => 'size-8 !text-store-success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-8 !text-store-success']); ?>
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
<?php endif; ?></span>
            <p class="mt-6 text-sm font-semibold uppercase tracking-[0.16em] text-store-success">Order confirmed</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-store-ink">Thanks for shopping with StoreZ!</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-store-muted">Your demo order has been placed
                successfully. We’ll keep you updated as it moves toward your delivery address.</p>
            <div class="mx-auto mt-6 max-w-sm rounded-control bg-store-soft p-4 text-left text-sm">
                <div class="flex justify-between"><span class="text-store-muted">Order ID</span><strong
                        class="text-store-ink">SZ-100248</strong></div>
                <div class="mt-2 flex justify-between"><span class="text-store-muted">Delivery estimate</span><strong
                        class="text-store-ink">24–48 hours</strong></div>
            </div>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row"><a href="<?php echo e(route('store.home')); ?>"
                    wire:navigate
                    class="inline-flex h-11 items-center justify-center rounded-control bg-store-blue px-5 text-sm font-bold text-white">Continue
                    Shopping</a><a href="<?php echo e(route('account.tracking', ['order' => 'SZ-100248'])); ?>" wire:navigate
                    class="inline-flex h-11 items-center justify-center rounded-control border border-store-blue px-5 text-sm font-bold text-store-blue">Track
                    Order</a></div>
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
<?php endif; ?></main>
<?php /**PATH D:\projects\laravel\storez\resources\views/pages/store/order-success-content.blade.php ENDPATH**/ ?>