<?php
// This file is part of Moodle - http://moodle.org.
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
 * Contact Us submissions management page.
 *
 * Displays Contact Us submissions with filtering, pagination,
 * sorting, and administrative actions.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

use local_stablezhelpers\content\contactus_manager;

require_login();

$context = context_system::instance();

require_capability('moodle/site:config', $context);

$PAGE->set_context($context);

$page_path = '/local/stablezhelpers/content/contact/submissions.php';
$PAGE->set_url(
    new moodle_url($page_path)
);

$PAGE->set_pagelayout('admin');
$PAGE->set_title('Contact Us Submissions');
$PAGE->set_heading('Contact Us Submissions');

$manager = new contactus_manager();

/*
 * -------------------------------------------------------------------------
 * Parameters.
 * -------------------------------------------------------------------------
 */

$page = max(
    0,
    optional_param('page', 0, PARAM_INT)
);

$name = trim(
    optional_param('name', '', PARAM_TEXT)
);

$email = trim(
    optional_param('email', '', PARAM_EMAIL)
);

$action = optional_param('action', '', PARAM_ALPHA);

$id = optional_param('id', 0, PARAM_INT);

$confirm = optional_param('confirm', 0, PARAM_BOOL);

$perpage = 20;

/*
 * -------------------------------------------------------------------------
 * Process POST actions.
 * -------------------------------------------------------------------------
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();

    $action = required_param('action', PARAM_ALPHA);
    $id = required_param('id', PARAM_INT);

    $submission = $manager->get_submission($id);

    if (!$submission) {
        redirect(
            $PAGE->url,
            'The Contact Us submission could not be found.',
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    switch ($action) {
        case 'read':
            $manager->mark_as_read($id);

            redirect(
                new moodle_url(
                    $PAGE->url,
                    [
                        'page' => $page,
                        'name' => $name,
                        'email' => $email,
                    ]
                ),
                'Submission marked as read.',
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
            break;

        case 'unread':
            $manager->mark_as_unread($id);

            redirect(
                new moodle_url(
                    $PAGE->url,
                    [
                        'page' => $page,
                        'name' => $name,
                        'email' => $email,
                    ]
                ),
                'Submission marked as unread.',
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
            break;

        case 'delete':
            if (!$confirm) {
                throw new \moodle_exception(
                    'invalidparameter',
                    'error',
                    '',
                    'Deletion confirmation is required.'
                );
            }

            $manager->delete_submission($id);

            redirect(
                new moodle_url(
                    $PAGE->url,
                    [
                        'page' => $page,
                        'name' => $name,
                        'email' => $email,
                    ]
                ),
                'Submission deleted successfully.',
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
            break;

        default:
            throw new \moodle_exception(
                'invalidparameter',
                'error',
                '',
                'Invalid action.'
            );
    }
}

/*
 * -------------------------------------------------------------------------
 * Filters.
 * -------------------------------------------------------------------------
 */

$filters = [
    'name' => $name,
    'email' => $email,
];

$totalcount = $manager->get_filtered_submission_count(
    $filters
);

$submissions = $manager->get_filtered_submissions(
    $filters,
    $page * $perpage,
    $perpage
);

/*
 * -------------------------------------------------------------------------
 * Output header.
 * -------------------------------------------------------------------------
 */

echo $OUTPUT->header();

echo html_writer::start_div(
    'contact-us-submissions mt-3'
);
/*
 * -------------------------------------------------------------------------
 * Filter form.
 * -------------------------------------------------------------------------
 */

$filterurl = new moodle_url($page_path);

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => $filterurl->out(false),
    'class' => 'mb-4',
]);

echo html_writer::start_div(
    'row g-3 align-items-end'
);

/*
 * Name filter.
 */

echo html_writer::start_div('col-md-4');

echo html_writer::label(
    'Name',
    'filter-name',
    false,
    ['class' => 'form-label']
);

echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'name',
    'id' => 'filter-name',
    'value' => $name,
    'class' => 'form-control',
    'placeholder' => 'Search by name',
]);

echo html_writer::end_div();

/*
 * Email filter.
 */

echo html_writer::start_div('col-md-4');

echo html_writer::label(
    'Email',
    'filter-email',
    false,
    ['class' => 'form-label']
);

echo html_writer::empty_tag('input', [
    'type' => 'email',
    'name' => 'email',
    'id' => 'filter-email',
    'value' => $email,
    'class' => 'form-control',
    'placeholder' => 'Search by email',
]);

echo html_writer::end_div();

/*
 * Filter buttons.
 */

echo html_writer::start_div('col-md-4');

echo html_writer::start_div(
    'd-flex gap-2'
);

echo html_writer::tag(
    'button',
    'Filter',
    [
        'type' => 'submit',
        'class' => 'btn btn-primary',
    ]
);

$clearurl = new moodle_url($page_path);

echo html_writer::link(
    $clearurl,
    'Clear',
    [
        'class' => 'btn btn-secondary',
    ]
);

echo html_writer::end_div();

echo html_writer::end_div();

echo html_writer::end_div();

echo html_writer::end_tag('form');

/*
 * -------------------------------------------------------------------------
 * Summary.
 * -------------------------------------------------------------------------
 */

$summary = get_string(
    'contactus_submissioncount',
    'local_stablezhelpers',
    $totalcount
);

echo html_writer::div(
    $summary,
    'mb-3'
);

/*
 * -------------------------------------------------------------------------
 * Submission table.
 * -------------------------------------------------------------------------
 */

$table = new html_table();

$table->attributes['class'] = 'generaltable';

$table->head = [
    '#',
    'Name',
    'Email',
    'Subject',
    'Status',
    'Modified',
    'Actions',
];

