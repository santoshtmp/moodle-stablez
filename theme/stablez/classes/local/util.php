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
 * Custom util functions
 *
 */

namespace theme_stablez\local;

use local_stablezhelpers\local\service\course_service;
use local_stablezhelpers\local\service\enrol_services;
use local_stablezhelpers\local\stablezhelpers;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to get some util info in Moodle.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {


    public static function get_continue_last_courses() {
        return '';

        global $OUTPUT, $USER, $DB;
        $templatename = "theme_stablez/parts/continue-last-courses";
        $user_lastaccess = $DB->get_records('user_lastaccess', array('userid' => $USER->id), $sort = 'timeaccess desc', $fields = '*', $limitfrom = 0, $limitnum = 2);
        $lastaccess_courses = [];
        foreach ($user_lastaccess as $lastaccess) {
            $courseid = $lastaccess->courseid;

            if ($DB->record_exists('course', ['id' => $courseid])) {
                $course = get_course($courseid);

                $coursecategories = $DB->get_record('course_categories', ['id' => $course->category]);

                $courseInfo = [
                    'id' => $course->id,
                    'shortname' => $course->shortname,
                    'fullname' => format_string($course->fullname),
                    'category' => $course->category,
                    'categoryname' =>  $coursecategories ? format_string($coursecategories->name) : '',
                    'course_url' => stablezhelpers::get_moodle_url('/course/view.php', ['id' => $course->id], true),
                    'course_image_url' => course_service::get_course_image($course, true),
                    'category_url' => stablezhelpers::get_moodle_url('/course/index.php', ['categoryid' => $course->category], true),
                    'is_enrolled' => enrol_services::is_enrolled($USER->id, $course->id) ?? false,
                    'progress' => course_service::get_course_completion_progress($course, $USER->id),
                ];

                $lastaccess_courses[] = $courseInfo;
            }

            if (count($lastaccess_courses) >= 2) {
                break;
            }
        }

        $templatecontext = [
            'user' => $USER,
            'lastaccess_courses' => $lastaccess_courses,
        ];

        return $OUTPUT->render_from_template($templatename, $templatecontext);
    }
}
