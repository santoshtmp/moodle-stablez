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
 * Base helper class for local_stablezhelpers course index.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\local\feature;

defined('MOODLE_INTERNAL') || die();

/**
 * Independent Moodle course index builder.
 *
 * Supports:
 * - Sections.
 * - Subsections.
 * - Activities/resources.
 * - Original Moodle ordering.
 * - Nested subsections.
 * - Completion status.
 * - Current activity.
 * - Current section.
 * - Conditional availability/restriction information.
 * - Locked/unlocked state.
 *
 * Usage:
 *
 * $index = new course_index(2);
 * $data = $index->get_data();
 *
 * Or:
 *
 * $index = new course_index($course);
 * $data = $index->get_data();
 *
 * With current activity:
 *
 * $index = new course_index(2, $cmid);
 * $data = $index->get_data();
 */
class course_index {

    /** @var \stdClass */
    protected $course;

    /** @var \course_modinfo */
    protected $modinfo;

    /** @var \completion_info */
    protected $completioninfo;

    /** @var int|null */
    protected $currentcmid;

    /** @var array */
    protected $cmids = [];

    /**
     * Constructor.
     *
     * @param int|\stdClass $course Course ID or course object.
     * @param int|null $currentcmid Current course module ID.
     */
    public function __construct($course, ?int $currentcmid = null) {
        if (is_numeric($course)) {
            $course = get_course((int) $course);
        }

        if (!is_object($course) || empty($course->id)) {
            throw new \coding_exception('Invalid course supplied.');
        }

        $this->course = $course;
        $this->modinfo = get_fast_modinfo($course->id);
        $this->completioninfo = new \completion_info($course);
        $this->currentcmid = $currentcmid;
    }

    /**
     * Get complete course index.
     *
     * @return array
     */
    public function get_data(): array {
        $sections = [];

        foreach ($this->modinfo->get_section_info_all() as $sectioninfo) {
            /*
             * Subsections are rendered inside their parent.
             */
            if ($this->is_subsection($sectioninfo)) {
                continue;
            }

            if (!$this->can_view_section($sectioninfo)) {
                continue;
            }

            $section = $this->build_section($sectioninfo, 0);

            if ($section !== null) {
                $sections[] = $section;
            }
        }

        return [
            'courseid' => (int) $this->course->id,
            'coursename' => format_string($this->course->fullname),
            'sections' => $sections,
        ];
    }

    /**
     * Get previous and next accessible activities/resources.
     *
     * The order comes directly from Moodle's modinfo section order.
     *
     * @return array
     */
    protected function get_activity_navigation(): array {
        $previous = null;
        $next = null;

        if (empty($this->cmids)) {
            $this->get_data();
        }

        $nummods = count($this->cmids);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return [];
        }

        $position = array_search($this->currentcmid, $this->cmids);


        // Check if we have a previous mod to show.
        if ($position > 0) {
            $previous = $this->cmids[$position - 1];
            $previous = $this->modinfo->cms[$previous];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $next = $this->cmids[$position + 1];
            $next = $this->modinfo->cms[$next];
        }

