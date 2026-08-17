<?php

require_once(dirname(__FILE__) . '/../../../config.php');

global $DB, $PAGE;

// Get course ID.
$courseid = required_param('courseid', PARAM_INT);

// Check that the course exists.
$course = $DB->get_record(
    'course',
    ['id' => $courseid],
    '*',
    MUST_EXIST
);

// Get course context.
/** @var \context $coursecontext Course context instance. */
$coursecontext = context_course::instance($courseid);

// Check permission.
require_capability(
    'moodle/course:manageactivities',
    $coursecontext
);

// Set page URL and context.
$PAGE->set_url(
    new moodle_url(
        '/theme/stablez/content/set-activity-restriction.php',
        ['courseid' => $courseid]
    )
);

$context = context_system::instance();
$PAGE->set_context($context);

// Get course module IDs.
$course_index = new \local_stablezhelpers\local\feature\course_index($courseid);
$cmids = $course_index->get_cmids();

$previouscmid = null;

foreach ($cmids as $cmid) {
    /*
     * Base restriction:
     *
     * User must have role ID 5.
     */
    $availability = [
        'op' => '&',
        'c' => [
            [
                'type' => 'role',
                'typeid' => 0,
                'id' => 5,
            ],
        ],
        'showc' => [
            true,
        ],
    ];

    /*
     * Check whether the previous activity/resource
     * has completion tracking enabled.
     */
    if ($previouscmid !== null) {

        $previouscm = $DB->get_record(
            'course_modules',
            ['id' => $previouscmid],
            'id, completion',
            IGNORE_MISSING
        );

        /*
         * If previous activity has completion tracking enabled,
         * require it to be completed before this activity is available.
         */
        if (
            $previouscm &&
            $previouscm->completion != COMPLETION_TRACKING_NONE
        ) {

            $availability['c'][] = [
                'type' => 'completion',
                'cm' => $previouscmid,
                'e' => 1,
            ];

            $availability['showc'][] = true;
        }
    }

    /*
     * Save availability restriction.
     */
    $DB->set_field(
        'course_modules',
        'availability',
        json_encode($availability),
        ['id' => $cmid]
    );

    /*
     * Current module becomes previous module.
     */
    $previouscmid = $cmid;
}

/*
 * Rebuild Moodle course cache.
 */
rebuild_course_cache($courseid, true);

/*
 * Redirect back to the course.
 */
redirect(
    new moodle_url(
        '/course/view.php',
        ['id' => $courseid]
    ),
    "Sucessfully update module access restriction.",
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
