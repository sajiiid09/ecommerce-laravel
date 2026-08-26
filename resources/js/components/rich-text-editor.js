import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';

const MediaImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            mediaId: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-media-id'),
                renderHTML: (attributes) => attributes.mediaId ? { 'data-media-id': attributes.mediaId } : {},
            },
        };
    },
});

window.richTextEditor = (wire, initial = null, jsonField = 'description_json', htmlField = 'description_html', placeholder = 'Start writing...') => ({
    editor: null,
    mediaListener: null,
    syncTimer: null,
    init() {
        this.editor = new Editor({
            element: this.$refs.editor,
            extensions: [StarterKit.configure({ link: { openOnClick: false } }), MediaImage.configure({ inline: false, allowBase64: false }), Placeholder.configure({ placeholder })],
            content: initial || '<p></p>',
            onUpdate: ({ editor }) => {
                this.queueSync(editor);
            },
        });
        this.mediaListener = (event) => {
            if (event.detail?.context !== 'content') return;
            const { id, url } = event.detail;
            if (id && url) this.editor?.chain().focus().setImage({ src: url, alt: '', mediaId: id }).run();
        };
        window.addEventListener('media-selected', this.mediaListener);
    },
    queueSync(editor) {
        window.clearTimeout(this.syncTimer);
        this.syncTimer = window.setTimeout(() => {
            wire.set(jsonField, editor.getJSON(), { shouldValidate: false });
            wire.set(htmlField, editor.getHTML(), { shouldValidate: false });
        }, 350);
    },
    flushSync() {
        if (!this.editor) return;
        window.clearTimeout(this.syncTimer);
        wire.set(jsonField, this.editor.getJSON(), { shouldValidate: false });
        wire.set(htmlField, this.editor.getHTML(), { shouldValidate: false });
    },
    toggle(command) { this.editor?.chain().focus()[command]().run(); },
    toggleHeading(level) { this.editor?.chain().focus().toggleHeading({ level }).run(); },
    setLink() {
        const href = window.prompt('Enter a safe link URL');
        if (href) this.editor?.chain().focus().setLink({ href }).run();
    },
    insertImage() { window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'content' } })); },
    destroy() { this.flushSync(); if (this.mediaListener) window.removeEventListener('media-selected', this.mediaListener); this.editor?.destroy(); },
});
