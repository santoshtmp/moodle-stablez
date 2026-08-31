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
 * Upgrade script.
 * For more information, take a look to the documentation available:
 *   - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for local_stablezhelpers.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Result.
 */
function xmldb_local_stablezhelpers_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Version 2026092000: Create all initial tables.
    $newversion = 2026092000;
    if ($oldversion < $newversion) {
        // ==================== local_stablezhelpers_course_meta ====================.
        $table = new xmldb_table('local_stablezhelpers_course_meta');

        // Adding fields to table local_stablezhelpers_course_meta.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('meta_key', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('meta_value', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_stablezhelpers_course_meta.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('courseid_metakey_uniq', XMLDB_KEY_UNIQUE, ['courseid', 'meta_key']);

        // Conditionally launch create table for local_stablezhelpers_course_meta.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Add composite unique index for performance and data integrity.
        $index = new xmldb_index('courseid_metakey_uniq', XMLDB_INDEX_UNIQUE, ['courseid', 'meta_key']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add index for meta_key analytics queries.
        $index = new xmldb_index('metakey_idx', XMLDB_INDEX_NOTUNIQUE, ['meta_key']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // ==================== local_stablezhelpers_user_meta ====================.
        $table = new xmldb_table('local_stablezhelpers_user_meta');

        // Adding fields to table local_stablezhelpers_user_meta.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('meta_key', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('meta_value', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_stablezhelpers_user_meta.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('userid_metakey_uniq', XMLDB_KEY_UNIQUE, ['userid', 'meta_key']);

        // Conditionally launch create table for local_stablezhelpers_user_meta.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Add composite unique index for performance and data integrity.
        $index = new xmldb_index('userid_metakey_uniq', XMLDB_INDEX_UNIQUE, ['userid', 'meta_key']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add index for meta_key analytics queries.
        $index = new xmldb_index('metakey_idx', XMLDB_INDEX_NOTUNIQUE, ['meta_key']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // ==================== local_stablezhelpers_content ====================.
        $table = new xmldb_table('local_stablezhelpers_content');

        // Adding fields to table local_stablezhelpers_content.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('contenttype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('contentformat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('contentitemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('image', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_stablezhelpers_content.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('usermodified_fk', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $table->add_key('shortname_uniq', XMLDB_KEY_UNIQUE, ['shortname']);

        // Conditionally launch create table for local_stablezhelpers_content.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Add indexes to local_stablezhelpers_content.
        $index = new xmldb_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('usermodified_ix', XMLDB_INDEX_NOTUNIQUE, ['usermodified']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('contenttype_ix', XMLDB_INDEX_NOTUNIQUE, ['contenttype']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('status_ix', XMLDB_INDEX_NOTUNIQUE, ['status']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('parentid_ix', XMLDB_INDEX_NOTUNIQUE, ['parentid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('contenttype_status_idx', XMLDB_INDEX_NOTUNIQUE, ['contenttype', 'status']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // ==================== local_stablezhelpers_content_meta ====================.
        $table = new xmldb_table('local_stablezhelpers_content_meta');

        // Adding fields to table local_stablezhelpers_content_meta.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('meta_key', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('meta_value', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_stablezhelpers_content_meta.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('contentid_fk', XMLDB_KEY_FOREIGN, ['contentid'], 'local_stablezhelpers_content', ['id']);
        $table->add_key('contentid_metakey_uniq', XMLDB_KEY_UNIQUE, ['contentid', 'meta_key']);

        // Conditionally launch create table for local_stablezhelpers_content_meta.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Add index for content ID.
        $index = new xmldb_index('contentid_ix', XMLDB_INDEX_NOTUNIQUE, ['contentid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add index for meta key.
        $index = new xmldb_index('metakey_ix', XMLDB_INDEX_NOTUNIQUE, ['meta_key']);

        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // ==================== local_stablezhelpers_contactform ====================.
        $table = new xmldb_table('local_stablezhelpers_contactform');

        // Adding fields to table local_stablezhelpers_contactform.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('other', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_stablezhelpers_contactform.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_stablezhelpers_contactform.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Add index for searching submissions by email address.
        $index = new xmldb_index('email_idx', XMLDB_INDEX_NOTUNIQUE, ['email']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add index for filtering submissions by Moodle user ID.
        $index = new xmldb_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add index for filtering submissions by status.
        $index = new xmldb_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add index for sorting submissions by creation time.
        $index = new xmldb_index('timecreated_idx', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Stablezhelpers savepoint reached.
        upgrade_plugin_savepoint(true, $newversion, 'local', 'stablezhelpers');
    }

    return true;
}
