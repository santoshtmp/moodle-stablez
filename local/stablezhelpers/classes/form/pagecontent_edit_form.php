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
 * Form for editing content in stablezhelpers plugin.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\form;

use local_stablezhelpers\content\page_manager;
use local_stablezhelpers\datarepository\content_datarepository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Form class for editing content.
 */
class pagecontent_edit_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        global $CFG;

        $mform = $this->_form;

        $contentdata = $this->_customdata['content'] ?? null;
        $returnurl = $this->_customdata['returnurl'] ?? '';

        $formheader = $contentdata ? 'editcontent' : 'addcontent';

        $context = \context_system::instance();

        // -------------------------------------------------------------------------
        // General section.
        // -------------------------------------------------------------------------

        $mform->addElement('header', 'generalsettings', get_string($formheader, 'local_stablezhelpers'));

        // Title.
        $mform->addElement('text', 'title', get_string('title', 'local_stablezhelpers'), ['size' => '60']);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('title', 'title', 'local_stablezhelpers');

        // Short name.
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_stablezhelpers'), ['size' => '60']);
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->addHelpButton('shortname', 'shortname', 'local_stablezhelpers');

        // // Content type selector.
        // $contenttypes = $this->get_content_types();
        // $mform->addElement('select', 'contenttype', get_string('contenttype', 'local_stablezhelpers'), $contenttypes);
        // $mform->setDefault('contenttype', 'page');

        // Content editor.
        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'trusttext' => true,
            'context' => $context,
            'subdirs' => false,
        ];

        $attr = 'cols="20" class="custom-pages-textarea" rows="15"';

        $mform->addElement('editor', 'content', get_string('content', 'local_stablezhelpers'), $attr, $editoroptions);
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');
        $mform->addHelpButton('content', 'content', 'local_stablezhelpers');

        // -------------------------------------------------------------------------
        // Feature image.
        // -------------------------------------------------------------------------

        $imageoptions = page_manager::get_image_filemanager_options();

        $mform->addElement(
            'filemanager',
            'image_filemanager',
            get_string('image', 'local_stablezhelpers'),
            null,
            $imageoptions
        );

        $mform->addHelpButton(
            'image_filemanager',
            'image_filemanager',
            'local_stablezhelpers'
        );

        // -------------------------------------------------------------------------
        // Status.
        // -------------------------------------------------------------------------

        $mform->addElement(
            'checkbox',
            'status',
            get_string('status', 'local_stablezhelpers'),
            get_string('publish', 'local_stablezhelpers')
        );

        $mform->setType('status', PARAM_INT);
        $mform->setDefault('status', 0);

        $mform->addHelpButton(
            'status',
            'status',
            'local_stablezhelpers'
        );

        // -------------------------------------------------------------------------
        // Hidden fields.
        // -------------------------------------------------------------------------

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'parentid');
        $mform->setType('parentid', PARAM_INT);
        $mform->setDefault('parentid', 0);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_TEXT);
        $mform->setDefault(
            'type',
            content_datarepository::DEFAULT_CONTENT_TYPE
        );

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);
        $mform->setDefault('returnurl', $returnurl);

        // -------------------------------------------------------------------------
        // Buttons.
        // -------------------------------------------------------------------------

        $this->add_action_buttons();

        // -------------------------------------------------------------------------
        // Set defaults when editing.
        // -------------------------------------------------------------------------

        if ($contentdata) {
            $this->set_data(
                $this->prepare_data_for_form($contentdata)
            );
        }
    }

    /**
     * Prepare content data for form.
     *
     * @param object $contentdata Content record.
     * @return array
     */
    private function prepare_data_for_form($contentdata) {
        global $CFG;

        $context = \context_system::instance();

        $data = clone $contentdata;

        // ---------------------------------------------------------------------
        // Content editor.
        // ---------------------------------------------------------------------

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'trusttext' => true,
            'noclean' => true,
            'context' => $context,
            'subdirs' => true,
        ];

        /*
         * IMPORTANT:
         *
         * This prepares the existing permanent files:
         *
         * local_stablezhelpers/content/<contentid>/
         *
         * into the user's draft area.
         *
         * It also prepares the editor data correctly.
         */
        $data = file_prepare_standard_editor(
            $data,
            'content',
            $editoroptions,
            $context,
            'local_stablezhelpers',
            'content',
            $contentdata->id
        );

        $data->content = $data->content_editor;

        // ---------------------------------------------------------------------
        // Feature image.
        // ---------------------------------------------------------------------

        if (!empty($contentdata->image)) {
            $draftitemid = file_get_submitted_draft_itemid(
                'image_filemanager'
            );

            $imageoptions = page_manager::get_image_filemanager_options();

            file_prepare_draft_area(
                $draftitemid,
                $context->id,
                'local_stablezhelpers',
                'content_page_image',
                $contentdata->id,
                $imageoptions
            );

            $data->image_filemanager = $draftitemid;
        }

        return (array)$data;
    }

    /**
     * Get available content types.
     *
     * @return array
     */
    private function get_content_types(): array {
        return [
            'page' => get_string(
                'contenttype_page',
                'local_stablezhelpers'
            ),
            'faq' => get_string(
                'contenttype_faq',
                'local_stablezhelpers'
            ),
            'testimonial' => get_string(
                'contenttype_testimonial',
                'local_stablezhelpers'
            ),
            'article' => get_string(
                'contenttype_article',
                'local_stablezhelpers'
            ),
            'video' => get_string(
                'contenttype_video',
                'local_stablezhelpers'
            ),
        ];
    }

    /**
     * Validation.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (empty(trim($data['title']))) {
            $errors['title'] = get_string('required');
        }

        if (empty($data['content']['text'])) {
            $errors['content'] = get_string('required');
        }

        if (!empty($data['shortname'])) {
            $datashortname = trim($data['shortname']);

            $existing = $DB->get_record(
                'local_stablezhelpers_content',
                ['shortname' => $datashortname]
            );

            if ($existing && (!$data['id'] || $existing->id != $data['id'])) {
                $errors['shortname'] =
                    'Short name "' .
                    $datashortname .
                    '" already exists.';
            }
        }

        return $errors;
    }
}
