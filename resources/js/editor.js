import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import { Table, TableRow, TableCell, TableHeader } from '@tiptap/extension-table';
import Placeholder from '@tiptap/extension-placeholder';

/**
 * Document Editor — TipTap integration for the Livewire component.
 *
 * Communicates with the Livewire DocumentEditor component via:
 * - Livewire.dispatch('editor-content-updated', { body: json })
 * - Listens for 'load-content' event to set editor content
 */

let editor = null;
let autosaveTimer = null;
let lastSavedHash = null;
const AUTOSAVE_INTERVAL = 30000; // 30 seconds

function initEditor() {
    const element = document.getElementById('editor-canvas');
    if (!element) return;

    const initialContent = window.__EDITOR_INITIAL_CONTENT__ || { type: 'doc', content: [{ type: 'paragraph' }] };

    editor = new Editor({
        element,
        extensions: [
            StarterKit.configure({
                heading: { levels: [1, 2, 3] },
            }),
            Underline,
            TextAlign.configure({
                types: ['heading', 'paragraph'],
                alignments: ['left', 'center', 'right'],
            }),
            Table.configure({ resizable: false }),
            TableRow,
            TableCell,
            TableHeader,
            Placeholder.configure({
                placeholder: element.dataset.placeholder || 'Start writing...',
            }),
        ],
        content: initialContent,
        editorProps: {
            attributes: {
                class: 'prose prose-lg max-w-none focus:outline-none min-h-[60vh] px-8 py-6',
                dir: 'auto',
            },
        },
        onUpdate: ({ editor }) => {
            updateToolbarState(editor);
            updateSaveStatus('unsaved');
        },
        onSelectionUpdate: ({ editor }) => {
            updateToolbarState(editor);
        },
        onCreate: ({ editor }) => {
            updateToolbarState(editor);
            lastSavedHash = hashContent(editor.getJSON());
            startAutosave();
        },
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + S = manual save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            triggerSave(false);
        }
        // Ctrl+Shift+X = toggle paragraph direction
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'X') {
            e.preventDefault();
            toggleDirection();
        }
    });

    // Save on blur (window loses focus)
    window.addEventListener('blur', () => {
        if (editor && hasUnsavedChanges()) {
            triggerSave(true);
        }
    });

    // Toolbar button handlers
    document.querySelectorAll('[data-editor-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.editorAction;
            handleToolbarAction(action);
        });
    });

    // Expose editor globally for Livewire interop
    window.__editor = editor;
}

function handleToolbarAction(action) {
    if (!editor) return;

    const actions = {
        'bold': () => editor.chain().focus().toggleBold().run(),
        'italic': () => editor.chain().focus().toggleItalic().run(),
        'underline': () => editor.chain().focus().toggleUnderline().run(),
        'strike': () => editor.chain().focus().toggleStrike().run(),
        'h1': () => editor.chain().focus().toggleHeading({ level: 1 }).run(),
        'h2': () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
        'h3': () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
        'paragraph': () => editor.chain().focus().setParagraph().run(),
        'bulletList': () => editor.chain().focus().toggleBulletList().run(),
        'orderedList': () => editor.chain().focus().toggleOrderedList().run(),
        'alignLeft': () => editor.chain().focus().setTextAlign('left').run(),
        'alignCenter': () => editor.chain().focus().setTextAlign('center').run(),
        'alignRight': () => editor.chain().focus().setTextAlign('right').run(),
        'undo': () => editor.chain().focus().undo().run(),
        'redo': () => editor.chain().focus().redo().run(),
        'dirLtr': () => setDirection('ltr'),
        'dirRtl': () => setDirection('rtl'),
    };

    if (actions[action]) {
        actions[action]();
        updateToolbarState(editor);
    }
}

function setDirection(dir) {
    if (!editor) return;
    // Set text-align based on direction
    const align = dir === 'rtl' ? 'right' : 'left';
    editor.chain().focus().setTextAlign(align).run();
}

function toggleDirection() {
    if (!editor) return;
    const { textAlign } = editor.getAttributes('paragraph');
    const newDir = textAlign === 'right' ? 'left' : 'right';
    editor.chain().focus().setTextAlign(newDir).run();
}

