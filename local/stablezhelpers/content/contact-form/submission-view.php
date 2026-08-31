<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published
// by the Free Software Foundation, either version 3 of the License,
// or (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * View a single Contact Us submission.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

use local_stablezhelpers\content\contactus_manager;

/** @var \context $context */
$context = context_system::instance();

/**
 * ========================================================
 *     Access checks.
 * ========================================================
 */
require_login();
require_capability('local/stablezhelpers:managecontent', $context);

$id = required_param('id', PARAM_INT);
$manager = new contactus_manager();
$submission = $manager->get_submission($id);

if (!$submission) {
    throw new moodle_exception(
        'submissionnotfound',
        'local_stablezhelpers'
    );
}

$PAGE->set_context($context);
$PAGE->set_url(
    new moodle_url(
        '/local/stablezhelpers/content/contact-form/submission-view.php',
        ['id' => $id]
    )
);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(
    get_string('contactus_submission_detail', 'local_stablezhelpers')
);
$PAGE->set_heading(
    get_string('contactus_submission_detail', 'local_stablezhelpers')
);

echo $OUTPUT->header();

echo $OUTPUT->heading(
    get_string('contactus_submission_detail', 'local_stablezhelpers')
);

$table = new html_table();
$table->attributes['class'] = 'generaltable w-100';

$table->data[] = [
    get_string('submission_id', 'local_stablezhelpers'),
    $submission->id,
];

$table->data[] = [
    get_string('contactus_name', 'local_stablezhelpers'),
    format_string($submission->name),
];

$table->data[] = [
    get_string('contactus_email', 'local_stablezhelpers'),
    html_writer::link(
        'mailto:' . s($submission->email),
        s($submission->email)
    ),
];

$table->data[] = [
    get_string('contactus_subject', 'local_stablezhelpers'),
    format_string($submission->subject),
];

$table->data[] = [
    get_string('contactus_message', 'local_stablezhelpers'),
    format_text(
        $submission->message,
        FORMAT_PLAIN
    ),
];

if (!empty($submission->other)) {
    $table->data[] = [
        get_string('contactus_other', 'local_stablezhelpers'),
        format_text(
            $submission->other,
            FORMAT_PLAIN
        ),
    ];
}

$statusstring = 'status_unread';

if ((int) $submission->status === 1) {
    $statusstring = 'status_read';
} else if ((int) $submission->status === 2) {
    $statusstring = 'status_replied';
}

$table->data[] = [
    get_string('status', 'local_stablezhelpers'),
    get_string($statusstring, 'local_stablezhelpers'),
];

$table->data[] = [
    get_string('timecreated', 'local_stablezhelpers'),
    userdate($submission->timecreated),
];

$table->data[] = [
    get_string('timemodified', 'local_stablezhelpers'),
    userdate($submission->timemodified),
];

echo html_writer::table($table);

/*
 * Mark the submission as read when an administrator opens it.
 *
 * Only change unread submissions. This avoids unnecessarily
 * updating timemodified every time the page is opened.
 */
if ((int) $submission->status === 0) {
    $manager->mark_as_read($submission->id);
}

echo html_writer::start_div('mt-4');

$backurl = new moodle_url(
    '/local/stablezhelpers/content/contact-form/submissions.php'
);

echo html_writer::link(
    $backurl,
    get_string('backtosubmissions', 'local_stablezhelpers'),
    [
        'class' => 'btn btn-secondary',
    ]
);

echo html_writer::end_div();

echo $OUTPUT->footer();
