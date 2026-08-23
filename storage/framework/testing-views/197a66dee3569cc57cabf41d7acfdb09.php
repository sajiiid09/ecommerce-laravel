<main x-data="{ step: 1, address: 'home', delivery: 'standard', payment: 'cod' }" class="bg-store-soft py-6 sm:py-8"><?php if (isset($component)) { $__componentOriginal762f9af6429e83d1a3af23c4cf80265f = $component; } ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.ui.breadcrumb','data' => ['items' => [['label' => 'Cart', 'url' => route('store.cart')], ['label' => 'Checkout']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.ui.breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Cart', 'url' => route('store.cart')], ['label' => 'Checkout']])]); ?>
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
<?php endif; ?><h1 class="text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">Checkout</h1><div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]"><section class="space-y-5"><div class="flex items-center gap-2 rounded-card border border-store-border bg-white p-4 text-sm"><span class="grid size-8 place-items-center rounded-full bg-store-blue text-xs font-bold text-white">1</span><span class="font-bold text-store-ink">Address</span><span class="mx-1 h-px flex-1 bg-store-border"></span><span class="grid size-8 place-items-center rounded-full" :class="step >= 2 ? 'bg-store-blue text-white' : 'bg-store-soft text-store-muted'">2</span><span class="font-semibold" :class="step >= 2 ? 'text-store-ink' : 'text-store-muted'">Delivery</span><span class="mx-1 h-px flex-1 bg-store-border"></span><span class="grid size-8 place-items-center rounded-full" :class="step >= 3 ? 'bg-store-blue text-white' : 'bg-store-soft text-store-muted'">3</span><span class="font-semibold" :class="step >= 3 ? 'text-store-ink' : 'text-store-muted'">Payment</span></div><div x-show="step === 1" class="rounded-card border border-store-border bg-white p-5"><div class="flex items-center justify-between"><h2 class="text-lg font-extrabold text-store-ink">Delivery Address</h2><button type="button" class="text-sm font-semibold text-store-blue">+ Add new</button></div><label class="mt-5 flex cursor-pointer gap-3 rounded-control border-2 border-store-blue bg-store-blue-soft p-4"><input type="radio" name="address" value="home" x-model="address" class="mt-1 text-store-blue focus:ring-store-blue"><span><span class="block font-bold text-store-ink">Home · Alex Morgan</span><span class="mt-1 block text-sm text-store-muted">House 12, Road 7, Dhanmondi, Dhaka</span></span></label><button type="button" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-blue text-sm font-bold text-white" @click="step = 2">Continue to Delivery</button></div><div x-show="step === 2" x-cloak class="rounded-card border border-store-border bg-white p-5"><div class="flex items-center justify-between"><h2 class="text-lg font-extrabold text-store-ink">Delivery Method</h2><button type="button" class="text-sm font-semibold text-store-blue" @click="step = 1">Back</button></div><label class="mt-5 flex cursor-pointer gap-3 rounded-control border-2 border-store-blue bg-store-blue-soft p-4"><input type="radio" name="delivery" value="standard" x-model="delivery" class="mt-1 text-store-blue focus:ring-store-blue"><span><span class="block font-bold text-store-ink">Standard Delivery · Free</span><span class="mt-1 block text-sm text-store-muted">Delivered in 24–48 hours in Dhaka</span></span></label><label class="mt-3 flex cursor-pointer gap-3 rounded-control border border-store-border p-4"><input type="radio" name="delivery" value="express" x-model="delivery" class="mt-1 text-store-blue focus:ring-store-blue"><span><span class="block font-bold text-store-ink">Express Delivery · ৳60</span><span class="mt-1 block text-sm text-store-muted">Priority delivery within 12–24 hours</span></span></label><button type="button" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-blue text-sm font-bold text-white" @click="step = 3">Continue to Payment</button></div><div x-show="step === 3" x-cloak class="rounded-card border border-store-border bg-white p-5"><div class="flex items-center justify-between"><h2 class="text-lg font-extrabold text-store-ink">Payment Method</h2><button type="button" class="text-sm font-semibold text-store-blue" @click="step = 2">Back</button></div><label class="mt-5 flex cursor-pointer gap-3 rounded-control border-2 border-store-blue bg-store-blue-soft p-4"><input type="radio" name="payment" value="cod" x-model="payment" class="mt-1 text-store-blue focus:ring-store-blue"><span><span class="block font-bold text-store-ink">Cash on Delivery</span><span class="mt-1 block text-sm text-store-muted">Pay securely when your order arrives</span></span></label><label class="mt-3 flex cursor-pointer gap-3 rounded-control border border-store-border p-4"><input type="radio" name="payment" value="bkash" x-model="payment" class="mt-1 text-store-blue focus:ring-store-blue"><span><span class="block font-bold text-store-ink">bKash / Nagad</span><span class="mt-1 block text-sm text-store-muted">Secure mobile payment</span></span></label><a href="<?php echo e(route('store.order-success')); ?>" wire:navigate class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-red text-sm font-bold text-white">Place Demo Order</a></div></section><aside class="h-fit rounded-card border border-store-border bg-white p-5 lg:sticky lg:top-5"><h2 class="text-lg font-extrabold text-store-ink">Your Order</h2><div class="mt-4 flex items-center gap-3 border-b border-store-border pb-4"><span class="grid size-10 place-items-center rounded-full bg-store-blue-soft text-store-blue"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon.index','data' => ['name' => 'shopping-bag','class' => 'size-5 !text-store-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'size-5 !text-store-blue']); ?>
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
<?php endif; ?></span><div><p class="text-sm font-bold text-store-ink" x-text="`${cart.reduce((total, item) => total + item.quantity, 0)} items`"></p><p class="text-xs text-store-muted">StoreZ demo order</p></div></div><div class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><span class="text-store-muted">Subtotal</span><span x-text="`৳${cart.reduce((total, item) => total + item.price * item.quantity, 0).toLocaleString()}`"></span></div><div class="flex justify-between"><span class="text-store-muted">Delivery</span><span class="text-store-success">Free</span></div></div><div class="my-4 border-t border-store-border"></div><div class="flex justify-between text-lg font-extrabold text-store-ink"><span>Total</span><span x-text="`৳${cart.reduce((total, item) => total + item.price * item.quantity, 0).toLocaleString()}`"></span></div></aside></div> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $attributes = $__attributesOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__attributesOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f)): ?>
<?php $component = $__componentOriginal762f9af6429e83d1a3af23c4cf80265f; ?>
<?php unset($__componentOriginal762f9af6429e83d1a3af23c4cf80265f); ?>
<?php endif; ?></main>
<?php /**PATH C:\xampp\htdocs\storez\resources\views/pages/store/checkout-content.blade.php ENDPATH**/ ?>