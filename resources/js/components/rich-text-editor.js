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

window.richTextEditor = (wire, initial = null, jsonField = 'description_json', htmlField = 'description_html', placeholder = 'Start writing...') => {
    let editor = null;
    let mediaListener = null;
    let syncTimer = null;

    return {
        init() {
            editor = new Editor({
                element: this.$refs.editor,
                extensions: [StarterKit.configure({ link: { openOnClick: false } }), MediaImage.configure({ inline: false, allowBase64: false }), Placeholder.configure({ placeholder })],
                content: initial || '<p></p>',
                onUpdate: ({ editor }) => {
                    queueSync(editor);
                },
            });
            mediaListener = (event) => {
                if (event.detail?.context !== 'content') return;
                const { id, url } = event.detail;
                if (id && url && editor && !editor.isDestroyed) editor.chain().focus().setImage({ src: url, alt: '', mediaId: id }).run();
            };
            window.addEventListener('media-selected', mediaListener);
        },
        flushSync() {
            if (!editor || editor.isDestroyed) return;
            window.clearTimeout(syncTimer);
            wire.set(jsonField, editor.getJSON(), false);
            wire.set(htmlField, editor.getHTML(), false);
        },
        toggle(command) {
            if (editor && !editor.isDestroyed) editor.chain().focus()[command]().run();
        },
        toggleHeading(level) {
            if (editor && !editor.isDestroyed) editor.chain().focus().toggleHeading({ level }).run();
        },
        setLink() {
            const href = window.prompt('Enter a safe link URL');
            if (href && editor && !editor.isDestroyed) editor.chain().focus().setLink({ href }).run();
        },
        insertImage() {
            window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'content' } }));
        },
        destroy() {
            window.clearTimeout(syncTimer);
            if (editor && !editor.isDestroyed) {
                wire.set(jsonField, editor.getJSON(), false);
                wire.set(htmlField, editor.getHTML(), false);
                editor.destroy();
            }
            if (mediaListener) window.removeEventListener('media-selected', mediaListener);
            editor = null;
            mediaListener = null;
        },
    };

    function queueSync(updatedEditor) {
        window.clearTimeout(syncTimer);
        syncTimer = window.setTimeout(() => {
            if (updatedEditor.isDestroyed) return;
            wire.set(jsonField, updatedEditor.getJSON(), false);
            wire.set(htmlField, updatedEditor.getHTML(), false);
        }, 350);
    }
};
