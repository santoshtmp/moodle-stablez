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
 * Enrol user course list external API for local_stablezhelpers plugin.
 *
 * @package    local_stablezhelpers
 * @category   external
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\external\unepal;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_stablezhelpers\local\service\enrol_services;

defined('MOODLE_INTERNAL') || die();

/**
 * Enrol user course list external API class.
 */
class enrol_user_course_list extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function enrol_user_course_list_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_RAW, 'User ID', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_RAW, 'Course ID', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_RAW, 'Page number', VALUE_DEFAULT, ''),
            'perpage' => new external_value(PARAM_RAW, 'Page size', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Get list of courses where user is enrolled.
     *
     * @param mixed $userid User ID
     * @param mixed $courseid Course ID
     * @param mixed $page Page number
     * @param mixed $perpage Page size
     * @return array
     */
    public static function enrol_user_course_list($userid, $courseid, $page, $perpage) {

        $params = self::validate_parameters(
            self::enrol_user_course_list_parameters(),
            [
                'userid' => $userid,
                'courseid' => $courseid,
                'page' => $page,
                'perpage' => $perpage,
            ]
        );

        // Convert values safely.
        $userid = is_numeric($params['userid']) ? (int)$params['userid'] : 0;
        $courseid = is_numeric($params['courseid']) ? (int)$params['courseid'] : 0;
        $page = is_numeric($params['page']) ? (int)$params['page'] : 0;
        $perpage = is_numeric($params['perpage']) ? (int)$params['perpage'] : 30;

        // Prevent invalid values.
        $page = max(0, $page);
        $perpage = max(1, $perpage);

        $returndata = [
            'status' => false,
            'message' => '',
            'data' => [],
            'meta' => [
                'totalrecords' => 0,
                'page' => $page,
                'perpage' => $perpage,
                'totalpage' => 0,
                'datadisplaycount' => 0,
                'datafrom' => 0,
                'datato' => 0,
                'request_userid' => $userid,
                'request_courseid' => $courseid,
            ],
        ];

        $filterparam = [
            'userid' => $userid,
            'courseid' => $courseid,
            'spage' => $page,
            'perpage' => $perpage,
            'sortby' => 'firstname',
            'sortdir' => SORT_ASC,
        ];

        $enrolusercourse = enrol_services::get_enrol_user_course($filterparam);

        $returndata['status'] = true;
        $returndata['data'] = $enrolusercourse['data'] ?? [];
        $returndata['meta'] = $enrolusercourse['meta'] ?? $returndata['meta'];

        $returndata['meta']['request_userid'] = $userid;
        $returndata['meta']['request_courseid'] = $courseid;

        return $returndata;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function enrol_user_course_list_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status'),
            'message' => new external_value(PARAM_RAW, 'Response message', VALUE_OPTIONAL),

            'data' => new external_multiple_structure(
                new external_single_structure([
                    'userid' => new external_value(PARAM_INT, 'User ID', VALUE_OPTIONAL),
                    'username' => new external_value(PARAM_RAW, 'Username', VALUE_OPTIONAL),
                    'email' => new external_value(PARAM_RAW, 'Email', VALUE_OPTIONAL),
                    'user_fullname' => new external_value(PARAM_RAW, 'User full name', VALUE_OPTIONAL),
                    'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_OPTIONAL),
                    'course_shortname' => new external_value(PARAM_RAW, 'Course short name', VALUE_OPTIONAL),
                    'course_fullname' => new external_value(PARAM_RAW, 'Course full name', VALUE_OPTIONAL),
                    'course_categoryid' => new external_value(PARAM_INT, 'Course Category ID', VALUE_OPTIONAL),
                    'course_categoryname' => new external_value(PARAM_RAW, 'Course category name', VALUE_OPTIONAL),
                    'course_url' => new external_value(PARAM_RAW, 'Course URL', VALUE_OPTIONAL),
                    'course_image_url' => new external_value(PARAM_RAW, 'Course image URL', VALUE_OPTIONAL),
                    'category_url' => new external_value(PARAM_RAW, 'Category URL', VALUE_OPTIONAL),
                    'completion_progress_percentage' => new external_value(PARAM_RAW, 'User course completion progress percentage', VALUE_OPTIONAL),
                    'final_grade' => new external_value(PARAM_RAW, 'User final grade in course', VALUE_OPTIONAL),
                    'max_grade' => new external_value(PARAM_RAW, 'User max grade in course', VALUE_OPTIONAL),
                    'grade_percentage' => new external_value(PARAM_RAW, 'User grade percentage in course', VALUE_OPTIONAL),
                ]),
                'User enrolled courses data',
                VALUE_OPTIONAL
            ),

            'meta' => new external_single_structure(
                [
                    'totalrecords' => new external_value(PARAM_INT, 'Total number of records', VALUE_OPTIONAL),
                    'page' => new external_value(PARAM_INT, 'Current page number', VALUE_OPTIONAL),
                    'perpage' => new external_value(PARAM_INT, 'Number of records per page', VALUE_OPTIONAL),
                    'totalpage' => new external_value(PARAM_INT, 'Total number of pages', VALUE_OPTIONAL),
                    'datadisplaycount' => new external_value(PARAM_INT, 'Current page data count', VALUE_OPTIONAL),
                    'datafrom' => new external_value(PARAM_INT, 'Current page data from record number', VALUE_OPTIONAL),
                    'datato' => new external_value(PARAM_INT, 'Current page data to record number', VALUE_OPTIONAL),
                    'request_userid' => new external_value(PARAM_INT, 'User ID for the course list', VALUE_OPTIONAL),
                    'request_courseid' => new external_value(PARAM_INT, 'Course ID for the course list', VALUE_OPTIONAL),
                ],
                'Meta information',
                VALUE_OPTIONAL
            ),
        ]);
    }
}
