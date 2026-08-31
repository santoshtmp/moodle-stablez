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
    /*
     * ---------------------------------------------------------
     * Validate context.
     * ---------------------------------------------------------
     */
    if ($context->contextlevel == CONTEXT_BLOCK) {
        send_file_not_found();
    }

    /*
     * ---------------------------------------------------------
     * Validate filearea.
     * ---------------------------------------------------------
     */
    $stablez_filearea = [
        block_handler::FILEAREA_FIELD_GALLERY,
        'content_page_image',
        'content',
    ];

    if (!in_array($filearea, $stablez_filearea, true)) {
        send_file_not_found();
    }

    /*
     * ---------------------------------------------------------
     * Cacheability.
     * ---------------------------------------------------------
     */
    if (!array_key_exists('cacheability', $options)) {
        $options['cacheability'] = 'public';
    }

    /*
     * ---------------------------------------------------------
     * Extract itemid and filename.
     *
     * URL:
     *
     * pluginfile.php/
     *     <contextid>/
     *     <component>/
     *     <filearea>/
     *     <itemid>/
     *     <filename>
     *
     * Example:
     *
     * pluginfile.php/1/local_stablezhelpers/content/69074995/bg.png
     * ---------------------------------------------------------
     */
    if (empty($args)) {
        send_file_not_found();
    }

    $filename = array_pop($args);
    $itemid = array_shift($args);

    if (empty($itemid) || empty($filename)) {
        send_file_not_found();
    }

    /*
     * ---------------------------------------------------------
     * Build filepath.
     *
     * Currently your editor uses:
     *
     * 'subdirs' => false
     *
     * so normally this will simply be '/'.
     *
     * The code also supports subdirectories.
     * ---------------------------------------------------------
     */
    $filepath = '/';

    if (!empty($args)) {
        $filepath = '/' . implode('/', $args) . '/';
    }

    /*
     * ---------------------------------------------------------
     * Get the exact requested file.
     *
     * IMPORTANT:
     *
     * Do NOT use get_area_files() + reset().
     *
     * reset() returns the first file and can cause:
     *
     * bg.png
     * Frame 359.png
     *
     * to both return the same physical file.
     * ---------------------------------------------------------
     */
    $fs = get_file_storage();

    $file = $fs->get_file(
        $context->id,
        'local_stablezhelpers',
        $filearea,
        $itemid,
        $filepath,
        $filename
    );

    /*
     * ---------------------------------------------------------
     * File not found.
     * ---------------------------------------------------------
     */
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    /*
     * ---------------------------------------------------------
     * Close session before sending file.
     * ---------------------------------------------------------
     */
    \core\session\manager::write_close();

    /*
     * ---------------------------------------------------------
     * Send exact requested file.
     * ---------------------------------------------------------
     */
    return send_stored_file(
        $file,
        null,
        0,
        $forcedownload,
        $options
    );
}
