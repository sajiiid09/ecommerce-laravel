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
<?php endif; ?><div><nav class="mb-5 text-xs text-store-muted"><a href="<?php echo e(route('account.orders')); ?>" wire:navigate class="hover:text-store-blue">My Orders</a><span class="mx-2">/</span><span class="text-store-ink">Track Order</span></nav><div class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-extrabold text-store-ink sm:text-3xl">Track Order #<?php echo e($orderData['id']); ?></h1><p class="mt-1 text-sm text-store-muted">Your order is on the way.</p></div><?php if (isset($component)) { $__componentOriginal756bb0dc222e9d9d77c2d621d89b7cdf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal756bb0dc222e9d9d77c2d621d89b7cdf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.status-badge','data' => ['status' => $orderData['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($orderData['status'])]); ?>
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
<?php endif; ?></div><section class="mt-6 rounded-card border border-store-border bg-white p-5 sm:p-8"><div class="relative ml-2 border-l-2 border-store-blue pl-7 sm:ml-5 sm:pl-10"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div class="relative pb-8 last:pb-0"><span class="absolute -left-[2.05rem] grid size-8 place-items-center rounded-full <?php echo e(($event['active'] ?? false) ? 'bg-store-blue text-white' : (($event['completed'] ?? false) ? 'bg-green-100 text-store-success' : 'bg-store-soft text-store-muted')); ?> sm:-left-[3.05rem]"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($event['active'] ?? false)): ?><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'truck','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?><?php elseif(($event['completed'] ?? false)): ?><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'check','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?><?php else: ?><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'clock','class' => 'size-4 !text-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'size-4 !text-current']); ?>
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
<?php endif; ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span><h2 class="text-sm font-bold text-store-ink"><?php echo e($event['label']); ?></h2><p class="mt-1 text-xs text-store-muted"><?php echo e(($event['active'] ?? false) ? 'Today, 9:00 AM' : (($event['completed'] ?? false) ? 'Order update confirmed' : 'Awaiting delivery update')); ?></p><p class="mt-2 text-sm text-store-text"><?php echo e(($event['active'] ?? false) ? 'The delivery partner is heading to you.' : (($event['completed'] ?? false) ? 'Your order has progressed to this stage.' : 'We will notify you when this step is complete.')); ?></p></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div></section></div></div> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $attributes = $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $component = $__componentOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?></main>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/pages/account/tracking-content.blade.php ENDPATH**/ ?>