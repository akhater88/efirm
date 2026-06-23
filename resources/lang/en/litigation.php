<?php

// [HARD-STOP-LAWYER-REQUIRED] All litigation strings require legal review before production.

return [
    // --- General ---
    'litigation' => 'Litigation',
    'litigation_matters' => 'Litigation Matters',
    'commercial_matters' => 'Commercial Matters',
    'is_litigation' => 'Litigation Matter',

    // --- Matter litigation fields ---
    'court' => 'Court',
    'judge' => 'Judge',
    'court_case_number' => 'Court Case Number',
    'case_number_internal' => 'Internal Case Number',
    'litigation_status' => 'Litigation Status',
    'filed_date' => 'Filed Date',
    'next_hearing_date' => 'Next Hearing Date',
    'representation_role' => 'Representation Role',

    // --- Litigation statuses [PROVISIONAL-FOUNDER-DECIDED] ---
    // Per advisor input: docs/02_advisor_meeting_log.md Conversation 1, Decision #1
    'status_pre_filing' => 'Pre-Filing',
    'status_fee_payment_and_registration' => 'Fee Payment & Registration',
    'status_filed' => 'Filed',
    'status_notification_pending' => 'Notification Pending',
    'status_in_evidence' => 'In Evidence',
    'status_referred_to_expert' => 'Referred to Expert',
    'status_in_judgment' => 'In Judgment',
    'status_appealed' => 'Appealed',
    'status_closed_won' => 'Closed (Won)',
    'status_closed_lost' => 'Closed (Lost)',
    'status_settled' => 'Settled',
    'status_withdrawn' => 'Withdrawn',

    // --- Representation roles [PROVISIONAL-FOUNDER-DECIDED] ---
    'role_plaintiff' => 'Plaintiff',
    'role_defendant' => 'Defendant',
    'role_intervenor' => 'Intervenor',
    'role_third_party' => 'Third Party',

    // --- Courts ---
    'courts' => 'Courts',
    'court_name_ar' => 'Court Name (Arabic)',
    'court_name_en' => 'Court Name (English)',
    'court_type' => 'Court Type',
    'jurisdiction_country' => 'Jurisdiction Country',
    'jurisdiction_governorate' => 'Jurisdiction Governorate',
    'city' => 'City',
    'address' => 'Address',
    'phone' => 'Phone',
    'notes' => 'Notes',

    // --- Court types [PROVISIONAL-FOUNDER-DECIDED] ---
    'court_type_magistrate' => 'Magistrate Court',
    'court_type_first_instance' => 'Court of First Instance',
    'court_type_appeal' => 'Court of Appeal',
    'court_type_cassation' => 'Court of Cassation',
    'court_type_specialized_commercial' => 'Specialized Commercial Court',
    'court_type_specialized_labor' => 'Specialized Labor Court',
    'court_type_specialized_family' => 'Specialized Family Court',
    'court_type_administrative' => 'Administrative Court',
    'court_type_sharia' => 'Sharia Court',
    'court_type_arbitration' => 'Arbitration',

    // --- Judges ---
    'judges' => 'Judges',
    'judge_name_ar' => 'Judge Name (Arabic)',
    'judge_name_en' => 'Judge Name (English)',
    'judge_title' => 'Title',

    // --- Hearings ---
    'hearing' => 'Hearing',
    'hearings' => 'Hearings',
    'hearing_date' => 'Hearing Date',
    'hearing_type' => 'Hearing Type',
    'hearing_status' => 'Hearing Status',
    'held_at' => 'Held At',
    'outcome' => 'Outcome',
    'next_action_required' => 'Next Action Required',
    'postponed_to' => 'Postponed To',
    'our_attendee' => 'Our Attendee',

    // --- Hearing types [PROVISIONAL-FOUNDER-DECIDED] ---
    // Per advisor input: docs/02_advisor_meeting_log.md Conversation 1, Decision #2
    'hearing_type_first_session' => 'First Session',
    'hearing_type_evidence' => 'Evidence', // [DEPRECATED] — split into plaintiff/defendant per Decision #2
    'hearing_type_plaintiff_evidence' => 'Plaintiff Evidence',
    'hearing_type_defendant_evidence' => 'Defendant Evidence',
    'hearing_type_notification_session' => 'Notification Session',
    'hearing_type_expert_witness' => 'Expert Witness',
    'hearing_type_witness_testimony' => 'Witness Testimony',
    'hearing_type_final_arguments' => 'Final Arguments',
    'hearing_type_judgment' => 'Judgment',
    'hearing_type_enforcement' => 'Enforcement',
    'hearing_type_other' => 'Other',

    // --- Hearing statuses ---
    'hearing_status_scheduled' => 'Scheduled',
    'hearing_status_held' => 'Held',
    'hearing_status_postponed' => 'Postponed',
    'hearing_status_cancelled' => 'Cancelled',

    // --- Court Reviews ---
    'court_review' => 'Court Review',
    'court_reviews' => 'Court Reviews',
    'decision_date' => 'Decision Date',
    'decision_type' => 'Decision Type',
    'decision_outcome' => 'Outcome',
    'summary_ar' => 'Summary (Arabic)',
    'summary_en' => 'Summary (English)',
    'decision_document' => 'Decision Document',
    'appealable' => 'Appealable',
    'appeal_deadline_date' => 'Appeal Deadline',
    'appeal_filed' => 'Appeal Filed',
    'next_steps' => 'Next Steps',

    // --- Decision types [PROVISIONAL-FOUNDER-DECIDED] ---
    'decision_type_interim_order' => 'Interim Order',
    'decision_type_procedural_ruling' => 'Procedural Ruling',
    'decision_type_expert_appointment' => 'Expert Appointment',
    'decision_type_evidence_ruling' => 'Evidence Ruling',
    'decision_type_partial_judgment' => 'Partial Judgment',
    'decision_type_final_judgment' => 'Final Judgment',
    'decision_type_appeal_decision' => 'Appeal Decision',
    'decision_type_enforcement_order' => 'Enforcement Order',
    'decision_type_other' => 'Other',

    // --- Decision outcomes [PROVISIONAL-FOUNDER-DECIDED] ---
    'outcome_favourable' => 'Favourable',
    'outcome_adverse' => 'Adverse',
    'outcome_mixed' => 'Mixed',
    'outcome_procedural_only' => 'Procedural Only',

    // --- Service Log ---
    'service_log' => 'Service Log',
    'service_log_entries' => 'Service Log Entries',
    'served_party' => 'Served Party',
    'service_method' => 'Service Method',
    'service_date' => 'Service Date',
    'service_address' => 'Service Address',
    'served_by_name' => 'Served By',
    'served_to_recipient_name' => 'Served To',
    'proof_document' => 'Proof Document',
    'service_status' => 'Service Status',

    // --- Service methods [PROVISIONAL-FOUNDER-DECIDED] ---
    'service_method_personal_service' => 'Personal Service',
    'service_method_registered_mail' => 'Registered Mail',
    'service_method_court_bailiff' => 'Court Bailiff',
    'service_method_substituted_service' => 'Substituted Service',
    'service_method_publication' => 'Publication',
    'service_method_electronic' => 'Electronic',
    'service_method_foreign_service' => 'Foreign Service',

    // --- Service statuses ---
    'service_status_successful' => 'Successful',
    'service_status_failed_no_response' => 'Failed - No Response',
    'service_status_failed_refused' => 'Failed - Refused',
    'service_status_failed_invalid_address' => 'Failed - Invalid Address',
    'service_status_pending_proof' => 'Pending Proof',

    // --- Opposing Counsel ---
    'opposing_counsel' => 'Opposing Counsel',
    'is_opposing_counsel' => 'Is Opposing Counsel',

    // --- Court levels (per matter) [PROVISIONAL-FOUNDER-DECIDED] ---
    'court_level_magistrate' => 'Magistrate Court',
    'court_level_first_instance' => 'First Instance Court',
    'court_level_appeal' => 'Appeal Court',
    'court_level_cassation' => 'Cassation Court',
    'court_level_specialized_commercial' => 'Specialized Commercial Court',
    'court_level_specialized_labor' => 'Specialized Labor Court',
    'court_level_administrative' => 'Administrative Court',
    'court_level_sharia' => 'Sharia Court',
    'court_level_arbitration' => 'Arbitration',

    // --- Judgment presence types [PROVISIONAL-FOUNDER-DECIDED] ---
    'judgment_wijahi' => 'In-Presence',
    'judgment_mithla_wijahi' => 'Deemed In-Presence',
    'judgment_ghyabi' => 'Pure Default',

    // --- Appeal deadline ---
    'appeal_deadline' => 'Appeal Deadline',
    'requires_input' => 'Requires Manual Input',

    // --- Hearing Session Content (F-FIX-02.1, Decision #28) ---
    'judge_statement' => 'Judge Statement',
    'judge_statement_ar' => 'Judge Statement (Arabic)',
    'judge_statement_en' => 'Judge Statement (English)',
    'outcome_summary' => 'Outcome Summary',
    'outcome_summary_ar' => 'Outcome Summary (Arabic)',
    'outcome_summary_en' => 'Outcome Summary (English)',
    'our_submissions_made' => 'Our Submissions Made',
    'opposing_submissions_made' => 'Opposing Submissions Made',
    'next_session_required_actions' => 'Next Session Required Actions',
    'next_session_required_actions_ar' => 'Next Session Required Actions (Arabic)',
    'next_session_required_actions_en' => 'Next Session Required Actions (English)',
    'session_attended_by' => 'Session Attended By',
    'session_content_requires_held_status' => 'Session content can only be recorded for hearings with "held" status.',
    'action_items' => 'Action Items',
    'action_item' => 'Action Item',
    'action_item_description' => 'Description',
    'action_item_due_date' => 'Due Date',
    'action_item_status' => 'Status',
    'action_item_status_pending' => 'Pending',
    'action_item_status_completed' => 'Completed',
    'action_item_status_waived' => 'Waived',
    'sessions_timeline' => 'Sessions Timeline',

    // --- Court Review Dispatch (F-FIX-02.2, Decision #29) ---
    'dispatched_to' => 'Dispatched To',
    'dispatched_at' => 'Dispatched At',
    'completed_by' => 'Completed By',
    'location_in_courthouse' => 'Location in Courthouse',
    'location_in_courthouse_ar' => 'Location in Courthouse (Arabic)',
    'location_in_courthouse_en' => 'Location in Courthouse (English)',
    'expected_outcome' => 'Expected Outcome',
    'expected_outcome_ar' => 'Expected Outcome (Arabic)',
    'expected_outcome_en' => 'Expected Outcome (English)',
    'completion_notes' => 'Completion Notes',
    'evidence_document' => 'Evidence Document',
    'dispatched_to_me' => 'Dispatched to Me',
    'dispatch' => 'Dispatch',
    'dispatch_complete' => 'Complete Dispatch',

    // --- Hearing Postponement Chain (F-FIX-02.5, Decision #30) ---
    'postponement_reason' => 'Postponement Reason',
    'postponement_reason_ar' => 'Postponement Reason (Arabic)',
    'postponement_reason_en' => 'Postponement Reason (English)',
    'postponement_initiated_by' => 'Postponement Initiated By',
    'postponement_initiated_our_side' => 'Our Side',
    'postponement_initiated_opposing_side' => 'Opposing Side',
    'postponement_initiated_court' => 'Court',
    'postponement_initiated_unknown' => 'Unknown',
    'postponement_chain' => 'Postponement Chain',
    'circular_postponement_reference' => 'Cannot set postponement target: circular reference detected.',

    // --- Quick Timer (F-FIX-02.4, Decision #31) ---
    'timer_already_active' => 'You already have an active timer running. Stop it before starting a new one.',

    // --- Success messages ---
    'court_created_success' => 'Court created successfully',
    'court_updated_success' => 'Court updated successfully',
    'court_deleted_success' => 'Court deleted successfully',
    'judge_created_success' => 'Judge created successfully',
    'judge_updated_success' => 'Judge updated successfully',
    'judge_deleted_success' => 'Judge deleted successfully',
    'hearing_created_success' => 'Hearing created successfully',
    'hearing_updated_success' => 'Hearing updated successfully',
    'hearing_deleted_success' => 'Hearing deleted successfully',
    'court_review_created_success' => 'Court review created successfully',
    'court_review_updated_success' => 'Court review updated successfully',
    'court_review_deleted_success' => 'Court review deleted successfully',
    'service_log_created_success' => 'Service log entry created successfully',
    'service_log_updated_success' => 'Service log entry updated successfully',
    'service_log_deleted_success' => 'Service log entry deleted successfully',
];
