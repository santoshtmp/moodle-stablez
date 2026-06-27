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
 * Grade service class for local_stablezhelpers plugin.
 *
 * Provides service methods for grade-related operations including
 * user course grades, grade items, and grade calculations.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\local\service;

use stdClass;
use grade_plugin_return;
use graded_users_iterator;
use gradereport_user\report\user as user_report;

defined('MOODLE_INTERNAL') || die();

/**
 * Grade service class for handling grade-related data operations.
 */
class grade_service {

    /**
     * Returns the user's course grade.
     *
     * @param int|null $userid User ID (null for current user).
     * @param int $courseid Course ID.
     * @return stdClass|null
     */
    public static function get_user_course_grade($userid, $courseid) {
        global $CFG, $USER;

        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->libdir . '/grade/grade_item.php');

        if (empty($userid)) {
            $userid = $USER->id;
        }

        // Get course grade item (course total)
        $gradeitem = \grade_item::fetch_course_item($courseid);

        if (!$gradeitem) {
            return null;
        }
        // Get user grade
        $grade = \grade_grade::fetch([
            'itemid' => $gradeitem->id,
            'userid' => $userid
        ]);

        if (!$grade || is_null($grade->finalgrade)) {
            return null;
        }

        $result = new stdClass();
        $result->finalgrade = round($grade->finalgrade, 2);
        $result->grademax   = round($gradeitem->grademax, 2);
        $result->gradepercentage = $gradeitem->grademax > 0
            ? round(($grade->finalgrade / $gradeitem->grademax) * 100, 2)
            : null;
        $result->formatted  = \grade_format_gradevalue($grade->finalgrade, $gradeitem);
        $result->feedback   = $grade->feedback ?? null;

        return $result;
    }

    /**
     * @param int $userid user ID.
     * @param int $courseid Course ID.
     * @param string $itemtype item type.
     * 
     * @return array 
     */
    public static function get_user_course_grades($userid, $courseid,  $itemtype = 'course') {
        global $DB;

        $grade_sql = "SELECT gg.finalgrade, gi.grademax
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.itemtype = :itemtype
                AND gi.courseid = :courseid
                AND gg.userid = :userid";
        $param = [
            'itemtype' => $itemtype,
            'courseid' => $courseid,
            'userid' => $userid
        ];
        $gradeResult = $DB->get_records_sql($grade_sql, $param);
        return $gradeResult;
    }

    /**
     * Returns all grades for a course.
     *
     * @param int $courseid Course ID.
     * @return array Array of grade objects keyed by user ID.
     */
    public static function get_course_grades($courseid) {
        return 'not developed';
    }

    /**
     * Get the report data
     * @param  stdClass $course  course object
     * @param  stdClass $context context object
     * @param  null|stdClass $user    user object (it can be null for all the users)
     * @param  int $userid       the user to retrieve data from, 0 for all
     * @param  int $groupid      the group id to filter
     * @param  bool $tabledata   whether to get the table data (true) or the gradeitemdata
     * 
     * From public/grade/report/user/classes/external/user.php
     * 
     */
    public static function get_report_data(
        stdClass $course,
        stdClass $context,
        ?stdClass $user,
        int $userid,
        int $groupid,
        bool $tabledata = true
    ) {
        global $CFG;

        // Require files here to save some memory in case validation fails.
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir  . '/gradelib.php');
        require_once($CFG->dirroot . '/grade/lib.php');
        require_once($CFG->dirroot . '/grade/report/user/lib.php');

        // Force regrade to update items marked as 'needupdate'.
        grade_regrade_final_grades($course->id);

        $gpr = new grade_plugin_return(
            [
                'type'           => 'report',
                'plugin'         => 'user',
                'courseid'       => $course->id,
                'courseidnumber' => $course->idnumber,
                'userid'         => $userid
            ]
        );

        $reportdata = [];

        // Just one user.
        if ($user) {
            $report = new user_report($course->id, $gpr, $context, $userid);
            $report->fill_table();

            $gradeuserdata = [
                'courseid'       => $course->id,
                'courseidnumber' => $course->idnumber,
                'userid'         => $user->id,
                'userfullname'   => fullname($user),
                'useridnumber'   => $user->idnumber,
                'maxdepth'       => $report->maxdepth,
            ];
            if ($tabledata) {
                $gradeuserdata['tabledata'] = $report->tabledata;
            } else {
                $gradeuserdata['gradeitems'] = $report->gradeitemsdata;
            }
            $reportdata[] = $gradeuserdata;
        } else {
            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

            $gui = new graded_users_iterator($course, null, $groupid);
            $gui->require_active_enrolment($showonlyactiveenrol);
            $gui->init();

            while ($userdata = $gui->next_user()) {
                $currentuser = $userdata->user;
                $report = new user_report($course->id, $gpr, $context, $currentuser->id);
                $report->fill_table();

                $gradeuserdata = [
                    'courseid'       => $course->id,
                    'courseidnumber' => $course->idnumber,
                    'userid'         => $currentuser->id,
                    'userfullname'   => fullname($currentuser),
                    'useridnumber'   => $currentuser->idnumber,
                    'maxdepth'       => $report->maxdepth,
                ];
                if ($tabledata) {
                    $gradeuserdata['tabledata'] = $report->tabledata;
                } else {
                    $gradeuserdata['gradeitems'] = $report->gradeitemsdata;
                }
                $reportdata[] = $gradeuserdata;
            }
            $gui->close();
        }
        return $reportdata;
    }
}
