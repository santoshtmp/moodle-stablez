<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Moodle database cleaner admin page.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();

$context = context_system::instance();

if (!is_siteadmin()) {
    throw new required_capability_exception(
        $context,
        'moodle/site:config',
        'nopermissions',
        'Site administrator'
    );
}

$PAGE->set_url(new moodle_url('/local/stablezhelpers/content/db_cleaner.php'));
$PAGE->set_context($context);
$PAGE->set_title('Moodle Database Cleaner');
$PAGE->set_heading('Moodle Database Cleaner');

// Checkbox options exposed on the form; keys map 1:1 to db_cleaner::clean() option keys.
$checkboxoptions = [
    'drafts',
    'tinyautosave',
    'logs',
    'sessions',
    'tasklog',
    'adhoctasks',
    'upgradelog',
    'configlog',
    'backupcontrollers',
    'questionpreviews',
    'notifications',
    'stats',
    'recyclebin',
    'filetrash',
    'cache',
];

$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'clean') {
    require_sesskey();

    $days = optional_param('days', 1, PARAM_INT);
    $days = max(1, min(365, $days));

    $options = ['days' => $days];
    foreach ($checkboxoptions as $key) {
        $options[$key] = optional_param($key, 0, PARAM_BOOL);
    }

    $result = \local_stablezhelpers\local\feature\db_cleaner::clean($options);

    echo $OUTPUT->header();

    echo $OUTPUT->heading('Cleanup completed');

    echo $OUTPUT->notification(
        'The selected cleanup operations have been completed.',
        'success'
    );

    $labels = [
        'draft_files_deleted' => 'Draft files deleted',
        'tiny_autosave_deleted' => 'TinyMCE autosave records deleted',
        'logs_deleted' => 'Log records deleted',
        'sessions_deleted' => 'Expired sessions deleted',
        'task_log_deleted' => 'Task log records deleted',
        'adhoc_tasks_deleted' => 'Stuck ad-hoc tasks deleted',
        'upgrade_log_deleted' => 'Upgrade log records deleted',
        'config_log_deleted' => 'Config log records deleted',
        'backup_controllers_deleted' => 'Backup controller records deleted',
        'question_previews_deleted' => 'Question preview usages deleted',
        'notifications_deleted' => 'Read notifications deleted',
        'stats_deleted' => 'Stats records deleted',
        'recyclebin_course_deleted' => 'Course recycle bin items emptied',
        'recyclebin_category_deleted' => 'Category recycle bin items emptied',
    ];

    $items = [];

    foreach ($labels as $key => $label) {
        if (isset($result[$key])) {
            $items[] = $label . ': ' . number_format((int) $result[$key]);
        }
    }

    if (!empty($result['file_trash_cleaned'])) {
        $items[] = 'File trash cleanup: Completed';
    }

    if (!empty($result['cache_purged'])) {
        $items[] = 'Cache purge: Completed';
    }

    if (empty($items)) {
        $items[] = 'No cleanup operations were selected.';
    }

    echo html_writer::alist($items);

    echo html_writer::div(
        html_writer::link(
            new moodle_url('/local/stablezhelpers/content/db_cleaner.php'),
            'Back to Database Cleaner',
            [
                'class' => 'btn btn-secondary mt-3',
            ]
        )
    );

    echo $OUTPUT->footer();

    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading('Moodle Database Cleaner');

echo $OUTPUT->notification(
    '<strong>Warning:</strong> This tool performs destructive cleanup operations. '
        . 'Make sure you have a database backup before running it.',
    'warning'
);

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => new moodle_url('/local/stablezhelpers/content/db_cleaner.php'),
    'id' => 'db-cleaner-form',
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'action',
    'value' => 'clean',
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey(),
]);

/*
 * Retention.
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'Retention');

echo html_writer::tag(
    'label',
    'Delete data older than:',
    [
        'for' => 'days',
        'class' => 'mr-2',
    ]
);

echo html_writer::empty_tag('input', [
    'type' => 'number',
    'id' => 'days',
    'name' => 'days',
    'value' => 1,
    'min' => 1,
    'max' => 365,
    'class' => 'form-control',
    'style' => 'width:100px; display:inline-block;',
]);

echo html_writer::tag('span', ' days', ['class' => 'ml-2']);

echo html_writer::tag(
    'div',
    'Applies to all options below that are date-based.',
    ['class' => 'text-muted small mt-1']
);

echo html_writer::end_div();

/*
 * Safe / routine cleanup.
 */
