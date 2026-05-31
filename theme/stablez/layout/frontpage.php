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
 * "frontpage" based layout for the theme_stablez
 * @package    theme_stablez
 * @copyright  2025 stablez
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_stablez\local\service\theme_settings_service;

defined('MOODLE_INTERNAL') || die();
global $templatecontext; 

require_once($CFG->dirroot . '/theme/stablez/includes/layout_templatecontext.php');
$templatecontext = array_merge(
    $templatecontext,
    theme_settings_service::get_instance()->front_page_settings()
);

echo $OUTPUT->render_from_template('theme_stablez/layout/frontpage', $templatecontext);
