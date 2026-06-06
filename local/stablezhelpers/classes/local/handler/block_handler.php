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
 * Course link handler for local_stablezhelpers plugin.
 *
 * Provides functionality for managing course enrollment links.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\local\handler;

defined('MOODLE_INTERNAL') || die();


use core\output\html_writer;
use core\output\theme_config;
use local_stablezhelpers\local\service\course_service;
use local_stablezhelpers\local\stablezhelpers;
use theme_stablez\local\service\theme_settings_service;

defined('MOODLE_INTERNAL') || die();


/**
 * 
 */
class block_handler {
    /**
     * @var string $block_type Type of the stablez block
     */
    public $block_type;

    /**
     * @var \stdClass $block_settings
     */
    protected $block_settings;

    /**
     * @var \context $context
     */
    protected $context;


    /**
     * init
     */
    public function __construct($block_type, $block_settings) {
        global $COURSE;
        $this->block_type = $block_type;
        $this->block_settings = $block_settings;
        $this->context = \core\context\course::instance($COURSE->id); // \context_course::instance($COURSE->id);
    }

    /**
     * define blocks type
     * @return array
     */
    public static function get_stablez_blocks_types() {
        $block_types = [
            'progress_bar' => 'Progress Bar Block',
            'enrol_btn' => 'Enroll Button Block',
            'course_info' => 'Course Information Block',
            'course_list' => "Courses List Block",
            'contact_us' => "Contact Us",
            'start_guideline' => "Start Guideline",
        ];
        // array_multisort(array_column($block_types, 'name'), SORT_ASC, $block_types);;
        ksort($block_types);
        return $block_types;
    }
    /**
     * 
     */
    public static function course_fields_list() {
        $course_meta_var = [];
        global $OUTPUT, $COURSE, $DB;
        $course_metad_fields = course_service::get_course_customfields($COURSE->id, "key_array");
        foreach ($course_metad_fields as $key => $field) {
            $course_meta_var[$field['shortname']] = $field['name'];
        }
        $course_meta_var['description_summary'] = 'Course description summary';
        $course_meta_var[''] = '- Select -';
        ksort($course_meta_var);

        return $course_meta_var;
    }

    /**
     * get_block_stablez_content
     */
    public function get_block_stablez_content() {
        $method_name = $this->block_type . "_block_content";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return $this->default_block_content();
    }

    /**
     * default_block_content
     */
    protected function default_block_content() {
        if ($this->block_type) {
            return "Stablez Block Type (" . $this->block_type . ") is in development. Contact developer or site admin. ";
        }
        return "Stablez Block Type is not defined. Edit the block and set stablez Block Type";
    }

    /**
     * enrol_btn
     */
    protected function enrol_btn_block_content() {
        global $OUTPUT, $USER, $COURSE;
        $template_content = [];
        $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        $template_content['is_enrolled'] = is_enrolled($this->context, $USER);
        if (!$template_content['is_enrolled']) {
            $enrolinstances = enrol_get_instances((int)$COURSE->id, true);
            foreach ($enrolinstances as $key => $courseenrolinstance) {
                if (!in_array($courseenrolinstance->enrol, ['manual', 'guest'])) {
                    $template_content['enrol_url'] = stablezhelpers::get_moodle_url('/enrol/index.php', ['id' => $COURSE->id], true);
                    break;
                }
            }
        }

        return $OUTPUT->render_from_template("theme_stablez/blocks/enrol_btn", $template_content);
    }

    /**
     * progress_bar
     */
    protected function progress_bar_block_content() {
        global $OUTPUT, $USER, $COURSE;
        $template_content = [];
        $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        $template_content['is_enrolled'] = is_enrolled($this->context, $USER);
        $template_content['course_percentage'] = course_service::get_course_completion_progress($COURSE, $USER->id);
        return $OUTPUT->render_from_template("theme_stablez/blocks/progress-bar", $template_content);
    }

