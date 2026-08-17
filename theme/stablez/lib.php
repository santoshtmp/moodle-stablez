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

use core\output\theme_config;
use theme_stablez\local\service\theme_settings_service;

defined('MOODLE_INTERNAL') || die();


/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_stablez_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea) {
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        //

        $stablez_theme_filearea = ['logo', 'loginbackgroundimage', 'favicon', 'banner_popup_image'];
        if (
            in_array($filearea, $stablez_theme_filearea) ||
            (preg_match("/^banner_image_[1-9][0-9]?$/", $filearea)) ||
            (preg_match("/^start_guideline_image_[1-9][0-9]?$/", $filearea))
        ) {
            $theme = theme_config::load('stablez');
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else {
            $component = 'theme_stablez';
            $fs = get_file_storage();
            $filename = array_pop($args);
            $filepath = '/'; // $args ? '/' . implode('/', $args) . '/' : '/';
            $files = $fs->get_area_files($context->id, $component, $filearea, $args[0], 'timemodified', false);
            if ($files) {
                $file = reset($files); // Get the first file
            } else {
                $file = $fs->get_file($context->id, $component, $filearea, $args[0], $filepath, $filename);
            }
            if ($file && !$file->is_directory()) {
                // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
                // do not lower it because the files are dispalyed very often.
                \core\session\manager::write_close();
                return send_stored_file($file, null, 0, $forcedownload, $options);
            }

            $parent_theme = theme_config::load('boost');
            return $parent_theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }
    }
    // 
    send_file_not_found();
}

/**
 * Get main scss
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_stablez_get_main_scss_content($theme) {
    global $CFG;
    $scss = '';
    $theme_boost = theme_config::load('boost');
    $scss .= theme_boost_get_main_scss_content($theme_boost);
    $scss .= file_get_contents($CFG->dirroot . '/theme/stablez/scss/main.scss');
    return $scss;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_stablez_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['primary'],
        'backgroundcolor' => ['background'],
        'headerbgcolor' => ['headerbgcolor'],
        'primarybuttoncolor' => ['primary-btn'],
        'primarybuttonhovercolor' => ['primary-btn-hover'],
        'secondarybuttoncolor' => ['secondary-btn'],
        'secondarybuttonhovercolor' => ['secondary-btn-hover']
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            $value = '#012757';
        }
        array_map(function ($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Add a new variable to indicate that we are running behat.
    if (defined('BEHAT_SITE_RUNNING')) {
        $scss .= "\$behatsite: true;\n";
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre ?? '')) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}

/**
 * Get extra scss
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_stablez_get_extra_scss($theme) {

    $content = '';
    $imageurl = $theme->setting_file_url('backgroundimage', 'backgroundimage');

    // Sets the background image, and its settings.
    if (!empty($imageurl)) {
        $content .= '@media (min-width: 768px) {';
        $content .= 'body { ';
        $content .= "background-image: url('$imageurl'); background-size: cover;";
        $content .= ' } }';
    }

    // Sets the login background image.
    $loginbackgroundimageurl = $theme->setting_file_url('loginbackgroundimage', 'loginbackgroundimage');
    $backgroundposition = '';
    $isdefaultloginimage = empty($loginbackgroundimageurl);
    if ($isdefaultloginimage) {
        // Use the default login background image.
        $loginbackgroundimageurl = $theme->image_url(
            'login_background',
            'theme',
        );
        // Set the default background position to center.
        $backgroundposition = 'background-position: center;';
    }
    $content .= 'body.pagelayout-login #page .login-layout-left { ';
    $content .= "background-image: url('$loginbackgroundimageurl'); ";
    $content .= "background-size: cover; {$backgroundposition} position: relative;";
    $content .= ' }';

    // Add a watermark to indicate the image is AI generated, but only for the default image.
    if ($isdefaultloginimage) {
        $content .= 'body.pagelayout-login #page .login-layout-left::after {';
        // Escape the label for use in a CSS string value: collapse newlines (which would break the CSS string)
        // and escape single quotes and backslashes via addcslashes.
        $ailabel = preg_replace('/[\r\n]+/', ' ', get_string('aigeneratedimage', 'theme_boost'));
        $content .= " content: '" . addcslashes($ailabel, "'\\") . "';";
        $content .= ' position: absolute; bottom: 1rem; right: 1rem;';
        $content .= ' color: $white;';
        $content .= ' font-size: 0.8rem;';
        $content .= ' text-shadow: 0 1px 2px $black;';
        $content .= ' pointer-events: none;';
        $content .= ' }';
    }

    // Add a background color
    $backgroundcolor = $theme->settings->backgroundcolor ?? '';
    if ($backgroundcolor) {
        $content .= 'body { ';
        $content .= "background-color: $backgroundcolor;";
        $content .= ' }';
    }
    $extra_scss = $theme->settings->scss ?? '';
    return $content . " \n " . $extra_scss;
}


/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_stablez_get_precompiled_css() {
    global $CFG;
    $precompiled_css = '';
    $precompiled_css .= file_get_contents($CFG->dirroot . '/theme/stablez/style/moodle.css');
    return $precompiled_css;
}

/**
 * Get theme setting custom js
 */
function theme_stablez_get_custom_js() {
    $custom_js = '';
    if (get_config('theme_stablez', 'custom_js')) {
        $custom_js = '<script type="text/javascript" >' . get_config('theme_stablez', 'custom_js') . '</script>';
    }
    return $custom_js;
}

/**
 * Get the current user preferences that are available
 *
 * @return array[]
 */
function theme_stablez_user_preferences(): array {
    return [
        'drawer-open-block' => [
            'type' => PARAM_BOOL,
            'null' => NULL_NOT_ALLOWED,
            'default' => false,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
        'drawer-open-index' => [
            'type' => PARAM_BOOL,
            'null' => NULL_NOT_ALLOWED,
            'default' => true,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
    ];
}

/**
 * Add custom body classes based on content view page.
 *
 * @param moodle_page $page
 */
function theme_stablez_page_init(moodle_page $page) {

    // Only run on content view page
    if ($page->pagetype === 'content-view') {

        $id = $page->subpage; // You already set this: $PAGE->set_subpage((string)$id);

        if ($id == '2' || $id == '1') {
            // $page->add_body_class('hide-fullheader');
        }
    }
}


/**
 * Modify content view template data.
 *
 * @param array $templatecontent
 * @param \stdClass $content
 * @param int $id
 */
function theme_stablez_local_stablezhelpers_modify_content_view_template(array &$templatecontent, \stdClass $content, int $id) {
    if ($id == 2) {
        $templatecontent = array_merge($templatecontent, theme_settings_service::get_instance()->contact_details_settings());
    }
}
