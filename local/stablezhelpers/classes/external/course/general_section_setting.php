<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External functions for managing the General section visibility.
 *
 * @package    local_stablezhelpers
 * @category   external
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\external\course;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use context_course;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use local_stablezhelpers\datarepository\coursemeta_datarepository;

/**
 * External functionality for managing the visibility of the General section.
 */
class general_section_setting extends external_api {

    /**
     * Initialise the General section Hide/Show functionality.
     *
     * This adds the required AMD JavaScript to the course page.
     *
     * The functionality is only initialised on the course page. The current
     * user's role, editing state, course ID, and current General section
     * visibility are passed to the AMD module.
     *
     * @return void
     */
    public static function setting_hideshow() {
        global $PAGE, $COURSE, $USER;

        if ($PAGE->pagelayout !== 'course') {
            return;
        }

        /** @var \context $context */
        $context = context_course::instance($COURSE->id);

        // Determine whether the current user has the student role.
        $roles = get_user_roles($context, $USER->id);

        $isstudent = false;

        foreach ($roles as $role) {
            if ($role->shortname === 'student') {
                $isstudent = true;
                break;
            }
        }

        // Get the saved General section visibility.
        $isvisible = coursemeta_datarepository::get(
            $COURSE->id,
            'course_general_section_visible'
        );

        // General section is visible by default when no setting exists.
        $isvisible = ($isvisible === null) ? true : $isvisible;

        $data = [
            'is_editing' => $PAGE->user_is_editing(),
            'is_visible' => (bool) $isvisible,
            'is_student' => $isstudent,
            'is_authuser'  => isloggedin() && !isguestuser(),
            'norole' => empty($roles),
            'courseid' => (int) $COURSE->id,
        ];

        // Initialise the General section AMD functionality.
        $PAGE->requires->js_call_amd(
            'local_stablezhelpers/course/general_section',
            'setting_hideshow',
            [
                'data' => $data,
            ]
        );
    }

    /**
     * Define the parameters for the external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(
                PARAM_INT,
                'Course ID'
            ),
            'visible' => new external_value(
                PARAM_BOOL,
                'Whether the General section should be visible'
            ),
        ]);
    }

    /**
     * Update the General section visibility.
     *
     * The user must have either the moodle/course:update or
     * moodle/course:manageactivities capability in the course context.
     *
     * @param int $courseid Course ID.
     * @param bool $visible Whether the General section should be visible.
     * @return array Result containing the update status, course ID,
     *     and current visibility state.
     */
    public static function execute($courseid, $visible) {
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'visible' => $visible,
            ]
        );

        $course = get_course($params['courseid']);

        /** @var \context $context */
        $context = context_course::instance($course->id);

        self::validate_context($context);

        // The user can manage the General section if they can either
        // update the course or manage course activities.
        $canupdatecourse = has_capability(
            'moodle/course:update',
            $context
        );

        $canmanageactivities = has_capability(
            'moodle/course:manageactivities',
            $context
        );

        if (!$canupdatecourse && !$canmanageactivities) {
            throw new \required_capability_exception(
                $context,
                'moodle/course:update',
                'nopermissions',
                ''
            );
        }

        // Save the General section visibility for the course.
        coursemeta_datarepository::set(
            $course->id,
            'course_general_section_visible',
            (bool) $params['visible']
        );

        return [
            'success' => true,
            'courseid' => (int) $course->id,
            'visible' => (bool) $params['visible'],
        ];
    }

    /**
     * Define the return structure for the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(
                PARAM_BOOL,
                'Whether the visibility update was successful'
            ),
            'courseid' => new external_value(
                PARAM_INT,
                'Course ID'
            ),
            'visible' => new external_value(
                PARAM_BOOL,
                'Current General section visibility'
            ),
        ]);
    }
}
