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
 * stablez config.
 *
 * @package    theme_stablez
 * @copyright  2025 stablez
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

$THEME->name = 'stablez';
$THEME->parents = ['boost'];

$THEME->sheets = ["style"];
$THEME->javascripts = ["javascript"];
$THEME->editor_sheets = [];
$THEME->editor_scss = [];
$THEME->usefallback = true;
$THEME->scss = function ($theme) {
    return theme_stablez_get_main_scss_content($theme);
};
$THEME->extrascsscallback = 'theme_stablez_get_extra_scss';
$THEME->prescsscallback = 'theme_stablez_get_pre_scss';
// $THEME->precompiledcsscallback = 'theme_stablez_get_precompiled_css';
// $THEME->csspostprocess = 'theme_stablez_process_css';

$THEME->enable_dock = false;
$THEME->yuicssmodules = array();
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->requiredblocks = '';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;
$THEME->haseditswitch = true;
$THEME->usescourseindex = true;
// By default, all boost theme do not need their titles displayed.
$THEME->activityheaderconfig = [
    'notitle' => true
];

$block_regions = [
    'side-pre',
    'at_fullheader',
    'above-content',
    'below-content',
    'admin-content'
];
$THEME->layouts = [
    // Standard layout with blocks.
    'standard' => array(
        'file' => 'drawers.php',
        'regions' => $block_regions,
        'defaultregion' => 'side-pre',
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'drawers.php',
        'regions' => $block_regions,
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true),
    ),
    // Main course page.
    'course' => array(
        'file' => 'drawers.php',
        'regions' => $block_regions,
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    'coursecategory' => array(
        'file' => 'drawers.php',
        'regions' => $block_regions,
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    // Part of course, typical for modules - default page layout if $cm specified in require_login().
    'incourse' => array(
        'file' => 'drawers.php',
        'regions' => $block_regions,
        'defaultregion' => 'side-pre',
    ),
    'custompages' => array(
        'file' => 'drawers.php',
        'regions' => $block_regions,
        'defaultregion' => 'side-pre',
    ),
];
