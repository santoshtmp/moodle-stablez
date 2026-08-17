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
 * stablezblocks.
 *
 * @package    block_stablezblocks
 * @copyright  2025 stablezblocks
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_stablezhelpers\local\handler\block_handler;

defined('MOODLE_INTERNAL') || die();

/**
 * Serve gallery files.
 *
 * @param stdClass $course Course object.
 * @param stdClass $birecord_or_cm Block instance record.
 * @param context $context Block context.
 * @param string $filearea File area.
 * @param array $args Extra arguments.
 * @param bool $forcedownload Whether to force download.
 * @param array $options Additional options.
 * @return bool
 */
function block_stablezblocks_pluginfile(
    $course,
    $birecord_or_cm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    $stablezblocks_filearea = [block_handler::FILEAREA_FIELD_GALLERY];
    if (
        !in_array($filearea, $stablezblocks_filearea)
        //    || !(preg_match("/^block_start_guideline_img_[1-9][0-9]?$/", $filearea))
    ) {
        send_file_not_found();
    }

    // If the block is in a course context, enforce course access.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        $parentcontext = $context->get_parent_context();

        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            $category = $DB->get_record(
                'course_categories',
                ['id' => $parentcontext->instanceid],
                '*',
                MUST_EXIST
            );

            if (!$category->visible) {
                require_capability('moodle/category:viewhiddencategories', $parentcontext);
            }
        } else if (
            $parentcontext->contextlevel === CONTEXT_USER &&
            $parentcontext->instanceid != $USER->id
        ) {
            send_file_not_found();
        }
    }

    // The item ID is the block instance ID, which keeps each block's gallery separate.
    $itemid = (int)array_shift($args);

    if ($itemid <= 0 || $context->instanceid != $itemid) {
        send_file_not_found();
    }

    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        block_handler::COMPONENT,
        block_handler::FILEAREA_FIELD_GALLERY,
        $itemid,
        $filepath,
        $filename
    );

    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    \core\session\manager::write_close();
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
