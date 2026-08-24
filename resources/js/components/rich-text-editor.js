import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

window.richTextEditor = (wire, initial = null) => ({
    editor: null,
    init() {
        this.editor = new Editor({
            element: this.$refs.editor,
            extensions: [StarterKit, Link.configure({ openOnClick: false })],
            content: initial || '<p></p>',
            onUpdate: ({ editor }) => {
                wire.set('description_json', editor.getJSON());
                wire.set('description_html', editor.getHTML());
            },
        });
    },
    toggle(command) { this.editor?.chain().focus()[command]().run(); },
    destroy() { this.editor?.destroy(); },
});