    /**
     * contact_us
     */
    protected function contact_us_block_content() {
        global $OUTPUT, $PAGE, $CFG, $SITE;
        $send_email = optional_param('send_email', 0, PARAM_INT);
        $sesskey = optional_param('sesskey', '', PARAM_RAW);
        $template_content = theme_settings_service::get_instance()->contact_details_settings();
        // check if the message is send or not
        if ($_POST && $send_email && $sesskey) {
            if ($sesskey == sesskey()) {
                $sendto_email = ($template_content['contact_form_recipient_email']) ?: $CFG->supportemail;
                $sendto_name = ($template_content['contact_form_recipient_name']) ?: $CFG->supportname;
                $form_name = optional_param('name', '', PARAM_TEXT);
                $form_email = optional_param('email', '', PARAM_TEXT);
                $form_subject = optional_param('subject', '', PARAM_TEXT);
                $form_message = optional_param('message', '', PARAM_TEXT);
                if ($form_name && $form_email && $form_message) {
                    $msg_subject =  $SITE->shortname  . " : Contact Us Message";
                    // 
                    $htmlmessage = "";
                    $htmlmessage .= html_writer::start_tag('div');
                    $htmlmessage .= html_writer::tag(
                        'p',
                        "Contact us form content :: "
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Name : ') . $form_name
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Email : ') . $form_email
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Subject : ') . $form_subject
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Message : ') . $form_message
                    );
                    $htmlmessage .= html_writer::end_tag('div');
                    // 
                    $response_msg_send = notify_handler::send_email(
                        $sendto_email,
                        $sendto_name,
                        $msg_subject,
                        $htmlmessage
                    );
                    if ($response_msg_send) {
                        $redirect_msg = "Your message is send sucessfully. We will Get in touch with you shorthly";
                    } else {
                        $redirect_msg = "Email configuration is not completed or Something went wrong !";
                    }
                } else {
                    $redirect_msg = "Required data is missing.";
                }
            } else {
                $redirect_msg = "Session time out.";
            }
            redirect($PAGE->url->out(), $redirect_msg);
        } else {
            $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
            $template_content['form_action'] = $PAGE->url->out();
            $template_content['title'] = format_string($this->block_settings->title);
            return $OUTPUT->render_from_template("theme_stablez/blocks/contact-us", $template_content);
        }
    }
    /**
     * course_list
     */
    protected function course_list_block_content() {
        global $OUTPUT;

        $course_list = '';
        $course_list_order = isset($this->block_settings->course_list_order) ? explode(',', $this->block_settings->course_list_order) : '';
        if ($course_list_order) {
            $course_list = [];
            foreach ($course_list_order as $key => $course_id) {
                $course_list[] = course_service::course_card_info($course_id, true);
            }
        }
        $template_content = [];
        $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        $template_content['courses'] = $course_list;
        return $OUTPUT->render_from_template("theme_stablez/blocks/course_list", $template_content);
    }

    /**
     * course_info
     */
    protected function course_info_block_content() {
        global $OUTPUT, $COURSE, $DB;

        $course_id = empty($this->block_settings->course_list) ? $COURSE->id : end($this->block_settings->course_list);

        $course_fields = $this->block_settings->course_fields_order;
        $course_fields = is_string($course_fields) ? json_decode($course_fields, true) : $course_fields;
        $course_fields = is_array($course_fields) ? $course_fields : [];

        $course_fields_metadata = course_service::get_course_customfields($course_id, "key_array");
        $course_fields_value = [];
        $index = 0;
        foreach ($course_fields as $field_shortname => $display_option) {
            if ($field_shortname === 'description_summary') {
                $course = $DB->get_record('course', ['id' => $course_id]);
                $label = 'Summary';
                $value = course_service::get_course_formatted_summary($course);
            } else {
                $label = $course_fields_metadata[$field_shortname]['name'] ?? '';
                $value = $course_fields_metadata[$field_shortname]['value'] ?? '';
            }
            if ($field_shortname === 'intro_video' &&  $value) {
                $video_id = $thumbnail_url = '';
                $video_url = $value;
                if (str_contains($value, 'vimeo.com')) {
                    $video_type = 'vimeo';
                } elseif (str_contains($value, 'youtube.com') || str_contains($value, 'youtu')) {
                    $video_type = 'youtube';
                } else {
                    $video_type = '';
                }
                if ($video_type == 'youtube') {
                    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*\?v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                    preg_match($pattern, $value, $matches);
                    // preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $value, $matches);
                    $video_id = isset($matches[1]) ? $matches[1] : $video_id;
                    $thumbnail_url = 'http://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
                    $video_url = "https://www.youtube.com/embed/" . $video_id;
                    $value = $video_url;
                }
                if ($video_type == 'vimeo') {
                    // preg_match('/<iframe[^>]+src="([^"]+)"/i', $value, $matches);
                    // $value = isset($matches[1]) ? $matches[1] : $value;
                    // preg_match('/vimeo\.com\/video\/(\d+)/', $embed_video, $matches);
                    $pattern = '/vimeo\.com\/(?:channels\/[\w]+\/|groups\/[\w]+\/videos\/|album\/\d+\/video\/|video\/|)(\d+)(?:\?.*?h=([\w\d]+))?/';
                    // $pattern = '/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(?:channels\/[\w]+\/|groups\/[\w]+\/videos\/|album\/\d+\/video\/|video\/|)(\d+)/';
                    preg_match($pattern, $value, $matches);
                    $video_id = isset($matches[1]) ? $matches[1] : $video_id;
                    $video_id = isset($matches[2]) ? $video_id . '/' . $matches[2] : $video_id;
                    // $thumbnail = self::get_vimeo_data_from_id($video_id, 'thumbnail_url');
                    // if ($thumbnail) {
                    //     $thumbnail_url = $thumbnail;
                    // }
                }
                $course_fields_value[$field_shortname]['thumbnail_url'] = $thumbnail_url;
                // var_dump($value);
                // var_dump($video_type);
                // var_dump($video_id);
                // var_dump($thumbnail_url);
            }

            // 
            $course_fields_value[$index][$field_shortname]['classname'] = str_replace("_", "-", $field_shortname);
            if ($display_option == 'value') {
                $course_fields_value[$index][$field_shortname]['value'] = $value;
            } else {
                $course_fields_value[$index][$field_shortname]['label'] = $label;
                $course_fields_value[$index][$field_shortname]['value'] = $value;
            }
            $index++;
        }

        $course_informations = '';
        foreach ($course_fields_value as $key => $fields_value) {
            $course_info =  $OUTPUT->render_from_template("theme_stablez/blocks/course_info", $fields_value);
            if (!$course_info) {
                $otherField = [
                    'otherfield' => reset($fields_value)
                ];
                $course_info =  $OUTPUT->render_from_template("theme_stablez/blocks/course_info", $otherField);
            }
            $course_informations .= $course_info;
        }
        // 
        $template_content = [];
        $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        $template_content['course_id'] = $course_id;
        // $template_content['course_fields'] = $course_fields_value;
        $template_content['course_informations'] = $course_informations;

        return $OUTPUT->render_from_template("theme_stablez/blocks/course_informations", $template_content);
    }

