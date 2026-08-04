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
 * stablez plugin config settings
 * @package   theme_stablez
 * @copyright 2025 stablez
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_stablez\form;

use admin_setting_configcheckbox;
use admin_setting_configcolourpicker;
use admin_setting_confightmleditor;
use admin_setting_configpasswordunmask;
use admin_setting_configselect;
use admin_settingpage;
use admin_setting_configstoredfile;
use admin_setting_configtext;
use admin_setting_configtextarea;
use admin_setting_heading;
use admin_setting_scsscode;

defined('MOODLE_INTERNAL') || die;


/**
 * 
 */
class theme_settings {


    /**
     * general_setting   
     * 
     */
    public static function general_setting($settings) {
        global $CFG;
        $general_tab = new admin_settingpage('general_setting_tab', get_string('general', 'theme_stablez'));

        /**
         * -------------------- Setting heading :: Logo and Favicon --------------------
         */
        $name = 'theme_stablez/stablez_general_logo';
        $heading = 'Logo and Favicon';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $general_tab->add($setting);

        // Logo setting.
        $filearea = 'logo';
        $name = 'theme_stablez/' . $filearea;
        $title = get_string('logo', 'theme_stablez');
        $description = '';
        $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.tiff', '.svg', '.webp'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $opts);
        $general_tab->add($setting);

        // Logo Description setting.
        $name = 'theme_stablez/logo_description';
        $title = 'Logo Description';
        $description = get_string('logo_description_des', 'theme_stablez');
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description,  $default, $paramtype = PARAM_RAW, $cols = '60', $rows = '4');
        $general_tab->add($setting);

        // Favicon setting.
        $filearea = 'favicon';
        $name = 'theme_stablez/' . $filearea;
        $title = get_string('favicon', 'theme_stablez');;
        $description = '';
        $opts = array('accepted_types' => array('.ico', '.jpeg', '.jpg', '.png', '.svg'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $opts);
        $general_tab->add($setting);

        // $setting = new admin_setting_heading('general_setting_other_separator', '', '<hr>');
        // $general_tab->add($setting);

        // $name = 'theme_stablez/general_setting';
        // $heading = 'General Setting';
        // $information = '';
        // $setting = new admin_setting_heading($name, $heading, $information);
        // $general_tab->add($setting);

        /**
         * -------------------- Setting heading :: Contact Detain --------------------
         */
        $name = 'theme_stablez/stablez_general_contact';
        $heading = 'Contact Detail';
        $information = '+ For more <a href="/admin/settings.php?section=supportcontact"> Support contact </a> ';
        $setting = new admin_setting_heading($name, $heading, $information);
        $general_tab->add($setting);

        // Contact Form Recipient Email
        $name = 'theme_stablez/contact_form_recipient_email';
        $title = 'Recipient Email';
        $description = 'The email of contact form recipient.';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $general_tab->add($setting);

        // Contact Form Recipient Name
        $name = 'theme_stablez/contact_form_recipient_name';
        $title = 'Recipient Name';
        $description = 'The Name of contact form recipient.';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $general_tab->add($setting);

        $setting = new admin_setting_heading('contact_form_recipient_separator', '', '<hr>');
        $general_tab->add($setting);


        // contact institution name
        $name = 'theme_stablez/contact_name';
        $title = "Contact Name";
        $description = "Contact company/institution name";
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $general_tab->add($setting);

        // location address.
        $name = 'theme_stablez/location_address';
        $title = "Location address";
        $description = "";
        $default = 'Kathmandu, Nepal';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $general_tab->add($setting);

        // map_location.
        $name = 'theme_stablez/map_location';
        $title = 'Map Location Point';
        $description = 'Map Location point; Support iframe tag or only src';
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description,  $default);
        $general_tab->add($setting);

        // phone_num.
        $name = 'theme_stablez/phone_number';
        $title = get_string('phone_number', 'theme_stablez');
        $description = get_string('phone_numberdesc', 'theme_stablez');
        $default = '98xxxxxxxx';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $general_tab->add($setting);

