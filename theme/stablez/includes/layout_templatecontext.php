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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme StableZ layout controller.
 *
 * Prepares template context data and selects appropriate Mustache layout
 * based on current page layout in Moodle.
 *
 * @package   theme_stablez
 * @copyright 2025 stablez
 * @author    santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_stablez\local\service\theme_settings_service;

defined('MOODLE_INTERNAL') || die();

/**
 * ==========================================================
 * BLOCKS: Render block regions and add-block buttons
 * ==========================================================
 */

// Add "Add block" buttons for different regions (editing mode).
$addblockbutton = $OUTPUT->addblockbutton();
$addblockbutton_abovecontent = $OUTPUT->addblockbutton('above-content');
$addblockbutton_belowcontent = $OUTPUT->addblockbutton('below-content');
$addblockbutton_admincontent = $OUTPUT->addblockbutton('admin-content');

// Render block HTML for each region.
$blockshtml = $OUTPUT->blocks('side-pre');
$abovecontentblockHTML = $OUTPUT->blocks('above-content');
$belowcontentblockHTML = $OUTPUT->blocks('below-content');
$admincontentblockHTML = $OUTPUT->blocks('admin-content');

// Detect whether blocks exist in each region.
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
$hasabovecontentblock = (strpos($abovecontentblockHTML, 'data-block=') !== false || !empty($addblockbutton_abovecontent));
$hasbelowcontentblock = (strpos($belowcontentblockHTML, 'data-block=') !== false || !empty($addblockbutton_belowcontent));
$hasadmincontentblock = (strpos($admincontentblockHTML, 'data-block=') !== false || !empty($addblockbutton_admincontent));

// $regions = [
//     'side-pre',
//     'above-content',
//     'below-content',
//     'admin-content'
// ];
// $regionsBlocks = [];
// foreach ($regions as $region) {
//     $addblockbutton = $OUTPUT->addblockbutton($region === 'side-pre' ? '' : $region);
//     $blockshtml = $OUTPUT->blocks($region);
//     $regionsBlocks[$region] = [
//         'addblockbutton' => $addblockbutton,
//         'hasblocks' => (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton)),
//         'blockshtml' => $blockshtml,
//     ];
// }

/**
 * ==========================================================
 * USER DRAWER STATE
 * ==========================================================
 */

// Determine drawer states for logged-in users.
if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

// Theme setting override: force close course index.
$close_course_index = get_config('theme_stablez', 'close_course_index') ?? '0';
if ($close_course_index) {
    $courseindexopen = false;
    $blockdraweropen = false;
}

// Behat testing override.
if (defined('BEHAT_SITE_RUNNING') && get_user_preferences('behat_keep_drawer_closed') != 1) {
    $blockdraweropen = true;
}

/**
 * ==========================================================
 * PAGE STRUCTURE SETTINGS
 * ==========================================================
 */

$extraclasses = ['uses-drawers'];

if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

// Disable block drawer if no blocks exist.
if (!$hasblocks) {
    $blockdraweropen = false;
}

// Course index drawer (if supported by core).
$courseindex = false;
if (function_exists('core_course_drawer')) {
    $courseindex = core_course_drawer();
    if (!$courseindex) {
        $courseindexopen = false;
    }
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

/**
 * ==========================================================
 * NAVIGATION: Secondary + Primary menus
 * ==========================================================
 */

$secondarynavigation = false;
$overflow = '';

if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();

    $moremenu = new \core\navigation\output\more_menu(
        $PAGE->secondarynav,
        'nav-tabs',
        true,
        $tablistnav
    );

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

// Primary navigation (main header menu).
$primary = new \core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

// Region main settings visibility.
$buildregionmainsettings =
    !$PAGE->include_region_main_settings_in_header_actions()
    && !$PAGE->has_secondary_navigation();

$regionmainsettingsmenu = $buildregionmainsettings
    ? $OUTPUT->region_main_settings_menu()
    : false;

// Activity header data.
$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

/**
 * ==========================================================
 * COURSE DATA
 * ==========================================================
 */

$coursefullname = ($PAGE->course?->fullname) ? format_string(
    $PAGE->course->fullname,
    true,
    ['context' => context_course::instance($PAGE->course->id), 'escape' => false],
) : '';
$courseurl = $PAGE->course ? new \core\url('/course/view.php', ['id' => $PAGE->course->id]) : null;

/**
 * ==========================================================
 * CUSTOM THEME BLOCK REGION FLAG
 * ==========================================================
 */

$stablez_block_region = false;
if (
    $PAGE->pagelayout == 'frontpage' ||
    ($PAGE->pagelayout == 'course' &&  ($PAGE->url->get_path(false) != '/course/section.php')) ||
    $PAGE->pagelayout == 'standard' ||
    $PAGE->pagelayout == 'custompages'
) {
    $stablez_block_region = true;
}

/**
 * ==========================================================
 * TEMPLATE CONTEXT (BASE)
 * ==========================================================
 */

$templatecontext = [
    'sitename' => format_string(
        $SITE->shortname,
        true,
        [
            'context' => \context_course::instance(SITEID),
            "escape" => false
        ]
    ),
    'coursefullname' => $coursefullname,
    'courseurl' => $courseurl ? $courseurl->out(false) : null,
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,

    // Blocks.
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

    // Navigation.
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],

    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,

    // Roles / state.
    'is_admin_role' => is_siteadmin(),
    'is_edit_mode' => $PAGE->user_is_editing(),

    // Theme settings service.
    'contacstettings' => theme_settings_service::get_instance()->contact_details_settings(),
    'footersettings' => theme_settings_service::get_instance()->footer_settings(),
];