    /**
     * course_rating
     */
    protected function course_rating_block_content() {
        // global $OUTPUT, $PAGE;
        // $courserating = optional_param('courserating', 0, PARAM_INT);
        // if ($courserating == '1') {
        //     $rating = optional_param('rating', 0, PARAM_INT);
        //     $feedback = optional_param('feedback', 0, PARAM_TEXT);
        //     // var_dump($rating);
        //     // var_dump($feedback);
        //     $data = [
        //         'rating' => $rating,
        //         'feedback' => $feedback
        //     ];
        //     courserating_handler::save_data('', $data, $PAGE->url->out());
        // }
        // $get_path = $PAGE->url->get_path();
        // $params = $PAGE->url->params();
        // $params['courserating'] = 1;
        // $template_content = [];
        // $template_content['rating_action'] = (new \moodle_url($get_path, $params))->out(false);
        // $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        // return $OUTPUT->render_from_template("theme_stablez/blocks/course_rating", $template_content);
    }


    /**
     * faqs 
     */
    protected function faqs_block_content() {
        global $OUTPUT;
        $template_content = [];
        // $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        // $template_content['title'] = $this->block_settings->title;
        // $template_content['faq_datas'] = faqs_handler::get_faqs_question_data_in_array(-1);
        // return $OUTPUT->render_from_template("theme_stablez/blocks/faqs", $template_content);
    }


    /**
     * testimonial 
     */
    protected function testimonial_block_content() {
        global $OUTPUT;
        // $template_content = [];
        // $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        // $template_content['title'] = $this->block_settings->title;
        // $template_content['testimonial_datas'] = testimonial_handler::get_data_in_array(-1);
        // return $OUTPUT->render_from_template("theme_stablez/blocks/testimonial", $template_content);
    }

    /**
     * start_guideline
     */
    protected function start_guideline_block_content() {
        global $OUTPUT;
        $template_content = [];
        $template_content['block_stablez_id'] = $this->block_settings->block_stablez_id;
        $template_content['title'] = $this->block_settings->title;
        $template_content = array_merge($template_content, theme_settings_service::get_instance()->start_guideline_settings());
        return $OUTPUT->render_from_template("theme_stablez/blocks/start-guideline", $template_content);
    }

    /**
     * Grab the specified data like Thumbnail URL of a publicly embeddable video hosted on Vimeo.
     *
     * @param  str $video_id The ID of a Vimeo video.
     * @param  str $data 	  Video data to be fetched
     * @return str            The specified data
     */
    protected function get_vimeo_data_from_id($video_id, $data) {
        if (!$video_id) {
            return '';
        }
        // $request = wp_remote_get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $video_id);
        // $response = wp_remote_retrieve_body($request);
        // $video_array = json_decode($response, true);
        // if ($video_array) {
        //     return $video_array[$data];
        // } else {
        return '';
        // }
    }

    /**
     * ===== END =====
     */
}
