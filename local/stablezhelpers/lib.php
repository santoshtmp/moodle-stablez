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
 * Library file for local_stablezhelpers plugin.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_stablezhelpers\local\handler\block_handler;

defined('MOODLE_INTERNAL') || die();

/**
 * Post installation procedure.
 */
function local_stablezhelpers_after_install() {
    // Add any post-installation logic here.
    return true;
}

/**
 * Post uninstallation procedure.
 */
function local_stablezhelpers_after_uninstall() {
    // Add any post-uninstallation logic here.
    return true;
}

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
function local_stablezhelpers_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_BLOCK) {
        send_file_not_found();
    }
    
    $stablez_filearea = [block_handler::FILEAREA_FIELD_GALLERY];
    if (
        !in_array($filearea, $stablez_filearea)
        //    || !(preg_match("/^block_start_guideline_img_[1-9][0-9]?$/", $filearea))
    ) {
        send_file_not_found();
    }

    // By default, theme files must be cache-able by both browsers and proxies.
    if (!array_key_exists('cacheability', $options)) {
        $options['cacheability'] = 'public';
    }
    //
    $fs = get_file_storage();
    $filename = array_pop($args);
    $filepath = '/'; // $args ? '/' . implode('/', $args) . '/' : '/';
    $files = $fs->get_area_files(
        $context->id,
        block_handler::COMPONENT,
        block_handler::FILEAREA_FIELD_GALLERY,
        $args[0],
        'timemodified',
        false
    );
    if ($files) {
        $file = reset($files); // Get the first file
    } else {
        $file = $fs->get_file(
            $context->id,
            block_handler::COMPONENT,
            block_handler::FILEAREA_FIELD_GALLERY,
            $args[0],
            $filepath,
            $filename
        );
    }
    if ($file && !$file->is_directory()) {
        // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
        // do not lower it because the files are dispalyed very often.
        \core\session\manager::write_close();
        return send_stored_file($file, null, 0, $forcedownload, $options);
    }

    // 
    send_file_not_found();
}
