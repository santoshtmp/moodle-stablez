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
 * @package   theme_stablez
 * @copyright 2025 stablez
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE;
if (optional_param('section', '', PARAM_TEXT) == 'themesettingstablez') {
    if ($PAGE->pagelayout === 'admin' &&  $PAGE->pagetype === 'admin-setting-themesettingstablez') {
        // CodeMirror core & extras
        $PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.css'));
        $PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/theme/material-palenight.min.css'));

        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.js'), true);
        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/javascript/javascript.min.js'), true);
        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/css/css.min.js'), true);
        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/addon/display/autorefresh.min.js'), true);

        // theme css and js
        $PAGE->requires->css('/theme/stablez/style/admin-themesetting.css');
        $PAGE->requires->js_call_amd('theme_stablez/admin-themesetting', 'init', [
            'stablez',
            ['number_fields' => ['hero_banner_slider_number', 'footer_menu_number', 'banner_cta_count']]
        ]);
    }
}

// Ensure only admins see this
if ($hassiteconfig) {
    //Create the top-level category: "stablez"
    // $ADMIN->add('root', new admin_category('stablezadmin', get_string('pluginname', 'theme_stablez')));
    $ADMIN->add('appearance', new admin_category('stablezadmin', get_string('pluginname', 'theme_stablez')));
    
    // Create a subcategory inside "stablez" (e.g., "General Settings")
    // $ADMIN->add('stablezadmin', new admin_category('stablezadmin_general', get_string('pluginname', 'theme_stablez')));

    /**
     * stablez Setings
     */
    $ADMIN->add('stablezadmin', new admin_externalpage(
        'stablezadmin_themesettingstablez', // Unique identifier
        get_string('generalsettings', 'theme_stablez'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingstablez') // External URL
    ));

    /**
     * Contact Detail
     */
    $ADMIN->add('stablezadmin', new admin_externalpage(
        'stablezadmin_front_page', // Unique identifier
        get_string('frontpage', 'theme_stablez') . ' ' . get_string('settings', 'theme_stablez'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingstablez#frontpage_setting_tab') // External URL
    ));

    /**
     * Contact Detail
     */
    $ADMIN->add('stablezadmin', new admin_externalpage(
        'stablezadmin_contact_detail', // Unique identifier
        get_string('contact_detail', 'theme_stablez'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingstablez&tabtitle=theme-stablez-contact-detail#general_setting_tab') // External URL
    ));

    /**
     * Start Guideline
     */
    $ADMIN->add('stablezadmin', new admin_externalpage(
        'stablezadmin_start_guideline', // Unique identifier
        get_string('start_guideline', 'theme_stablez'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingstablez&tabtitle=theme-stablez-start-guideline#frontpage_setting_tab') // External URL
    ));

    /**
     * Footer Settings
     */
    $ADMIN->add('stablezadmin', new admin_externalpage(
        'stablezadmin_footer', // Unique identifier
        get_string('footer', 'theme_stablez') . " " . get_string('settings', 'theme_stablez'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingstablez&tabtitle=theme-stablez-footer#footer_settings_tab') // External URL
    ));

    /**
     * Custom Content Management
     */
    if (
        \core_plugin_manager::instance()->get_plugin_info('local_stablezhelpers') &&
        file_exists($CFG->dirroot . '/local/stablezhelpers/content/index.php')
    ) {
        $ADMIN->add('stablezadmin', new admin_externalpage(
            'stablez_custom_content_page', // Unique identifier
            get_string('contentmanagement', 'local_stablezhelpers'), // Link name
            new moodle_url('/local/stablezhelpers/content/index.php') // External URL
        ));
    }

    /**
     *
     */
}

/**
 * Theme Settings
 */
if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingstablez', get_string('configtitle', 'theme_stablez'));
    \theme_stablez\form\theme_settings::general_setting($settings);
    \theme_stablez\form\theme_settings::frontpage_setting($settings);
    \theme_stablez\form\theme_settings::courses_settings($settings);
    \theme_stablez\form\theme_settings::footer_settings($settings);
    \theme_stablez\form\theme_settings::login_signup_settings($settings);
    \theme_stablez\form\theme_settings::style_script_settings($settings);
    \theme_stablez\form\theme_settings::stablez_advance_settings($settings);
}
