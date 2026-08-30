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
 * Contact Us manager class.
 *
 * Handles business logic for Contact Us functionality.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\content;

use html_writer;
use local_stablezhelpers\datarepository\contactus_datarepository;
use local_stablezhelpers\local\handler\notify_handler;
use theme_stablez\local\service\theme_settings_service;

defined('MOODLE_INTERNAL') || die();

/**
 * Contact Us manager.
 */
class contactus_manager {

    /**
     * Contact Us repository.
     *
     * @var contactus_datarepository
     */
    protected $repository;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->repository = new contactus_datarepository();
    }

    /**
     * Get Contact Us form data.
     *
     * @param \moodle_url $formaction Form action.
     * @return array Template data.
     */
    public function export_for_form_template() {
        global $PAGE;
        $contactdetails = theme_settings_service::get_instance()->contact_details_settings();
        $contactdetails['form_action'] = $PAGE->url->out();
        return $contactdetails;
    }

    /**
     * Process Contact Us form submission.
     *
     * @return void
     */
    public function process_contact_form() {
        global $PAGE, $USER;

        // Only process POST requests.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return '';
        }

        require_sesskey();

        $data = $this->get_form_submission_data();

        if (!$this->validate_submission($data)) {
            redirect(
                $PAGE->url,
                'Required data is missing or invalid.',
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }

        $data->userid = !empty($USER->id) ? $USER->id : 0;
        $data->status = 0;

        $submissionid = $this->repository->create($data);

        if (!$submissionid) {
            redirect(
                $PAGE->url,
                'Unable to save your message. Please try again later.',
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }

        if ($data->send_email) {
            $emailsent = $this->send_notification($data);

            if (!$emailsent) {
                redirect(
                    $PAGE->url,
                    'Your message was submitted, but the email notification could not be sent.',
                    null,
                    \core\output\notification::NOTIFY_WARNING
                );
            }
        }

        redirect(
            $PAGE->url,
            'Your message was submitted successfully. We will get in touch with you shortly.',
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    /**
     * Get submitted Contact Us form data.
     *
     * @return object Form data.
     */
    protected function get_form_submission_data() {
        $data = new \stdClass();

        $data->name = trim(optional_param('name', '', PARAM_TEXT));
        $data->email = trim(optional_param('email', '', PARAM_EMAIL));
        $data->subject = trim(optional_param('subject', '', PARAM_TEXT));
        $data->message = trim(optional_param('message', '', PARAM_TEXT));
        $data->other = trim(optional_param('other', '', PARAM_TEXT));
        $data->send_email = optional_param('send_email', 0, PARAM_BOOL);

        return $data;
    }

    /**
     * Validate Contact Us submission.
     *
     * @param object $data Submission data.
     * @return bool True if valid.
     */
    protected function validate_submission($data) {
        if (!$data->name) {
            return false;
        }

        if (!$data->email || !validate_email($data->email)) {
            return false;
        }

        if (!$data->message) {
            return false;
        }

        return true;
    }

    /**
     * Send Contact Us notification email.
     *
     * @param object $data Submission data.
     * @return bool True if sent.
     */
    protected function send_notification($data) {
        global $SITE, $CFG;
        $contactus_content = theme_settings_service::get_instance()->contact_details_settings();
        $recipientemail = ($contactus_content['contact_form_recipient_email']) ?: $CFG->supportemail;
        $recipientname = ($contactus_content['contact_form_recipient_name']) ?: $CFG->supportname;

        if (!$recipientemail || !validate_email($recipientemail)) {
            return false;
        }

        $subject = $SITE->shortname . ' : Contact Us Message';

        $message = $this->build_email_message($data);

        return notify_handler::send_email(
            $recipientemail,
            $recipientname,
            $subject,
            $message
        );
    }

    /**
     * Build Contact Us email content.
     *
     * @param object $data Submission data.
     * @return string HTML message.
     */
    protected function build_email_message($data) {
        $html = html_writer::start_tag('div');

        $html .= html_writer::tag(
            'h3',
            'Contact Us Form Submission'
        );

        $html .= html_writer::tag(
            'p',
            html_writer::tag('strong', 'Name: ') . s($data->name)
        );

        $html .= html_writer::tag(
            'p',
            html_writer::tag('strong', 'Email: ') . s($data->email)
        );

        $html .= html_writer::tag(
            'p',
            html_writer::tag('strong', 'Subject: ') . s($data->subject)
        );

        $html .= html_writer::tag(
            'p',
            html_writer::tag('strong', 'Message: ') . s($data->message)
        );

        if (!empty($data->other)) {
            $html .= html_writer::tag(
                'p',
                html_writer::tag('strong', 'Other: ') . s($data->other)
            );
        }

        $html .= html_writer::end_tag('div');

        return $html;
    }

    /**
     * Get submission.
     *
     * @param int $id Submission ID.
     * @return object|false Submission.
     */
    public function get_submission($id) {
        return $this->repository->get($id);
    }

    /**
     * Get filtered submissions.
     *
     * @param array $filters Filters.
     * @param int $limitfrom Starting record.
     * @param int $limitnum Records per page.
     * @return array Submission records.
     */
    public function get_filtered_submissions(
        $filters,
        $limitfrom = 0,
        $limitnum = 20
    ) {
        return $this->repository->get_filtered(
            $filters,
            $limitfrom,
            $limitnum
        );
    }

    /**
     * Count filtered submissions.
     *
     * @param array $filters Filters.
     * @return int Number of submissions.
     */
    public function get_filtered_submission_count($filters) {
        return $this->repository->count_filtered($filters);
    }

    /**
     * Get total submissions.
     *
     * @return int Number of submissions.
     */
    public function get_submission_count() {
        return $this->repository->count();
    }

    /**
     * Get submissions by status.
     *
     * @param int $status Status.
     * @return int Number of submissions.
     */
    public function get_submission_count_by_status($status) {
        return $this->repository->count_by_status($status);
    }

    /**
     * Mark submission as read.
     *
     * @param int $id Submission ID.
     * @return bool True on success.
     */
    public function mark_as_read($id) {
        return $this->repository->update_status($id, 1);
    }

    /**
     * Mark submission as unread.
     *
     * @param int $id Submission ID.
     * @return bool True on success.
     */
    public function mark_as_unread($id) {
        return $this->repository->update_status($id, 0);
    }

    /**
     * Mark submission as replied.
     *
     * @param int $id Submission ID.
     * @return bool True on success.
     */
    public function mark_as_replied($id) {
        return $this->repository->update_status($id, 2);
    }

    /**
     * Delete submission.
     *
     * @param int $id Submission ID.
     * @return bool True on success.
     */
    public function delete_submission($id) {
        return $this->repository->delete($id);
    }
}
