@props(['title', 'options' => [], 'name' => 'filter', 'selected' => []])
@php($selectedValues = array_map('strval', (array) $selected))
<fieldset class="border-t border-store-border pt-4">
    <legend class="text-sm font-bold text-store-ink">{{ $title }}</legend>

    <div class="mt-3 space-y-3">
        @foreach ($options as $option)
            @php($value = is_array($option) ? ($option['value'] ?? $option['label']) : $option)
            @php($label = is_array($option) ? ($option['label'] ?? $value) : $option)

            <x-ui.checkbox
                :name="$name.'[]'"
                :value="$value"
                :checked="in_array((string) $value, $selectedValues, true)"
                :label="$label"
                size="sm"
                class="[&_[data-slot=checkbox-label]]:text-store-text"
            />
        @endforeach
    </div>
</fieldset>
