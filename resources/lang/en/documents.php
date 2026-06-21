<?php

return [
    'document' => 'Document',
    'documents' => 'Documents',
    'title' => 'Title',
    'document_type' => 'Document Type',
    'language' => 'Language',
    'status' => 'Status',
    'version' => 'Version',
    'versions' => 'Versions',
    'current_version' => 'Current Version',
    'change_summary' => 'Change Summary',
    'clause' => 'Clause',
    'clauses' => 'Clauses',
    'clause_type' => 'Clause Type',
    'clause_path' => 'Clause Path',
    'body' => 'Body',

    // Document types [PROVISIONAL-FOUNDER-DECIDED]
    'type_contract' => 'Contract',
    'type_memo' => 'Memo',
    'type_letter' => 'Letter',
    'type_amendment' => 'Amendment',
    'type_other' => 'Other',

    // Document statuses
    'status_draft' => 'Draft',
    'status_under_review' => 'Under Review',
    'status_with_counterparty' => 'With Counterparty',
    'status_signed' => 'Signed',
    'status_archived' => 'Archived',

    // Language options
    'language_ar' => 'Arabic',
    'language_en' => 'English',
    'language_bilingual' => 'Bilingual',
    'language_mixed' => 'Mixed',

    // Import
    'import_docx' => 'Import .docx',
    'import_file' => 'Word Document',
    'import_file_required' => 'Please select a .docx file to import.',
    'import_file_mimes' => 'Only .docx files are accepted.',
    'import_file_max' => 'File size must not exceed 25MB.',
    'import_title_placeholder' => 'Leave blank to auto-detect from document',
    'import_success' => 'Document imported successfully',
    'import_error' => 'Import failed',
    'import_file_not_found' => 'The uploaded file could not be found.',
    'imported_from_docx' => 'Imported from .docx',
    'create_blank' => 'Create Blank Document',
    'blank_document_created' => 'Blank document created',

    // Empty state
    'empty_state_heading' => 'No documents yet',
    'empty_state_description' => 'Import a Word document or create a new one from scratch.',

    // Editor
    'open_editor' => 'Open Editor',
    'save' => 'Save',
    'save_with_summary' => 'Save with Summary',
    'save_summary_placeholder' => 'Describe what changed (optional)...',
    'save_status_saved' => 'Saved',
    'save_status_saving' => 'Saving...',
    'save_status_unsaved' => 'Unsaved changes',
    'save_status_error' => 'Save failed',
    'save_status_conflict' => 'Conflict detected',
    'save_conflict' => 'A newer version exists. Please resolve the conflict.',
    'force_saved_after_conflict' => 'Force-saved after conflict resolution',
    'editor_placeholder' => 'Start writing your document...',

    // Conflict
    'conflict_title' => 'Version Conflict',
    'conflict_description' => 'Another user has saved a newer version while you were editing. You can discard your changes and load the latest version, or keep your changes and save as a new version.',
    'conflict_keep_mine' => 'Keep My Changes',
    'conflict_discard_mine' => 'Load Latest',

    // Version history
    'history' => 'History',
    'version_history' => 'Version History',
    'current' => 'Current',
    'no_versions' => 'No versions yet',
    'viewing_old_version' => 'Viewing version V:version (read-only)',
    'back_to_current' => 'Back to current',
    'restore_this_version' => 'Restore this version',
    'restored_from_version' => 'Restored from V:version',
    'compare_latest_versions' => 'Compare latest versions',
    'comparing_versions' => 'Comparing V:old and V:new',
    'words_added' => 'words added',
    'words_removed' => 'words removed',

    // Toolbar
    'toolbar_bold' => 'Bold',
    'toolbar_italic' => 'Italic',
    'toolbar_underline' => 'Underline',
    'toolbar_strikethrough' => 'Strikethrough',
    'toolbar_h1' => 'Heading 1',
    'toolbar_h2' => 'Heading 2',
    'toolbar_h3' => 'Heading 3',
    'toolbar_paragraph' => 'Paragraph',
    'toolbar_bullet_list' => 'Bullet List',
    'toolbar_ordered_list' => 'Numbered List',
    'toolbar_ltr' => 'Left to Right',
    'toolbar_rtl' => 'Right to Left',
    'toolbar_align_left' => 'Align Left',
    'toolbar_align_center' => 'Align Center',
    'toolbar_align_right' => 'Align Right',
    'toolbar_undo' => 'Undo',
    'toolbar_redo' => 'Redo',

    // Success messages
    'created_success' => 'Document created successfully',
    'updated_success' => 'Document updated successfully',
    'deleted_success' => 'Document deleted successfully',
    'version_created' => 'New version saved',
    'version_skipped' => 'No changes detected',
];
