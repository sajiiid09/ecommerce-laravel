<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
@props(['dataSlot','class'])
<x-heroicons::outline.plus :data-slot="$dataSlot" :class="$class" >

{{ $slot ?? "" }}
</x-heroicons::outline.plus>