function updateToolbarState(editor) {
    document.querySelectorAll('[data-editor-action]').forEach(btn => {
        const action = btn.dataset.editorAction;
        let isActive = false;

        switch (action) {
            case 'bold': isActive = editor.isActive('bold'); break;
            case 'italic': isActive = editor.isActive('italic'); break;
            case 'underline': isActive = editor.isActive('underline'); break;
            case 'strike': isActive = editor.isActive('strike'); break;
            case 'h1': isActive = editor.isActive('heading', { level: 1 }); break;
            case 'h2': isActive = editor.isActive('heading', { level: 2 }); break;
            case 'h3': isActive = editor.isActive('heading', { level: 3 }); break;
            case 'paragraph': isActive = editor.isActive('paragraph') && !editor.isActive('heading'); break;
            case 'bulletList': isActive = editor.isActive('bulletList'); break;
            case 'orderedList': isActive = editor.isActive('orderedList'); break;
            case 'alignLeft': isActive = editor.isActive({ textAlign: 'left' }); break;
            case 'alignCenter': isActive = editor.isActive({ textAlign: 'center' }); break;
            case 'alignRight': isActive = editor.isActive({ textAlign: 'right' }); break;
        }

        btn.classList.toggle('bg-indigo-100', isActive);
        btn.classList.toggle('text-indigo-700', isActive);
    });
}

function hashContent(json) {
    return JSON.stringify(json);
}

function hasUnsavedChanges() {
    if (!editor) return false;
    return hashContent(editor.getJSON()) !== lastSavedHash;
}

function triggerSave(isAutosave) {
    if (!editor || !hasUnsavedChanges()) return;

    const body = editor.getJSON();
    updateSaveStatus('saving');

    // Dispatch to Livewire
    Livewire.dispatch('editor-save', {
        body: body,
        isAutosave: isAutosave,
    });
}

function updateSaveStatus(status) {
    const el = document.getElementById('save-status');
    if (!el) return;

    const statusTexts = {
        'saved': el.dataset.textSaved || 'Saved',
        'saving': el.dataset.textSaving || 'Saving...',
        'unsaved': el.dataset.textUnsaved || 'Unsaved changes',
        'error': el.dataset.textError || 'Save failed',
        'conflict': el.dataset.textConflict || 'Conflict detected',
    };

    const statusColors = {
        'saved': 'text-green-600',
        'saving': 'text-yellow-600',
        'unsaved': 'text-orange-500',
        'error': 'text-red-600',
        'conflict': 'text-red-600',
    };

    el.textContent = statusTexts[status] || status;
    el.className = `text-sm ${statusColors[status] || 'text-gray-500'}`;
}

function startAutosave() {
    if (autosaveTimer) clearInterval(autosaveTimer);

    autosaveTimer = setInterval(() => {
        if (hasUnsavedChanges()) {
            triggerSave(true);
        }
    }, AUTOSAVE_INTERVAL);
}

// Listen for Livewire events
document.addEventListener('livewire:init', () => {
    // Save succeeded
    Livewire.on('editor-saved', (data) => {
        if (editor) {
            lastSavedHash = hashContent(editor.getJSON());
        }
        updateSaveStatus('saved');

        // Update version display
        const versionEl = document.getElementById('current-version');
        if (versionEl && data[0]?.version_number) {
            versionEl.textContent = 'V' + data[0].version_number;
        }
    });

    // Save skipped (no changes)
    Livewire.on('editor-save-skipped', () => {
        updateSaveStatus('saved');
    });

    // Save failed
    Livewire.on('editor-save-error', (data) => {
        updateSaveStatus('error');
    });

    // Conflict (409)
    Livewire.on('editor-conflict', (data) => {
        updateSaveStatus('conflict');
        // Show conflict modal
        const modal = document.getElementById('conflict-modal');
        if (modal) modal.classList.remove('hidden');
    });

    // Content loaded (e.g., after conflict resolution)
    Livewire.on('editor-load-content', (data) => {
        if (editor && data[0]?.body) {
            editor.commands.setContent(data[0].body);
            lastSavedHash = hashContent(data[0].body);
            updateSaveStatus('saved');
        }
    });
});

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditor);
} else {
    initEditor();
}