/**
 * ==========================================================
 * LOGIN / GUEST CONTEXT EXTENSION
 * ==========================================================
 */

$templatecontext['islogged_in'] = (isloggedin() && !isguestuser());

if (!$templatecontext['islogged_in']) {

    // Maintenance message.
    $maintenance = '';
    if ($CFG->maintenance_enabled == true) {
        $maintenance = !empty($CFG->maintenance_message)
            ? $CFG->maintenance_message
            : get_string('sitemaintenance', 'admin');
    }

    $login_form_context = [
        'loginurl' => get_login_url(),
        'logintoken' => \core\session\manager::get_login_token(),
        'canloginasguest' => $CFG->guestloginbutton && !isguestuser(),
        'canloginbyemail' => !empty($CFG->authloginviaemail),
        'cansignup' => $CFG->registerauth == 'email' || !empty($CFG->registerauth),
        'maintenance' => format_text($maintenance, FORMAT_MOODLE)
    ];

    $templatecontext = array_merge($templatecontext, $login_form_context);
}

/**
 * ==========================================================
 * EXTRA ASSETS
 * ==========================================================
 */

// Google font include.
$PAGE->requires->css(
    new moodle_url('https://fonts.googleapis.com/css2?family=Baskervville&display=swap')
);

/**
 * ==========================================================
 * LAYOUT SWITCHING (TEMPLATE SELECTION)
 * ==========================================================
 */

$pagelayout = $PAGE->pagelayout;
$templatename = 'theme_stablez/layout/drawers';

switch ($pagelayout) {

    case 'coursecategory':
        if (get_config('theme_stablez', 'expand_course_category') == '1') {
            $PAGE->requires->js_call_amd(
                'theme_stablez/course/course',
                'coursecategory'
            );
        }
        $templatename = 'theme_stablez/layout/drawers';
        break;

    case 'course':
        if (get_config('theme_stablez', 'collapse_course_topics') == '1') {
            $PAGE->requires->js_call_amd(
                'theme_stablez/course/course',
                'coursecollapsetopic'
            );
        }
        $templatename = 'theme_stablez/layout/course';
        break;

    case 'incourse':
        // // Check if user is not login.
        // if (!isloggedin() || isguestuser()) {
        //     // User is either logged out or guest.
        //     $currenturl = qualified_me();

        //     // Moodle login URL with redirect back.
        //     $loginurl = new moodle_url('/login/index.php', [
        //         'wantsurl' => $currenturl
        //     ]);
        //     redirect($loginurl);
        // }

        $$templatename = 'theme_stablez/layout/incourse';
        break;

    case 'frontpage':
        $templatecontext = array_merge(
            $templatecontext,
            theme_settings_service::get_instance()->front_page_settings()
        );

        $templatename = 'theme_stablez/layout/frontpage';
        break;

    case 'custompages':
        $templatename = 'theme_stablez/layout/custompages';
        break;

    case 'login':
        $leftinstructions = !empty($CFG->auth_instructions)
            ? format_text($CFG->auth_instructions, FORMAT_MOODLE, [
                'context' => context_system::instance()
            ])
            : null;

        $templatecontext['leftinstructions'] = $leftinstructions;

        $items = $PAGE->navbar->get_items();
        if (!empty($items)) {
            $lastitem = end($items);
            $PAGE->set_heading($lastitem->text);
        }

        $templatename = 'theme_stablez/layout/login';
        break;

    default:
        $templatename = 'theme_stablez/layout/drawers';
}

/**
 * ==========================================================
 * RENDER OUTPUT
 * ==========================================================
 */

echo $OUTPUT->render_from_template($templatename, $templatecontext);