        // Mail.
        $name = 'theme_stablez/mail';
        $title = get_string('mail', 'theme_stablez');
        $description = get_string('maildesc', 'theme_stablez');
        $default = 'stablez-theme@stableztheme.com';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $general_tab->add($setting);

        $name = 'theme_stablez/other_contact_info';
        $title = 'Other Contact Information';
        $description = '';
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description,  $default);
        $general_tab->add($setting);

        // Website.
        $name = 'theme_stablez/website';
        $title = get_string('website', 'theme_stablez');
        $description = get_string('websitedesc', 'theme_stablez');
        $default = 'stablez-theme-site-url.com';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $general_tab->add($setting);

        // Facebook url setting.
        $name = 'theme_stablez/facebook';
        $title = get_string('facebook', 'theme_stablez');
        $description = get_string('facebookdesc', 'theme_stablez');
        $setting = new admin_setting_configtext($name, $title, $description, 'http://www.facebook.com');
        $general_tab->add($setting);

        // Twitter url setting.
        $name = 'theme_stablez/twitter';
        $title = get_string('twitter', 'theme_stablez');
        $description = get_string('twitterdesc', 'theme_stablez');
        $setting = new admin_setting_configtext($name, $title, $description, 'http://www.twitter.com');
        $general_tab->add($setting);

        // Instagram url setting.
        $name = 'theme_stablez/instagram';
        $title = get_string('instagram', 'theme_stablez');
        $description = get_string('instagramdesc', 'theme_stablez');
        $setting = new admin_setting_configtext($name, $title, $description, 'https://www.instagram.com');
        $general_tab->add($setting);

        // Linkdin url setting.
        $name = 'theme_stablez/linkedin';
        $title = get_string('linkedin', 'theme_stablez');
        $description = get_string('linkedindesc', 'theme_stablez');
        $setting = new admin_setting_configtext($name, $title, $description, 'http://www.linkedin.com');
        $general_tab->add($setting);



        // Must add the page after definiting all the settings!
        $settings->add($general_tab);
    }

    /*
    * -----------------------
    * Frontpage settings tab
    * -----------------------
    */
    public static function frontpage_setting($settings) {

        $frontpage_tab = new admin_settingpage('frontpage_setting_tab', get_string('frontpage', 'theme_stablez'));

        $name = 'theme_stablez/frontpagesettings_more_setting';
        $heading = '';
        $information = '+ For Default Front Page Setting Go To <a href="/admin/settings.php?section=frontpagesettings"> Site home settings </a> ';
        $setting = new admin_setting_heading($name, $heading, $information);
        $frontpage_tab->add($setting);

        $name = 'theme_stablez/login_card_popup';
        $title = "Enable login card Popup";
        $description = '';
        $default = 0;
        $choices = array(0 => 'Disable', 1 => 'Enable');
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $frontpage_tab->add($setting);

        // =========================================================
        $name = 'theme_stablez/hero_banner_settings';
        $heading = 'Hero Banner';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $frontpage_tab->add($setting);


        $name = 'theme_stablez/hero_banner';
        $title = 'Hero Banner';
        $description = 'Show hero banner in home/front page. After enable you can set the values.';
        $default = 0;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        // $setting = new admin_setting_configselect($name, $title, $description, $default, [0 => 'Moodle Default', 1 => 'Hero banner section']);
        $frontpage_tab->add($setting);
        // 
        $hero_banner = get_config('theme_stablez', 'hero_banner');
        if ($hero_banner) {

            $name = 'theme_stablez/hero_banner_login_card';
            $title = "Show login card";
            $description = 'This will show login form card in the hero banner section';
            $default = 0;
            $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
            $frontpage_tab->add($setting);

            $name = 'theme_stablez/hero_banner_slider';
            $title = "Enable Hero Banner Slider";
            $description = 'This will replace the hero banner in to hero banner slider';
            $default = 0;
            $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
            $frontpage_tab->add($setting);
            // 
            $hero_banner_slider = get_config('theme_stablez', 'hero_banner_slider');
            if ($hero_banner_slider == '1') {
                $name = 'theme_stablez/hero_banner_slider_number';
                $title = "Banner Slider Number ";
                $description = "This define the number of banner slider.";
                $default = 1;
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $frontpage_tab->add($setting);
            }
            // 
            $hero_banner_slider_number = (int)get_config('theme_stablez', 'hero_banner_slider_number');
            if ($hero_banner_slider == '1' && $hero_banner_slider_number > 0) {
                for ($i = 1; $i <= $hero_banner_slider_number; $i++) {

                    $setting = new admin_setting_heading('hero_banner_slider_number_separator' . $i, '', '<hr>');
                    $frontpage_tab->add($setting);

                    $name = 'theme_stablez/banner_title_' . $i;
                    $title = "Hero Banner Slider Title " . $i;
                    $description = 'Site name will be used if this is empty.';
                    $default = '';
                    $setting = new admin_setting_configtextarea($name, $title, $description, $default, $paramtype = PARAM_RAW, $cols = '60', $rows = '3');
                    $frontpage_tab->add($setting);

                    $filearea = 'banner_image_' . $i;
                    $name = 'theme_stablez/' . $filearea;
                    $title = "Hero Banner Slider image " . $i;
                    $description = '';
                    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.tiff', '.svg'), 'maxfiles' => 1);
                    $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $opts);
                    $frontpage_tab->add($setting);
                }
            } else {
                $name = 'theme_stablez/banner_title_1';
                $title = "Hero Banner Title";
                $description = 'Site name will be used if this is empty.';
                $default = '';
                $setting = new admin_setting_configtextarea($name, $title, $description, $default, $paramtype = PARAM_RAW, $cols = '60', $rows = '3');
                $frontpage_tab->add($setting);

                $name = 'theme_stablez/banner_description_1';
                $title = "Hero Banner Description";
                $description = '';
                $default = '';
                $setting = new admin_setting_confightmleditor($name, $title, $description,  $default);
                $frontpage_tab->add($setting);

                $filearea = 'banner_image_1';
                $name = 'theme_stablez/' . $filearea;
                $title = "Hero Banner image";
                $description = '';
                // $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.tiff', '.svg'), 'maxfiles' => 1);
                $opts = array('accepted_types' => array('image'), 'maxfiles' => 1);
                $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $opts);
                $frontpage_tab->add($setting);
            }

            $name = 'theme_stablez/banner_cta_count';
            $title = "Number of CTA Button ";
            $description = "This define the number of banner CTA buttons.";
            $default = 1;
            $setting = new admin_setting_configtext($name, $title, $description, $default);
            $frontpage_tab->add($setting);
            // 
            $banner_cta_count = (int)get_config('theme_stablez', 'banner_cta_count');
            if ($banner_cta_count > 0) {
                for ($i = 1; $i <= $banner_cta_count; $i++) {

                    $setting = new admin_setting_heading('banner_cta_count_separator' . $i, '', '<hr>');
                    $frontpage_tab->add($setting);

                    $name = 'theme_stablez/banner_cta_label_' . $i;
                    $title = "Banner CTA Button Label " . $i;
                    $description = '';
                    $default = '';
                    $setting = new admin_setting_configtext($name, $title, $description, $default);
                    $frontpage_tab->add($setting);

                    $name = 'theme_stablez/banner_cta_link_' . $i;
                    $title = "Banner CTA Button Link " . $i;
                    $description = '';
                    $default = '';
                    $setting = new admin_setting_configtext($name, $title, $description, $default);
                    $frontpage_tab->add($setting);

                    $name = 'theme_stablez/banner_cta_type_' . $i;
                    $title = 'CTA Button Type ' . $i;
                    $description = 'This will change the style of the CTA button. If you want to change the style of specific button, you can use the custom CSS code at Raw SCSS.';
                    $default = 'default';
                    $choices = [
                        'primary' => 'Primary Button Layout', // add stablez-primary-btn
                        'outline-primary' => 'Outline Primary Button Layout', // add stablez-course-card css class
                    ];
                    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
                    $frontpage_tab->add($setting);
                }
            }
        }


        /**
         * -------------------- Setting heading :: Banner Popup Settings --------------------
         */
        $name = 'theme_stablez/frontpage_banner_popup_setting';
        $heading = 'Banner Popup';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $frontpage_tab->add($setting);

        $name = 'theme_stablez/banner_popup_enable';
        $title = "Enable Banner Popup";
        $description = 'Show banner popup in home/front page. After enable you can set the values.';
        $default = 0;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $frontpage_tab->add($setting);
        // 
        $banner_popup_enable = get_config('theme_stablez', 'banner_popup_enable');
        if ($banner_popup_enable) {
            $name = 'theme_stablez/banner_popup_link';
            $title = 'Banner Popup Link';
            $description = '';
            $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
            $frontpage_tab->add($setting);

            $filearea = 'banner_popup_image';
            $name = 'theme_stablez/' . $filearea;
            $title = "Banner Popup Image";
            $description = '';
            $opts = array('accepted_types' => ['image'], 'maxfiles' => 1);
            $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $opts);
            $frontpage_tab->add($setting);
        }

        /**
         * 
         * 
         */
        $name = 'theme_stablez/start_guideline';
        $heading = 'Start Guideline';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $frontpage_tab->add($setting);

        $name = 'theme_stablez/start_guideline_item_count';
        $title = 'Start guideline item count ';
        $description = 'Select how many item you want to add <strong>then click SAVE</strong> to load the input fields.';
        $default = 3;
        $options = array();
        for ($i = 0; $i < 10; $i++) {
            $options[$i] = $i;
        }
        $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        $frontpage_tab->add($setting);

        // If we don't have an slide yet, default to the preset.
        $start_guideline_item_count = get_config('theme_stablez', 'start_guideline_item_count');
        for ($i = 1; $i <= $start_guideline_item_count; $i++) {

            $setting = new admin_setting_heading('start_guideline_item_count_separator_' . $i, '', '<hr>');
            $frontpage_tab->add($setting);

            $fileid = 'start_guideline_image_' . $i;
            $name = 'theme_stablez/' . $fileid;
            $title = 'Enter Image Icon ' . $i;
            $description = '';
            $opts = array('accepted_types' => ['image'], 'maxfiles' => 1);
            $setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
            $frontpage_tab->add($setting);

            $name = 'theme_stablez/start_guideline_title_' . $i;
            $title = 'Enter start guideline title ' . $i;
            $description = '';
            $default = '';
            $setting = new admin_setting_configtext($name, $title, $description, $default);
            $frontpage_tab->add($setting);

            $name = 'theme_stablez/start_guideline_desc_' . $i;
            $title = 'Enter start guideline description ' . $i;
            $description = 'start guideline description';
            $default = "";
            $setting = new admin_setting_configtextarea($name, $title, $description, $default, $paramtype = PARAM_RAW, $cols = '60', $rows = '4');
            $frontpage_tab->add($setting);
        }

        // Must add the page after definiting all the settings!
        $settings->add($frontpage_tab);
    }

    /*
    * --------------------
    * Courses settings tab
    * --------------------
    */
    public static function courses_settings($settings) {
        $courses_tab = new admin_settingpage('courses_settings_tab', 'Courses');
        /**
         * --------------------  --------------------
         */
        $name = 'theme_stablez/stablez_course_layout_settings';
        $heading = 'Courses General';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $courses_tab->add($setting);

        $name = 'theme_stablez/courses_view';
        $title = 'Course view';
        $description = 'This will change the coueses layout.';
        $default = 'default';
        $choices = [
            'default' => 'Default Layout',
            'card' => 'Card Layout', // add stablez-course-card css class
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $courses_tab->add($setting);

        $name = 'theme_stablez/available_courses';
        $title = 'Available courses';
        $description = 'This will change home page available coueses section layout.';
        $default = 'default';
        $choices = [
            'default' => 'Default Layout',
            'hide' => 'Hide Layout',
            // 'slider' => 'Slider Layout',
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $courses_tab->add($setting);

        $name = 'frontpagecourselimit';
        $title = "Maximum number of courses ";
        $description = "This define the maximum number of courses in frontpage available course section.";
        $default = 9;
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $courses_tab->add($setting);

        $name = 'theme_stablez/stablez_activity_navigation';
        $title = 'Course Activity Navigation ';
        $description = 'This will show or hide course activity navigation in single activity "mod" page. ';
        $default = 0;
        $choices = array(0 => 'Default', 1 => 'Always show', 2 => 'Always hide');
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $courses_tab->add($setting);

        $name = 'theme_stablez/collapse_course_topics';
        $title = 'Collapse Course Topics';
        $description = 'If enabled, all course topics will be collapsed by default when viewing all sections on a single course page for topic format.';
        $default = 0;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $courses_tab->add($setting);

        $name = 'theme_stablez/close_course_index';
        $title = 'Close Course Index';
        $description = 'If enabled, the course index drawer will be collapsed by default on page load.';
        $default = 0;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $courses_tab->add($setting);

        $name = 'theme_stablez/expand_course_category';
        $title = 'Expand course category';
        $description = 'If enabled, course categories will be expanded by default when viewing the course index page.';
        $default = 1;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $courses_tab->add($setting);

        // Must add the page after definiting all the settings!
        $settings->add($courses_tab);
    }


    /*
    * --------------------
    * Footer settings tab
    * --------------------
    */
    public static function footer_settings($settings) {
        $footer_tab = new admin_settingpage('footer_settings_tab', get_string('footer', 'theme_stablez'));

        $name = 'theme_stablez/footer_general';
        $heading = 'Footer General Setting';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $footer_tab->add($setting);

        $name = 'theme_stablez/copyright';
        $title = get_string('copyright', 'theme_stablez');
        $description = get_string('copyrightdesc', 'theme_stablez');
        $default = 'Copyright © {year} stablez.';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $footer_tab->add($setting);

        $name = 'theme_stablez/footer_description';
        $title = 'Footer short descriptions';
        $description = '';
        $default = 'Our vision is to provide convenience and help increase your sales business.';
        $setting = new admin_setting_configtextarea($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $footer_tab->add($setting);


        $name = 'theme_stablez/footer_contact_section';
        $title = 'Contact details to footer';
        $description = 'Add contact details column section in footer. Contact Detail data is managed through stablez Theme Settings > General > Contact Detail';
        $default = 1;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $footer_tab->add($setting);

        // 
        $footer_contact_section = get_config('theme_stablez', 'footer_contact_section');
        if ($footer_contact_section == '1') {
            $name = 'theme_stablez/footer_contact_section_label';
            $title = "Footer Contact Section Label";
            $description = "";
            $default = 'Contact Detail';
            $setting = new admin_setting_configtext($name, $title, $description, $default);
            $footer_tab->add($setting);
        }

        // ------------------------------------------------------------------------------------------

        $name = 'theme_stablez/footer_menu';
        $heading = 'Footer Menu';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $footer_tab->add($setting);

        $name = 'theme_stablez/footer_menu_number';
        $title = "Footer Menu Number ";
        $description = "This define the number of footer menu.";
        $default = 2;
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $footer_tab->add($setting);
        // 
        $footer_menu_number = (int)get_config('theme_stablez', 'footer_menu_number');
        if ($footer_menu_number > 0) {
            for ($i = 1; $i <= $footer_menu_number; $i++) {

                $setting = new admin_setting_heading('footer_menu_number_separator' . $i, '', '<hr>');
                $footer_tab->add($setting);

                $name = 'theme_stablez/footer_menu_label_' . $i;
                $title = "Footer Menu Label " . $i;
                $description = "";
                $default = 'Quick Link';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $footer_tab->add($setting);

                $name = 'theme_stablez/footer_menu_items_' . $i;
                $title = 'Footer Menu Items';
                $description = 'Each menu item should be defined on a new line. Additionally, the item title and link must be separated by "|". For Example: <br>Course|/course/index.php<br>FAQs|https://moodle-site.com/faq<br>';
                $default = "Course|/course/index.php\nFAQs|https://moodle-site.com/faq";
                $setting = new admin_setting_configtextarea($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $footer_tab->add($setting);
            }
        }


        // ------------------------------------------------------------------------------------------

        $settings->add($footer_tab);
    }

    /*
    * --------------------
    * Login Signup settings tab
    * --------------------
    */
    public static function login_signup_settings($settings) {
        $login_signup_tab = new admin_settingpage('login_signup_settings_tab', "Login Signup");

        $name = 'theme_stablez/login_signup_general';
        $heading = 'General Setting';
        $information = '+ For Default <a href="/admin/settings.php?section=loginsettings"> Login settings </a> ';
        $setting = new admin_setting_heading($name, $heading, $information);
        $login_signup_tab->add($setting);

        // Login Background image setting.
        $name = 'theme_stablez/loginbackgroundimage';
        $title = get_string('loginbackgroundimage', 'theme_boost');
        $description = get_string('loginbackgroundimage_desc', 'theme_boost');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackgroundimage');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $login_signup_tab->add($setting);

        // ------------------------------------------------------------------------------------------
        $settings->add($login_signup_tab);
    }

    /**
     * stablez_advance_settings
     */
    public static function stablez_advance_settings($settings) {

        $stablez_advance_feature_tab = new admin_settingpage('stablez_advance_feature_setting_tab', 'More');

        /**
         * -------------------- Setting heading :: stablez Feature --------------------
         */

        $name = 'theme_stablez/stablez_features';
        $heading = ' Advance Feature';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $stablez_advance_feature_tab->add($setting);

        // $name = 'theme_stablez/stablez_timetrack';
        // $title = 'Enable Time Track ';
        // $description = 'Enable or Disable stablez Time Track in course activity and view <a href="/theme/stablez/page/report/timetrack.php"> Course Time Track Report </a>';
        // $default = 0;
        // $choices = array(0 => 'Disable', 1 => 'Enable');
        // $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        // $stablez_advance_feature_tab->add($setting);

        // check for local_adminer
        if (\core_plugin_manager::instance()->get_plugin_info('local_adminer')) {
            $name = 'theme_stablez/stablez_adminer_secret';
            $title = 'Adminer Plugin Secret Key';
            $description = 'This key is used to unlock Server > <a href="/local/adminer/index.php">Moodle Adminer</a>. You need to have <a href="https://moodle.org/plugins/local_adminer" target="_blank">Moodle Adminer Plugin </a> installed. ';
            $default = 'stablez@2025Adminer';
            // $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT, 50);
            $setting = new admin_setting_configpasswordunmask($name, $title, $description, $default, PARAM_TEXT, 50);
            $stablez_advance_feature_tab->add($setting);
        }

        // check local_stablezhelpers and google_translate_handler
        if (
            \core_plugin_manager::instance()->get_plugin_info('local_stablezhelpers') &&
            class_exists('\local_stablezhelpers\local\handler\google_translate_handler')
        ) {
            $lang_list = \get_string_manager()->get_list_of_translations();
            if (count($lang_list) > 1) {
                $name = 'theme_stablez/google_translate';
                $title = "Enable google translate";
                $description = 'This will translated any content that are not covered by the language packs using Google Translate when the language is changed.<br>First, multi-language support <a href="/admin/settings.php?section=langsettings">setting</a> must be enabled on the site by importing <a href="/admin/tool/langimport/index.php">language packs</a>.<br><br>Additionally, Recommend you to use <a href="https://moodle.org/plugins/filter_translations">Filters ::: filter_translations</a> plugin to translate content, after intalling you can enable through manage <a href="/admin/filters.php">filter setting</a>. Also read <a href="https://docs.moodle.org/405/en/Content_translation_plugin_set">https://docs.moodle.org/405/en/Content_translation_plugin_set</a> ';
                $default = 0;
                $choices = array(0 => 'No', 1 => 'Yes');
                $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
                $stablez_advance_feature_tab->add($setting);
            }
        }

        // Must add the page after definiting all the settings!
        $settings->add($stablez_advance_feature_tab);
    }

    /**
     * style_script_settings   
     * 
     */
    public static function style_script_settings($settings) {
        global $CFG;
        $style_script_tab = new admin_settingpage('style_script_settings_tab', 'Style and Script');

        /**
         * -------------------- Setting heading --------------------
         */
        // $setting = new admin_setting_heading('style_script_settings_style_script_hr', '', '<hr>');
        // $style_script_tab->add($setting);

        $name = 'theme_stablez/layout_style';
        $heading = 'Layout Style';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $style_script_tab->add($setting);

        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_stablez/brandcolor';
        $title = "Primary Brand Color";
        $description = "The value is present as '\$primary' variable. This will change the primary brand color of the site which is used in various elements like links, buttons, etc. If you want to change the color of specific element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#2f5d88'); // 
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);
      
        $name = 'theme_stablez/backgroundcolor';
        $title = "Primary background Color";
        $description = "The value is present as '\$background' variable. This will change the background color of the site. If you want to change the color of specific element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#eef5f9'); // 
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);
      
        $name = 'theme_stablez/headerbgcolor';
        $title = "Header background Color";
        $description = "The value is present as '\$headerbgcolor' variable. This will change the header bg color color of the site. If you want to change the color of specific element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#2f5d88'); // 
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);

        // ==========================================================================================

        $name = 'theme_stablez/button_layout_style';
        $heading = 'Buttons Layout Style';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $style_script_tab->add($setting);

        $name = 'theme_stablez/primarybuttoncolor';
        $title = "Primary Button Color";
        $description = "The value is present as '\$primary-btn' variable. This will change the color of all the primary buttons in the site. If you want to change the color of specific button or element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#2f5d88');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);

        $name = 'theme_stablez/primarybuttonhovercolor';
        $title = "Primary Button Hover Color";
        $description = "The value is present as '\$primary-btn-hover' variable. This will change the hover color of all the primary buttons in the site. If you want to change the color of specific button or element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#2f5d88');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);

        $name = 'theme_stablez/secondarybuttoncolor';
        $title = "Secondary Button Color";
        $description = "The value is present as '\$secondary-btn' variable. This will change the color of all the secondary buttons in the site. If you want to change the color of specific button or element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#ced4da');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);

        $name = 'theme_stablez/secondarybuttonhovercolor';
        $title = "Secondary Button Hover Color";
        $description = "The value is present as '\$secondary-btn-hover' variable. This will change the hover color of all the secondary buttons in the site. If you want to change the color of specific button or element, you can use the custom CSS code section below.";
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#d5dae0');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);

        // ==========================================================================================

        $name = 'theme_stablez/style_script';
        $heading = 'SCSS and JavaScript code';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $style_script_tab->add($setting);

        // Raw SCSS to include after the content.
        $setting = new admin_setting_scsscode(
            'theme_stablez/scss',
            get_string('rawscss', 'theme_boost'),
            get_string('rawscss_desc', 'theme_boost'),
            '',
            PARAM_RAW
        );
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);

        // Custom JavaScript
        $setting = new admin_setting_configtextarea(
            'theme_stablez/custom_js',
            'Custom JavaScript',
            '',
            '',
            PARAM_RAW
        );
        $setting->set_updatedcallback('theme_reset_all_caches');
        $style_script_tab->add($setting);


        // Must add the page after definiting all the settings!
        $settings->add($style_script_tab);
    }
    // 
}
