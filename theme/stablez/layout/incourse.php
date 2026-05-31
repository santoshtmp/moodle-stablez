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
 * "incourse" based layout for the theme_stablez
 *
 * @package    theme_stablez
 * @copyright  2025 stablez
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $templatecontext;

// $cm_id = isset($this->page->cm->id) ? $this->page->cm->id : '';
require_once($CFG->dirroot . '/theme/stablez/includes/layout_templatecontext.php');

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

echo $OUTPUT->render_from_template('theme_stablez/layout/incourse', $templatecontext);