echo html_writer::start_div('mb-4');
echo html_writer::tag('h4', 'Routine Cleanup');

echo html_writer::checkbox('drafts', 1, true, 'Delete old user draft files', ['id' => 'drafts']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('tinyautosave', 1, true, 'Delete old TinyMCE autosave records', ['id' => 'tinyautosave']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('sessions', 1, true, 'Delete expired sessions', ['id' => 'sessions']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('tasklog', 1, true, 'Delete old scheduled/ad-hoc task run logs', ['id' => 'tasklog']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('adhoctasks', 1, true, 'Delete stuck (failing) ad-hoc tasks', ['id' => 'adhoctasks']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('upgradelog', 1, true, 'Delete old upgrade log entries', ['id' => 'upgradelog']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('backupcontrollers', 1, true, 'Delete stale backup/restore controller state', ['id' => 'backupcontrollers']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('questionpreviews', 1, true, 'Delete question bank preview attempts', ['id' => 'questionpreviews']);
echo html_writer::empty_tag('br');
echo html_writer::checkbox('notifications', 1, true, 'Delete read notifications', ['id' => 'notifications']);

echo html_writer::end_div();

/*
 * Logs.
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'Logs');

echo html_writer::checkbox('logs', 1, false, 'Delete ALL Moodle logs (standard, legacy, and MNet log tables)', ['id' => 'logs']);

echo html_writer::tag(
    'div',
    'Permanently deletes every log record on the site (ignores the retention setting above). This breaks '
        . 'Live Logs, course participation reports, and any activity-audit history. There is no undo short '
        . 'of a database restore.',
    ['class' => 'text-danger small mt-1']
);

echo html_writer::end_div();

/*
 * Audit / config data (higher caution).
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'Audit Data');

echo html_writer::checkbox('configlog', 1, false, 'Delete old configuration change log entries', ['id' => 'configlog']);

echo html_writer::tag(
    'div',
    'This is the audit trail of every settings change made on the site. Only enable if you do not need long-term change history.',
    ['class' => 'text-danger small mt-1']
);

echo html_writer::end_div();

/*
 * Stats.
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'Site Statistics');

echo html_writer::checkbox('stats', 1, false, 'Delete site statistics tables (only if statistics are currently disabled)', ['id' => 'stats']);

echo html_writer::tag(
    'div',
    'No-op if "Enable statistics" is currently turned on in site admin, to avoid deleting data you rely on.',
    ['class' => 'text-muted small mt-1']
);

echo html_writer::end_div();

/*
 * Recycle bin.
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'Recycle Bin');

echo html_writer::checkbox('recyclebin', 1, false, 'Permanently empty course and category recycle bins', ['id' => 'recyclebin']);

echo html_writer::tag(
    'div',
    'The recycle bin is the built-in undo for accidentally deleted courses and activities. Emptying it is '
        . 'permanent and skips whatever retention period is configured for tool_recyclebin.',
    ['class' => 'text-danger small mt-1']
);

echo html_writer::end_div();

/*
 * File trash.
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'File Storage');

echo html_writer::checkbox('filetrash', 1, true, 'Clean Moodle file trash', ['id' => 'filetrash']);

echo html_writer::end_div();

/*
 * Cache.
 */
echo html_writer::start_div('mb-4');

echo html_writer::tag('h4', 'Cache');

echo html_writer::checkbox('cache', 1, true, 'Purge Moodle caches', ['id' => 'cache']);

echo html_writer::end_div();

/*
 * Submit button.
 */
echo html_writer::start_div('mt-4');

echo html_writer::tag(
    'button',
    'Run Cleanup',
    [
        'type' => 'submit',
        'class' => 'btn btn-danger',
        'id' => 'run-cleanup',
    ]
);

echo html_writer::end_div();

echo html_writer::end_tag('form');

/*
 * Confirmation dialog.
 */
$confirmation = 'Are you sure you want to run the selected cleanup operations? '
    . 'This action cannot be undone.';

echo html_writer::script("
    document.getElementById('db-cleaner-form').addEventListener('submit', function(e) {
        if (!confirm(" . json_encode($confirmation) . ")) {
            e.preventDefault();
        }
    });
");

echo $OUTPUT->footer();
