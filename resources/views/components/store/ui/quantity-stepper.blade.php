@props(['model' => 'quantity', 'label' => 'Quantity'])

<div {{ $attributes->class('inline-flex h-11 items-center rounded-control border border-store-border bg-white') }}>
    <button type="button" class="grid size-10 place-items-center text-store-ink hover:bg-store-soft" @click="{{ $model }} = Math.max(1, {{ $model }} - 1)" aria-label="Decrease {{ strtolower($label) }}"><x-ui.icon name="minus" class="size-4 !text-current" /></button>
    <output class="w-8 text-center text-sm font-bold text-store-ink" x-text="{{ $model }}"></output>
    <button type="button" class="grid size-10 place-items-center text-store-ink hover:bg-store-soft" @click="{{ $model }}++" aria-label="Increase {{ strtolower($label) }}"><x-ui.icon name="plus" class="size-4 !text-current" /></button>
</div>