        return [
            'hasprevious' => $previous !== null,
            'hasnext' => $next !== null,
            'previous' => $this->prepare_navigation_activity($previous),
            'next' => $this->prepare_navigation_activity($next),
        ];
    }

    /**
     * Prepare activity data for navigation.
     *
     * @param \cm_info|null $cm
     * @return array|null
     */
    protected function prepare_navigation_activity($cm): ?array {
        if (!$cm) {
            return null;
        }

        $icon = '';

        if ($cm->get_icon_url()) {
            $icon = $cm->get_icon_url()->out(false);
        }

        return [
            'id' => (int) $cm->id,
            'name' => $cm->get_formatted_name(),
            'url' => $cm->url
                ? $cm->url->out(false)
                : '',
            'modname' => $cm->modname,
            'icon' => $icon,
        ];
    }

    /**
     * Build a section.
     *
     * Activities and subsections are stored together in
     * children so Moodle's original order is preserved.
     *
     * @param \section_info $sectioninfo
     * @param int $level
     * @return array|null
     */
    protected function build_section(
        $sectioninfo,
        int $level = 0
    ): ?array {

        $sectionurl = new \moodle_url(
            '/course/section.php',
            [
                'id' => $sectioninfo->id,
            ]
        );

        $data = [
            'type' => 'section',

            'issection' => true,
            'isactivity' => false,

            'id' => (int) $sectioninfo->id,

            'section' => (int) $sectioninfo->section,

            'name' => get_section_name(
                $this->course,
                $sectioninfo
            ),

            'url' => $sectionurl->out(false),

            'level' => $level,

            'current' => false,

            /*
             * Section restriction information.
             */
            'restriction' => $this->get_section_restriction(
                $sectioninfo
            ),

            /*
             * Activities and subsections
             */
            'children' => [],
        ];

        /*
         * Moodle's original course order.
         */
        foreach ($this->get_section_cms($sectioninfo) as $cm) {

            if ($cm->deletioninprogress) {
                continue;
            }

            /*
             * SUBSECTION
             */
            if ($cm->modname === 'subsection') {
                $sectionid = $this->get_subsection_sectionid($cm);
                if (!$sectionid) {
                    continue;
                }

                $sectioninfo = $this->modinfo->get_section_info_by_id($sectionid);
                if (!$sectioninfo) {
                    continue;
                }

                if (!$this->can_view_section($sectioninfo)) {
                    continue;
                }

                $data['children'][] = $this->build_section(
                    $sectioninfo,
                    $level
                );

                continue;
            }

            /*
             * ACTIVITY / RESOURCE
             */
            $data['children'][] = $this->build_activity($cm);
            if (!$this->can_view_activity($cm)) {
                continue;
            }
            $this->cmids[] = $cm->id;
        }
        $data['haschildren'] = !empty($data['children']);

        // 
        $is_current = false;
        if ($data['haschildren']) {
            foreach ($data['children'] as $key => $value) {
                if ($value['current'] ?? false) {
                    $is_current = true;
                    break;
                }
            }
        }
        if ($is_current) {
            $data['current'] = true;
        }

        return $data;
    }

    /**
     * Get course modules in original Moodle order.
     *
     * @param \section_info $sectioninfo
     * @return array
     */
    protected function get_section_cms($sectioninfo): array {
        $sectionnum = $sectioninfo->section;

        if (!isset($this->modinfo->sections[$sectionnum])) {
            return [];
        }

        $cms = [];

        /*
         * DO NOT SORT.
         *
         * Moodle already provides the correct order.
         */
        foreach ($this->modinfo->sections[$sectionnum] as $cmid) {
            if (!isset($this->modinfo->cms[$cmid])) {
                continue;
            }

            $cms[] = $this->modinfo->cms[$cmid];
        }

        return $cms;
    }

    /**
     * Build activity.
     *
     * @param \cm_info $cm
     * @return array
     */
    protected function build_activity($cm): array {
        $icon = '';

        $iconurl = $cm->get_icon_url();

        if ($iconurl) {
            $icon = $iconurl->out(false);
        }

        $iscurrent = (
            $this->currentcmid !== null
            && (int) $this->currentcmid === (int) $cm->id
        );

        return [
            'type' => 'activity',

            /*
             * These flags are only here to make Mustache
             * recursive rendering easy.
             */
            'isactivity' => true,
            'issection' => false,

            'id' => (int) $cm->id,

            'name' => $cm->get_formatted_name(),

            'url' => $cm->url
                ? $cm->url->out(false)
                : '',

            'modname' => $cm->modname,

            'icon' => $icon,

            'visible' => (bool) $cm->visible,

            'uservisible' => (bool) $cm->uservisible,

            /*
             * Current activity.
             */
            'current' => $iscurrent,

            /*
             * Completion.
             */
            'completion' => $this->get_completion_data($cm),

            /*
             * Conditional access / restriction.
             */
            'restriction' => $this->get_activity_restriction($cm),
        ];
    }

    /**
     * Get activity completion data.
     *
     * @param \cm_info $cm
     * @return array
     */
    protected function get_completion_data($cm): array {
        $result = [
            'enabled' => false,
            'state' => null,
            'complete' => false,
        ];

        /*
         * Completion isn't enabled for this activity.
         */
        if (!$this->completioninfo->is_enabled($cm)) {
            return $result;
        }

        $result['enabled'] = true;

        $completion = $this->completioninfo->get_data(
            $cm,
            true
        );

        if (!$completion) {
            return $result;
        }

        $state = (int) $completion->completionstate;

        $result['state'] = $state;

        /*
         * Normal completed state.
         */
        if ($state === COMPLETION_COMPLETE) {
            $result['complete'] = true;
        }

        /*
         * Passed state.
         */
        if (
            defined('COMPLETION_COMPLETE_PASS')
            && $state === COMPLETION_COMPLETE_PASS
        ) {
            $result['complete'] = true;
        }

        /*
         * Failed state is also a completed attempt from
         * the activity-completion perspective.
         */
        if (
            defined('COMPLETION_COMPLETE_FAIL')
            && $state === COMPLETION_COMPLETE_FAIL
        ) {
            $result['complete'] = true;
        }

        if ($result['complete']) {
            $result['statuscomplete'] = true;
        } else {
            $result['statusincomplete'] = true;
        }


        return $result;
    }

    /**
     * Get activity restriction/access information.
     *
     * @param \cm_info $cm
     * @return array
     */
    protected function get_activity_restriction($cm): array {

        $result = [
            'restricted' => false,
            'available' => true,
            'canaccess' => true,
            'showlock' => false,
            'info' => '',
        ];

        /*
         * If the activity is available to the user,
         * there is no conditional-access lock.
         */
        if ($cm->uservisible) {
            return $result;
        }

        /*
         * A hidden activity is different from an activity
         * locked by conditional access.
         *
         * If it is simply hidden, don't display a lock
         * as if it were a restriction.
         */
        if (!$cm->visible) {
            return $result;
        }

        /*
         * Moodle's cm_info contains the result of the
         * availability calculation.
         */
        $result['restricted'] = true;
        $result['available'] = false;
        $result['canaccess'] = false;

        /*
         * Moodle sets availableinfo when the item should
         * be shown but is unavailable because of a condition.
         */
        if (!empty($cm->availableinfo)) {
            $result['showlock'] = true;
            $result['info'] = $cm->availableinfo;
        } else {
            /*
             * It may still be restricted even when there is
             * no displayable availability message.
             */
            $result['showlock'] = true;
        }

        return $result;
    }

    /**
     * Get section restriction/access information.
     *
     * @param \section_info $sectioninfo
     * @return array
     */
    protected function get_section_restriction($sectioninfo): array {

        $result = [
            'restricted' => false,
            'available' => true,
            'canaccess' => true,
            'showlock' => false,
            'info' => '',
        ];

        if ($sectioninfo->uservisible) {
            return $result;
        }

        /*
         * Hidden sections are handled separately from
         * conditional availability.
         */
        if (!$this->can_view_section($sectioninfo)) {
            return $result;
        }

        /*
         * If the user can view the hidden section,
         * but it is unavailable due to availability
         * conditions, get its restriction information.
         */
        if (
            !empty($sectioninfo->availability)
            && class_exists(
                '\core_availability\info_section'
            )
        ) {

            $availability =
                new \core_availability\info_section(
                    $sectioninfo
                );

            $information = '';

            $available =
                $availability->is_available(
                    $information,
                    true,
                    0,
                    $this->modinfo
                );

            if (!$available) {
                $result['restricted'] = true;
                $result['available'] = false;
                $result['canaccess'] = false;
                $result['showlock'] = true;
                $result['info'] = $information;
            }
        }

        return $result;
    }

    /**
     * Get subsection section ID.
     *
     * @param \cm_info $cm
     * @return int|null
     */
    protected function get_subsection_sectionid($cm): ?int {
        global $DB;

        $sectionid = $DB->get_field(
            'course_sections',
            'id',
            [
                'component' => 'mod_subsection',
                'itemid' => $cm->instance,
            ]
        );

        if ($sectionid) {
            return (int) $sectionid;
        }

        return null;
    }

    /**
     * Determine whether a section is a subsection.
     *
     * @param \section_info $sectioninfo
     * @return bool
     */
    protected function is_subsection($sectioninfo): bool {
        return !empty($sectioninfo->component)
            && $sectioninfo->component === 'mod_subsection';
    }

    /**
     * Check section visibility.
     *
     * @param \section_info $sectioninfo
     * @return bool
     */
    protected function can_view_section($sectioninfo): bool {
        if ($sectioninfo->uservisible) {
            return true;
        }

        return has_capability(
            'moodle/course:viewhiddensections',
            \context_course::instance($this->course->id)
        );
    }

    /**
     * Check activity visibility.
     *
     * @param \cm_info $cm
     * @return bool
     */
    protected function can_view_activity($cm): bool {
        if ($cm->deletioninprogress || $cm->is_stealth() || empty($cm->url)) {
            return false;
        }

        if ($cm->uservisible) {
            return true;
        }

        return has_capability(
            'moodle/course:viewhiddenactivities',
            \context_course::instance($this->course->id)
        );
    }

    /**
     * Render course index using Mustache template.
     *
     * @return string
     */
    public function render_course_index(): string {
        global $OUTPUT;
        $data = $this->get_data();
        return $OUTPUT->render_from_template('local_stablezhelpers/course_index/index', $data);
    }
    /**
     * Render course activity navigation using Mustache template.
     *
     * @return string
     */
    public function render_course_activity_navigation(): string {
        global $OUTPUT;
        return $OUTPUT->render_from_template(
            'local_stablezhelpers/course_index/navigation',
            $this->get_activity_navigation()
        );
    }

    /**
     * 
     */
    public function get_cmids(): array {
        if (empty($this->cmids)) {
            $this->get_data();
        }
        return $this->cmids;
    }
}
