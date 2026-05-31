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
 * stablez
 * @package   theme_stablez
 * @copyright 2025 stablez
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_stablez\local\service\theme_settings_service;

defined('MOODLE_INTERNAL') || die();

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();
$addblockbutton_abovecontent = $OUTPUT->addblockbutton('above-content');
$addblockbutton_belowcontent = $OUTPUT->addblockbutton('below-content');
$addblockbutton_admincontent = $OUTPUT->addblockbutton('admin-content');
// 
$blockshtml = $OUTPUT->blocks('side-pre');
$abovecontentblockHTML = $OUTPUT->blocks('above-content');
$belowcontentblockHTML = $OUTPUT->blocks('below-content');
$admincontentblockHTML = $OUTPUT->blocks('admin-content');
// 
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
$hasabovecontentblock = (strpos($abovecontentblockHTML, 'data-block=') !== false || !empty($addblockbutton_abovecontent));
$hasbelowcontentblock = (strpos($belowcontentblockHTML, 'data-block=') !== false || !empty($addblockbutton_belowcontent));
$hasadmincontentblock = (strpos($admincontentblockHTML, 'data-block=') !== false || !empty($addblockbutton_admincontent));


if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}
$close_course_index = get_config('theme_stablez', 'close_course_index') ?? '0';
if ($close_course_index) {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING') && get_user_preferences('behat_keep_drawer_closed') != 1) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

if (!$hasblocks) {
    $blockdraweropen = false;
}

$courseindex = false;
if (function_exists('core_course_drawer')) {
    $courseindex = core_course_drawer();
    if (!$courseindex) {
        $courseindexopen = false;
    }
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $selectmenu = new \core\output\select_menu(
            'tertiarynavigation',
            $overflowdata->urls,
            $overflowdata->selected,
        );
        $selectmenu->set_label($overflowdata->label, $overflowdata->labelattributes);
        $overflow = $selectmenu->export_for_template($OUTPUT);
    }
}

$primary = new \core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$coursefullname = ($PAGE->course?->fullname) ? format_string(
    $PAGE->course->fullname,
    true,
    ['context' => context_course::instance($PAGE->course->id), 'escape' => false],
) : '';
$courseurl = $PAGE->course ? new \core\url('/course/view.php', ['id' => $PAGE->course->id]) : null;

// stablez_block_region 
$stablez_block_region = false;
if (
    $PAGE->pagelayout == 'frontpage' ||
    ($PAGE->pagelayout == 'course' &&  ($PAGE->url->get_path(false) != '/course/section.php')) ||
    $PAGE->pagelayout == 'custompages'
) {
    $stablez_block_region = true;
}
//
global $templatecontext;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => \context_course::instance(SITEID), "escape" => false]),
    'coursefullname' => $coursefullname,
    'courseurl' => $courseurl ? $courseurl->out(false) : null,
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    // block variables
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'forceblockdraweropen' => $forceblockdraweropen,
    'addblockbutton' => $addblockbutton,
    'addblockbutton_abovecontent' => $addblockbutton_abovecontent,
    'addblockbutton_belowcontent' => $addblockbutton_belowcontent,
    'addblockbutton_admincontent' => $addblockbutton_admincontent,
    'sidepreblocks' => $blockshtml,
    'abovecontentblockHTML' => $abovecontentblockHTML,
    'belowcontentblockHTML' => $belowcontentblockHTML,
    'admincontentblockHTML' => $admincontentblockHTML,
    'hasblocks' => $hasblocks,
    'hasabovecontentblock' => $hasabovecontentblock,
    'hasbelowcontentblock' => $hasbelowcontentblock,
    'hasadmincontentblock' => $hasadmincontentblock,
    'stablez_block_region' => $stablez_block_region,
    // menu
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    // 
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    // 
    'is_admin_role' => is_siteadmin(),
    'is_edit_mode' => $PAGE->user_is_editing(),
    'contacstettings' => theme_settings_service::get_instance()->contact_details_settings(),
    'footersettings' => theme_settings_service::get_instance()->footer_settings(),
];
// Additional
$templatecontext['islogged_in'] = (isloggedin() && !isguestuser()) ? true : false;
if (!$templatecontext['islogged_in']) {
    $maintenance = '';
    if ($CFG->maintenance_enabled == true) {
        if (!empty($CFG->maintenance_message)) {
            $maintenance = $CFG->maintenance_message;
        } else {
            $maintenance = get_string('sitemaintenance', 'admin');
        }
    }
    $login_form_context = [
        'loginurl' => get_login_url(),
        'logintoken' => \core\session\manager::get_login_token(),
        'canloginasguest' => $CFG->guestloginbutton and !isguestuser(),
        'canloginbyemail' => !empty($CFG->authloginviaemail),
        'cansignup' => $CFG->registerauth == 'email' || !empty($CFG->registerauth),
        'maintenance' => format_text($maintenance, FORMAT_MOODLE)
    ];
    $templatecontext = array_merge($templatecontext, $login_form_context);
}

/**
 * Load js amd
 */
$pagelayout = $PAGE->pagelayout;
if ($pagelayout == 'coursecategory') {
    $expand_course_category = get_config('theme_stablez', 'expand_course_category') ?? '0';
    if ($expand_course_category == '1') {
        $PAGE->requires->js_call_amd(
            'theme_stablez/course/course',
            'coursecategory'
        );
    }
} else if ($pagelayout == 'course') {
    $collapse_course_topics = get_config('theme_stablez', 'collapse_course_topics') ?? '0';
    if ($collapse_course_topics == '1') {
        $PAGE->requires->js_call_amd(
            'theme_stablez/course/course',
            'coursecollapsetopic'
        );
    }
}
