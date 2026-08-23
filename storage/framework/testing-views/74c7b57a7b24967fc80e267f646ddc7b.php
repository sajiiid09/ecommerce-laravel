<main class="bg-store-soft py-12 sm:py-16"><?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
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
<section class="rounded-card border border-store-border bg-white p-6 shadow-store-soft sm:p-8"><h1 class="text-2xl font-extrabold text-store-ink">Create your StoreZ account</h1><p class="mt-1 text-sm text-store-muted">Join for easier checkout and order tracking.</p><form action="<?php echo e(route('account.dashboard')); ?>" class="mt-7 space-y-4"><div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-semibold text-store-ink">First name<input required class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label><label class="block text-sm font-semibold text-store-ink">Last name<input required class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label></div><label class="block text-sm font-semibold text-store-ink">Email address<input type="email" required class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label><label class="block text-sm font-semibold text-store-ink">Password<input type="password" required class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label><label class="flex items-start gap-2 text-sm text-store-muted"><input type="checkbox" required class="mt-0.5 size-4 rounded border-store-border text-store-blue">I agree to the StoreZ terms and privacy policy.</label><button class="h-11 w-full rounded-control bg-store-blue text-sm font-bold text-white hover:bg-store-blue-dark">Create Account</button></form><p class="mt-6 text-center text-sm text-store-muted">Already have an account? <a href="<?php echo e(route('login')); ?>" wire:navigate class="font-bold text-store-blue">Sign in</a></p></section> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $attributes = $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $component = $__componentOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?></main>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/pages/auth/register-content.blade.php ENDPATH**/ ?>