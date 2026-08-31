@props([
    'value' => null,
    'jsonField' => 'description_json',
    'htmlField' => 'description_html',
    'placeholder' => 'Start writing…',
])

<div
    x-data="richTextEditor($wire, @js($value), @js($jsonField), @js($htmlField), @js($placeholder))"
    x-init="init()"
    x-on:submit="flushSync()"
    x-on:livewire:navigating.window="destroy()"
    class="storez-rich-editor overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm"
    wire:ignore
>
    <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 p-2" role="toolbar" aria-label="Description formatting">
        <div data-toolbar-group class="flex items-center gap-1 border-r border-slate-200 pr-2">
            <button type="button" @mousedown.prevent @click="toggle('undo')" title="Undo" aria-label="Undo" class="editor-toolbar-button">Undo</button>
            <button type="button" @mousedown.prevent @click="toggle('redo')" title="Redo" aria-label="Redo" class="editor-toolbar-button">Redo</button>
        </div>

        <div data-toolbar-group class="flex items-center gap-1 border-r border-slate-200 px-2">
            <button type="button" @mousedown.prevent @click="setParagraph()" title="Paragraph" aria-label="Paragraph" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('paragraph') }">P</button>
            <button type="button" @mousedown.prevent @click="toggleHeading(2)" title="Heading 2" aria-label="Heading 2" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('heading', { level: 2 }) }">H2</button>
            <button type="button" @mousedown.prevent @click="toggleHeading(3)" title="Heading 3" aria-label="Heading 3" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('heading', { level: 3 }) }">H3</button>
        </div>

        <div data-toolbar-group class="flex items-center gap-1 border-r border-slate-200 px-2">
            <button type="button" @mousedown.prevent @click="toggle('toggleBold')" title="Bold" aria-label="Bold" class="editor-toolbar-button font-black" :class="{ 'bg-blue-100 text-blue-700': isActive('bold') }">B</button>
            <button type="button" @mousedown.prevent @click="toggle('toggleItalic')" title="Italic" aria-label="Italic" class="editor-toolbar-button italic" :class="{ 'bg-blue-100 text-blue-700': isActive('italic') }">I</button>
            <button type="button" @mousedown.prevent @click="toggle('toggleStrike')" title="Strikethrough" aria-label="Strikethrough" class="editor-toolbar-button line-through" :class="{ 'bg-blue-100 text-blue-700': isActive('strike') }">S</button>
        </div>

        <div data-toolbar-group class="flex items-center gap-1 border-r border-slate-200 px-2">
            <button type="button" @mousedown.prevent @click="toggle('toggleBulletList')" title="Bullet list" aria-label="Bullet list" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('bulletList') }">• List</button>
            <button type="button" @mousedown.prevent @click="toggle('toggleOrderedList')" title="Numbered list" aria-label="Numbered list" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('orderedList') }">1. List</button>
            <button type="button" @mousedown.prevent @click="toggle('toggleBlockquote')" title="Blockquote" aria-label="Blockquote" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('blockquote') }">Quote</button>
        </div>

        <div data-toolbar-group class="flex items-center gap-1 px-2">
            <button type="button" @mousedown.prevent @click="setLink()" title="Add or edit link" aria-label="Add or edit link" class="editor-toolbar-button" :class="{ 'bg-blue-100 text-blue-700': isActive('link') }">Link</button>
            <button type="button" @mousedown.prevent @click="insertImage()" title="Insert image" aria-label="Insert image" class="editor-toolbar-button">Image</button>
        </div>
    </div>

    <div x-ref="editor" class="prose max-w-none min-h-52 p-4 text-sm leading-6 text-slate-700 focus:outline-none" role="textbox" aria-multiline="true" :aria-label="'Product description editor'"></div>
</div>
