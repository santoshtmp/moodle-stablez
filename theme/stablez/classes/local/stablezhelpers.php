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
 *
 * @package   theme_stablez
 * @copyright 2025 stablez
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace theme_stablez\local;

defined('MOODLE_INTERNAL') || die;

/**
 * class stablezhelpers
 * 
 * @package    theme_stablez
 * @copyright   
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stablezhelpers {
    /**
     * set_extra_css_js
     * @param string $path path to your theme example '/theme/yourtheme'
     */
    public static function set_extra_css_js($path) {
        global $PAGE, $CFG;
        // $theme = isset($PAGE->theme->name) ? $PAGE->theme->name : '';
        $style_path = $path . "/style";
        $js_path = $path . "/javascript";
        /**
         * css files
         */
        $page_css = [];
        // By page type
        $pagetype = $PAGE->pagetype;
        $filepath = $CFG->dirroot . $style_path . '/' . $pagetype . '.css';
        if (file_exists($filepath)) {
            $page_css[] =  $pagetype;
        }
        // By page layout
        $pagelayout = $PAGE->pagelayout;
        $filepath = $CFG->dirroot . $style_path . '/' . $pagelayout . '.css';
        if (file_exists($filepath)) {
            $page_css[] =  $pagelayout;
        }
        foreach ($page_css as $key => $value) {
            $PAGE->requires->css(new moodle_url($CFG->wwwroot . $style_path . '/' . $value . '.css'));
        }

        /**
         * js files
         */
        $page_js = [];
        // By page type
        $pagetype = $PAGE->pagetype;
        $filepath = $CFG->dirroot . $js_path . '/' . $pagetype . '.js';
        if (file_exists($filepath)) {
            $page_js[] =  $pagetype;
        }
        // By page layout
        $pagelayout = $PAGE->pagelayout;
        $filepath = $CFG->dirroot . $js_path . '/' . $pagelayout . '.js';
        if (file_exists($filepath)) {
            $page_js[] =  $pagelayout;
        }
        foreach ($page_js as $key => $value) {
            $PAGE->requires->js(new moodle_url($CFG->wwwroot .  $js_path . '/' . $value . '.js'));
        }
    }


    /**
     * 
     */
    public static function get_svg_pix_icon_content($filename) {
        global $CFG;
        $icon = $CFG->dirroot . '/theme/stablez/pix/icons/' . $filename . '.svg';
        $svg_icon_content = @file_get_contents($icon);
        return $svg_icon_content;
    }
}
