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
 * Block edit form for block_stablezblocks.
 *
 * Defines the configuration form shown when editing a stablezblocks block instance.
 * Fields include block type selection, course list, course field display, and layout options.
 *
 * @package    block_stablezblocks
 * @copyright  2025 stablezblocks
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_stablezhelpers\local\handler\block_handler;

defined('MOODLE_INTERNAL') || die();

/**
 * Edit form class for the stablezblocks block.
 *
 * Extends block_edit_form to define block-specific configuration fields
 * such as block type, course selection, course metadata fields, and layout settings.
 */
class block_stablezblocks_edit_form extends block_edit_form {

    /**
     * Define block-specific form fields.
     *
     * Adds configuration fields for block type, course list, course info fields,
     * informational notices for special block types, and layout controls.
     *
     * @param MoodleQuickForm $mform The form object to add elements to.
     */
    protected function specific_definition($mform) {

        global $CFG, $PAGE, $COURSE;

        // ── Section: General Settings ─────────────────────────────────────────

        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // Block title displayed as a heading inside the block.
        $mform->addElement('text', 'config_title', 'Enter Title');
        $mform->setType('config_title', PARAM_TEXT);

        // Block type selector — determines which content this block renders.
        $block_stablezblocks_types = block_handler::get_stablez_blocks_types();
        $mform->addElement('autocomplete', 'config_stablez_block_type', 'Block Type', $block_stablezblocks_types, [
            'multiple'          => false,
            'noselectionstring' => 'Select Block Type',
        ]);
        $mform->addRule('config_stablez_block_type', get_string('required'), 'required', null, 'client');

        // Course list picker — shown only when block type is 'course_list' (controlled via JS).
        // Allows selecting multiple courses to display in the block.
        $mform->addElement('course', 'config_course_list', get_string('course'), [
            'multiple'          => true,
            'noselectionstring' => 'Select Courses',
        ]);
        // Hidden field to preserve the user-defined ordering of selected courses.
        $mform->addElement('hidden', 'config_course_list_order', '');
        $mform->setType('config_course_list_order', PARAM_TEXT);

        // Course field selector — shown only when block type is 'course_info'.
        // Allows choosing which course metadata field to display.
        $course_fields = block_handler::course_fields_list();
        $mform->addElement('select', 'config_course_fields', 'Course fields', $course_fields, [
            'multiple'          => false,
            'noselectionstring' => 'Select course fields',
        ]);
        $mform->hideIf('config_course_fields', 'config_stablez_block_type', 'neq', 'course_info');

        // Hidden field to preserve the user-defined ordering of selected course fields.
        $mform->addElement('hidden', 'config_course_fields_order', '');
        $mform->setType('config_course_fields_order', PARAM_TEXT);

        // ── Informational notices for special block types ─────────────────────
        // These static elements are shown/hidden based on the selected block type.

        // $mform->addElement('static', "contact_us_description", "", "Contact Us data is managed through <a href='/admin/settings.php?section=themesettingstablezblocks#general_setting_tab'> Theme stablezblocks settings</a>");
        // $mform->hideIf('contact_us_description', 'config_stablez_block_type', 'neq', 'contact_us');


        // Notice: FAQs block requires the theme FAQs feature to be enabled.
        $mform->addElement('static', 'faqs_description', '', "FAQs must be enabled in Theme stablezblocks settings. FAQs data is managed through <a href='/theme/stablezblocks/pages/faqs/admin.php'>FAQs Settings</a>.");
        $mform->hideIf('faqs_description', 'config_stablez_block_type', 'neq', 'faqs');

        // Notice: Testimonial block requires the theme testimonial feature to be enabled.
        $mform->addElement('static', 'testimonial_description', '', "Testimonials must be enabled in Theme stablezblocks settings. Testimonial data is managed through <a href='/theme/stablezblocks/pages/testimonial/admin.php'>Testimonial Settings</a>.");
        $mform->hideIf('testimonial_description', 'config_stablez_block_type', 'neq', 'testimonial');

        // Notice: Start Guideline block data is configured in the theme settings.
        $mform->addElement('static', 'start_guideline_description', '', "Start Guideline data is managed through <a href='/admin/settings.php?section=themesettingstablezblocks#frontpage_setting_tab'>Theme stablezblocks settings</a>.");
        $mform->hideIf('start_guideline_description', 'config_stablez_block_type', 'neq', 'start_guideline');

        // ── Section: Block Layout ─────────────────────────────────────────────

        $mform->addElement('header', 'config_header_layout', 'Block Layout');

        // Course-context-only options: enrolment linking between courses.
        // These fields are only relevant when the block is placed on a course page.
        if (
            $PAGE->pagetype == 'course-view' &&
            $PAGE->context->contextlevel == CONTEXT_COURSE &&
            $PAGE->context->instanceid == $COURSE->id
        ) {
            // Option to propagate the current course's links to other selected courses.
            $mform->addElement('select', 'config_apply_course_links', 'Apply current course links with other selected courses.', [
                0 => 'No',
                1 => 'Yes',
            ]);
            $mform->hideIf('config_apply_course_links', 'config_stablez_block_type', 'neq', 'course_list');

            // Explanatory note about the meta link enrolment side-effect.
            $mform->addElement('static', 'config_apply_course_links_description', '', 'This will also apply the "Course meta link" enrolment method to all selected courses, if Course meta link is enabled on this site.');
            $mform->hideIf('config_apply_course_links_description', 'config_stablez_block_type', 'neq', 'course_list');
        }

        // Option to hide the block's main heading.
        $mform->addElement('select', 'config_remove_main_heading', 'Remove main heading', [
            0 => 'No',
            1 => 'Yes',
        ]);

        // Option to render the block at full page width.
        $mform->addElement('select', 'config_full_width_section', 'Full width', [
            0 => 'No',
            1 => 'Yes',
        ]);

        // Initialise the edit form JS module for dynamic show/hide behaviour.
        // $PAGE->requires->js_call_amd('block_stablezblocks/edit-form', 'init');
        $mform->addElement('html', '
            <script>
                (function () {
                    var maxWait = 30000; // stop after 30 seconds
                    var interval = 50;  // poll every 50 ms
                    var elapsed  = 0;
 
                    var timer = setInterval(function () {
                        elapsed += interval;
 
                        if (typeof require !== "undefined") {
                            // RequireJS is available — load the module and stop polling.
                            clearInterval(timer);
                            require(["block_stablezblocks/edit-form"], function (EditForm) {
                                EditForm.init();
                            });
                        } else if (elapsed >= maxWait) {
                            // 30 s elapsed and RequireJS still not found — give up cleanly.
                            clearInterval(timer);
                            console.warn("block_stablezblocks/edit-form: RequireJS was not available after 30 s. AMD module not loaded.");
                        }
                        // Neither condition met — keep polling.
 
                    }, interval);
                })();
            </script>
        ');
    }

    /**
     * Validate the submitted form data.
     *
     * Extends parent validation. Additional field-level checks (e.g. duplicate
     * title detection) can be added here when required.
     *
     * @param array $data  Submitted form data.
     * @param array $files Submitted files.
     * @return array Associative array of validation errors (field name => message).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate title.
        // if ($data['title']) {
        //     $data_title = trim($data['title']);
        //     if ($existing = $DB->get_record("", array('title' => $data_title))) {
        //         if (!$data['id'] || $existing->id != $data['id']) {
        //             $errors['title'] = 'Title "' . trim($data['title']) . '" alrady exist.';
        //         }
        //     }
        // }

        return $errors;
    }

    /**
     * Return the submitted form data after validation.
     *
     * Wraps parent::get_data(). Post-processing of course list ordering or
     * course field ordering can be applied here before data is saved to the
     * block config.
     *
     * @return stdClass|null Submitted and validated data, or null if invalid/not submitted.
     */
    public function get_data() {
        $data = parent::get_data();
        // if ($data) {
        //     if ($data->config_stablez_block_type == 'course_list') {
        //         course_link_handler::save_data($data);
        //         $config_course_list_order = isset($data->config_course_list_order) ? explode(',', $data->config_course_list_order) : [];
        //         $data->config_course_list = $config_course_list_order;
        //     }
        //     if ($data->config_stablez_block_type == 'course_info') {
        //         $config_course_fields_order = isset($data->config_course_fields_order) ? explode(',', $data->config_course_fields_order) : [];
        //         $data->config_course_fields = $config_course_fields_order;
        //     }
        // }
        return $data;
    }
}
