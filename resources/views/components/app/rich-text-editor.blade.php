@props([
    'value' => null,
    'jsonField' => 'description_json',
    'htmlField' => 'description_html',
    'placeholder' => 'Start writing…',
])
<div x-data="richTextEditor($wire, @js($value), @js($jsonField), @js($htmlField), @js($placeholder))" x-init="init()" x-on:submit="flushSync()" x-on:livewire:navigating.window="destroy()" class="storez-rich-editor overflow-hidden rounded-lg border border-slate-300 bg-white" wire:ignore>
    <div class="flex flex-wrap gap-1 border-b border-slate-200 p-2 text-xs">
        <button type="button" @click="toggle('toggleBold')" class="rounded px-2 py-1 hover:bg-slate-100"><strong>B</strong></button>
        <button type="button" @click="toggle('toggleItalic')" class="rounded px-2 py-1 hover:bg-slate-100"><em>I</em></button>
        <button type="button" @click="toggle('toggleStrike')" class="rounded px-2 py-1 hover:bg-slate-100"><s>S</s></button>
        <button type="button" @click="toggle('toggleBulletList')" class="rounded px-2 py-1 hover:bg-slate-100">List</button>
        <button type="button" @click="toggle('toggleOrderedList')" class="rounded px-2 py-1 hover:bg-slate-100">1.</button>
        <button type="button" @click="toggleHeading(2)" class="rounded px-2 py-1 hover:bg-slate-100">Heading</button>
        <button type="button" @click="toggle('toggleBlockquote')" class="rounded px-2 py-1 hover:bg-slate-100">Quote</button>
        <button type="button" @click="setLink()" class="rounded px-2 py-1 hover:bg-slate-100">Link</button>
        <button type="button" @click="insertImage()" class="rounded px-2 py-1 hover:bg-slate-100">Image</button>
        <button type="button" @click="toggle('undo')" class="rounded px-2 py-1 hover:bg-slate-100">Undo</button>
        <button type="button" @click="toggle('redo')" class="rounded px-2 py-1 hover:bg-slate-100">Redo</button>
    </div>
    <div x-ref="editor" class="prose max-w-none min-h-40 p-3 text-sm leading-6 text-slate-700 focus:outline-none"></div>
</div>