$table->align = [
    'left',
    'left',
    'left',
    'left',
    'center',
    'left',
    'left',
];

$table->data = [];

if ($submissions) {
    $counter = ($page * $perpage) + 1;

    foreach ($submissions as $submission) {
        /*
         * Status.
         */

        switch ((int) $submission->status) {
            case 1:
                $status = html_writer::span(
                    'Read',
                    'badge bg-success'
                );
                break;

            case 2:
                $status = html_writer::span(
                    'Replied',
                    'badge bg-info'
                );
                break;

            default:
                $status = html_writer::span(
                    'Unread',
                    'badge bg-warning text-dark'
                );
                break;
        }

        /*
         * Submission view URL.
         */

        $viewurl = new moodle_url(
            '/local/stablezhelpers/content/contact/view.php',
            [
                'id' => $submission->id,
            ]
        );

        $namecell = html_writer::link(
            $viewurl,
            s($submission->name)
        );

        /*
         * Actions.
         */

        $actionscell = html_writer::start_div(
            'd-flex gap-1 flex-wrap'
        );

        /*
         * Mark read/unread.
         */

        if ((int) $submission->status === 0) {
            $actionscell .= html_writer::start_tag(
                'form',
                [
                    'method' => 'post',
                    'action' => $PAGE->url->out(false),
                    'class' => 'd-inline',
                ]
            );

            $actionscell .= html_writer::input_hidden_params(
                new moodle_url(
                    $PAGE->url,
                    [
                        'page' => $page,
                        'name' => $name,
                        'email' => $email,
                    ]
                )
            );

            $actionscell .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'hidden',
                    'name' => 'action',
                    'value' => 'read',
                ]
            );

            $actionscell .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'hidden',
                    'name' => 'id',
                    'value' => $submission->id,
                ]
            );

            $actionscell .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'hidden',
                    'name' => 'sesskey',
                    'value' => sesskey(),
                ]
            );

            $actionscell .= html_writer::tag(
                'button',
                'Mark read',
                [
                    'type' => 'submit',
                    'class' => 'btn btn-sm btn-outline-success',
                ]
            );

            $actionscell .= html_writer::end_tag('form');
        } else {
            $actionscell .= html_writer::start_tag(
                'form',
                [
                    'method' => 'post',
                    'action' => $PAGE->url->out(false),
                    'class' => 'd-inline',
                ]
            );

            $actionscell .= html_writer::input_hidden_params(
                new moodle_url(
                    $PAGE->url,
                    [
                        'page' => $page,
                        'name' => $name,
                        'email' => $email,
                    ]
                )
            );

            $actionscell .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'hidden',
                    'name' => 'action',
                    'value' => 'unread',
                ]
            );

            $actionscell .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'hidden',
                    'name' => 'id',
                    'value' => $submission->id,
                ]
            );

            $actionscell .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'hidden',
                    'name' => 'sesskey',
                    'value' => sesskey(),
                ]
            );

            $actionscell .= html_writer::tag(
                'button',
                'Mark unread',
                [
                    'type' => 'submit',
                    'class' => 'btn btn-sm btn-outline-secondary',
                ]
            );

            $actionscell .= html_writer::end_tag('form');
        }

        /*
         * Delete form.
         */

        $actionscell .= html_writer::start_tag(
            'form',
            [
                'method' => 'post',
                'action' => $PAGE->url->out(false),
                'class' => 'd-inline',
            ]
        );

        $actionscell .= html_writer::input_hidden_params(
            new moodle_url(
                $PAGE->url,
                [
                    'page' => $page,
                    'name' => $name,
                    'email' => $email,
                ]
            )
        );

        $actionscell .= html_writer::empty_tag(
            'input',
            [
                'type' => 'hidden',
                'name' => 'action',
                'value' => 'delete',
            ]
        );

        $actionscell .= html_writer::empty_tag(
            'input',
            [
                'type' => 'hidden',
                'name' => 'id',
                'value' => $submission->id,
            ]
        );

        $actionscell .= html_writer::empty_tag(
            'input',
            [
                'type' => 'hidden',
                'name' => 'confirm',
                'value' => '1',
            ]
        );

        $actionscell .= html_writer::empty_tag(
            'input',
            [
                'type' => 'hidden',
                'name' => 'sesskey',
                'value' => sesskey(),
            ]
        );

        $actionscell .= html_writer::tag(
            'button',
            'Delete',
            [
                'type' => 'submit',
                'class' => 'btn btn-sm btn-outline-danger',
                'onclick' => 'return confirm("Are you sure you want to delete this submission?");',
            ]
        );

        $actionscell .= html_writer::end_tag('form');

        $actionscell .= html_writer::end_div();

        /*
         * Add table row.
         */

        $table->data[] = [
            $counter,
            $namecell,
            s($submission->email),
            s($submission->subject),
            $status,
            userdate($submission->timemodified),
            $actionscell,
        ];

        $counter++;
    }
} else {
    $emptyrow = new html_table_cell(
        html_writer::div(
            'No Contact Us submissions found.',
            'text-center p-3'
        )
    );

    $emptyrow->colspan = 7;

    $table->data[] = [
        $emptyrow,
    ];
}

echo html_writer::table($table);

/*
 * -------------------------------------------------------------------------
 * Pagination.
 * -------------------------------------------------------------------------
 */

if ($totalcount > $perpage) {
    $paginationurl = new moodle_url(
        $page_path,
        [
            'name' => $name,
            'email' => $email,
        ]
    );

    echo $OUTPUT->paging_bar(
        $totalcount,
        $page,
        $perpage,
        $paginationurl
    );
}
echo html_writer::end_div();

echo $OUTPUT->footer();
