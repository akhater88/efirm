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

    // Success messages
    'created_success' => 'Document created successfully',
    'updated_success' => 'Document updated successfully',
    'deleted_success' => 'Document deleted successfully',
    'version_created' => 'New version saved',
    'version_skipped' => 'No changes detected',
];
