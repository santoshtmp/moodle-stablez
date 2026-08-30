<?php
// This file is part of Moodle - http://moodle.org.
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
 * Base helper class for local_stablezhelpers db_cleaner.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\local\feature;

defined('MOODLE_INTERNAL') || die();

/**
 * Database and temporary-data cleaner.
 */
class db_cleaner {

    /**
     * Clean Moodle temporary/log data based on the supplied options.
     *
     * @param array $options Associative array of toggles and 'days' retention.
     *                        Recognised keys: drafts, logs, tinyautosave, filetrash,
     *                        cache, sessions, tasklog, adhoctasks, upgradelog,
     *                        configlog, backupcontrollers, questionpreviews,
     *                        notifications, stats, days.
     * @return array
     */
    public static function clean(array $options = []): array {
        global $DB;

        self::require_admin();

        $days = isset($options['days']) ? max(1, (int) $options['days']) : 1;
        $result = [];

        if (!empty($options['drafts'])) {
            $result['draft_files_deleted'] = self::clean_drafts($days);
        }

        if (!empty($options['tinyautosave'])) {
            $result['tiny_autosave_deleted'] = self::clean_tiny_autosave($days);
        }

        if (!empty($options['logs'])) {
            $result['logs_deleted'] = self::clean_all_logs();
        }

        if (!empty($options['sessions'])) {
            $result['sessions_deleted'] = self::clean_sessions($days);
        }

        if (!empty($options['tasklog'])) {
            $result['task_log_deleted'] = self::clean_task_log($days);
        }

        if (!empty($options['adhoctasks'])) {
            $result['adhoc_tasks_deleted'] = self::clean_stuck_adhoc_tasks($days);
        }

        if (!empty($options['upgradelog'])) {
            $result['upgrade_log_deleted'] = self::clean_upgrade_log($days);
        }

        if (!empty($options['configlog'])) {
            $result['config_log_deleted'] = self::clean_config_log($days);
        }

        if (!empty($options['backupcontrollers'])) {
            $result['backup_controllers_deleted'] = self::clean_backup_controllers($days);
        }

        if (!empty($options['questionpreviews'])) {
            $result['question_previews_deleted'] = self::clean_question_previews();
        }

        if (!empty($options['notifications'])) {
            $result['notifications_deleted'] = self::clean_read_notifications($days);
        }

        if (!empty($options['stats'])) {
            $result['stats_deleted'] = self::clean_stats();
        }

        if (!empty($options['recyclebin'])) {
            $recyclebin = self::clean_recycle_bin();
            $result['recyclebin_course_deleted'] = $recyclebin['course_items_deleted'];
            $result['recyclebin_category_deleted'] = $recyclebin['category_items_deleted'];
        }

        if (!empty($options['filetrash'])) {
            $fs = get_file_storage();
            $fs->cron();
            $result['file_trash_cleaned'] = true;
        }

        if (!empty($options['cache'])) {
            purge_all_caches();
            $result['cache_purged'] = true;
        }

        return $result;
    }

    /**
     * Delete user draft files older than the specified number of days.
     *
     * @param int $days Number of days to keep.
     * @return int Number of files deleted.
     */
    private static function clean_drafts(int $days = 1): int {
        global $DB;

        $fs = get_file_storage();
        $cutoff = time() - ($days * DAYSECS);
        $deleted = 0;

        $sql = "SELECT contextid,
                   itemid,
                   COUNT(id) AS filecount
              FROM {files}
             WHERE component = :component
               AND filearea = :filearea
               AND filename <> :directory
               AND timemodified < :cutoff
          GROUP BY contextid, itemid";

        $params = [
            'component' => 'user',
            'filearea' => 'draft',
            'directory' => '.',
            'cutoff' => $cutoff,
        ];

        $drafts = $DB->get_recordset_sql($sql, $params);

        foreach ($drafts as $draft) {
            $fs->delete_area_files(
                $draft->contextid,
                'user',
                'draft',
                $draft->itemid
            );

            $deleted += (int) $draft->filecount;
        }

        $drafts->close();

        return $deleted;
    }

    /**
     * Delete TinyMCE autosave records older than the specified number of days.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_tiny_autosave(int $days = 1): int {
        global $DB;

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'tiny_autosave',
            'timemodified < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Permanently empty the course and category recycle bins.
     *
     * This is the tool_recyclebin plugin's own undo mechanism for
     * accidentally deleted courses/activities. Emptying it goes through
     * the plugin's own bin classes (rather than raw table deletes) so
     * that backup files in storage are cleaned up correctly instead of
     * left orphaned. There is no undo once this runs.
     *
     * @return array{course_items_deleted:int, category_items_deleted:int}
     */
    private static function clean_recycle_bin(): array {
        global $DB;

        $result = ['course_items_deleted' => 0, 'category_items_deleted' => 0];

        if (!class_exists('\tool_recyclebin\course_bin') || !class_exists('\tool_recyclebin\category_bin')) {
            // Plugin not installed.
            return $result;
        }

        if ($DB->get_manager()->table_exists('tool_recyclebin_course')) {
            $courses = $DB->get_records_sql(
                'SELECT DISTINCT courseid FROM {tool_recyclebin_course}'
            );

            foreach ($courses as $course) {
                // Confirm the course still exists before touching its bin.
                if (!$DB->record_exists('course', ['id' => $course->courseid])) {
                    continue;
                }

                $bin = new \tool_recyclebin\course_bin($course->courseid);
                foreach ($bin->get_items() as $item) {
                    $bin->delete_item($item);
                    $result['course_items_deleted']++;
                }
            }
        }

        if ($DB->get_manager()->table_exists('tool_recyclebin_category')) {
            $categories = $DB->get_records_sql(
                'SELECT DISTINCT categoryid FROM {tool_recyclebin_category}'
            );

            foreach ($categories as $category) {
                // Confirm the category still exists before touching its bin.
                if (!$DB->record_exists('course_categories', ['id' => $category->categoryid])) {
                    continue;
                }

                $bin = new \tool_recyclebin\category_bin($category->categoryid);

                foreach ($bin->get_items() as $item) {
                    $bin->delete_item($item);
                    $result['category_items_deleted']++;
                }
            }
        }

        return $result;
    }

    /**
     * Delete ALL log data across every log store table present on this
     * site: the standard log store, the legacy log store (if it was ever
     * enabled), and MNet peer logs (if MNet is used).
     *
     * This destroys all historical "who did what when" data used by
     * Moodle's Reports (Live logs, course logs, participation reports)
     * and any compliance/audit process that relies on it. There is no
     * undo short of restoring a database backup.
     *
     * @return int Total number of records deleted across all log tables.
     */
    private static function clean_all_logs(): int {
        global $DB;

        $logtables = ['logstore_standard_log', 'log', 'mnet_log'];
        $deleted = 0;

        foreach ($logtables as $table) {
            if ($DB->get_manager()->table_exists($table)) {
                $deleted += $DB->count_records($table);
                $DB->delete_records($table);
            }
        }

        return $deleted;
    }

    /**
     * Delete expired session records.
     *
     * Normally cleared by Moodle cron, but sites with disabled or broken
     * cron can accumulate a large number of stale rows here.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_sessions(int $days = 1): int {
        global $DB;

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'sessions',
            'timemodified < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete old scheduled/ad-hoc task run history.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_task_log(int $days = 1): int {
        global $DB;

        if (!$DB->get_manager()->table_exists('task_log')) {
            return 0;
        }

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'task_log',
            'timestart < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete ad-hoc tasks that have been stuck (faildelay set, never
     * completing) for longer than the retention window. Running or
     * freshly-queued tasks (faildelay = 0) are left untouched.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_stuck_adhoc_tasks(int $days = 1): int {
        global $DB;

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'task_adhoc',
            'faildelay > 0 AND nextruntime < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete old upgrade log entries.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_upgrade_log(int $days = 1): int {
        global $DB;

        if (!$DB->get_manager()->table_exists('upgrade_log')) {
            return 0;
        }

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'upgrade_log',
            'timemodified < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete old configuration change log entries.
     *
     * Note: this is an audit trail of every settings change made on the
     * site. Only enable this if you do not need long-term change history.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_config_log(int $days = 1): int {
        global $DB;

        if (!$DB->get_manager()->table_exists('config_log')) {
            return 0;
        }

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'config_log',
            'timemodified < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete stale backup controller state left behind by interrupted or
     * long-completed backup/restore operations.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_backup_controllers(int $days = 1): int {
        global $DB;

        if (!$DB->get_manager()->table_exists('backup_controllers')) {
            return 0;
        }

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'backup_controllers',
            'timemodified < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete question usages generated by question-bank "preview" (not
     * real quiz/activity attempts). These accumulate every time a
     * question is previewed from the question bank.
     *
     * @return int Number of question usages deleted.
     */
    private static function clean_question_previews(): int {
        global $DB;

        if (!class_exists('\question_engine')) {
            return 0;
        }

        $usageids = $DB->get_fieldset_select(
            'question_usages',
            'id',
            'component = :component',
            ['component' => 'core_question_preview']
        );

        foreach ($usageids as $usageid) {
            \question_engine::delete_questions_usage_by_activity($usageid);
        }

        return count($usageids);
    }

    /**
     * Delete notifications the user has already read, older than the
     * retention window.
     *
     * @param int $days Number of days to keep.
     * @return int Number of records deleted.
     */
    private static function clean_read_notifications(int $days = 1): int {
        global $DB;

        if (!$DB->get_manager()->table_exists('notifications')) {
            return 0;
        }

        $cutoff = time() - ($days * DAYSECS);

        return $DB->delete_records_select(
            'notifications',
            'timeread IS NOT NULL AND timeread < :cutoff',
            ['cutoff' => $cutoff]
        );
    }

    /**
     * Delete site-statistics tables, but only when site stats are
     * currently disabled (so we do not wipe data an admin is relying on).
     *
     * @return int Number of records deleted across all stats tables.
     */
    private static function clean_stats(): int {
        global $DB, $CFG;

        if (!empty($CFG->enablestats)) {
            // Stats are switched on; leave the data alone.
            return 0;
        }

        $tables = [
            'stats_daily',
            'stats_weekly',
            'stats_monthly',
            'stats_user_daily',
            'stats_user_weekly',
            'stats_user_monthly'
        ];

        $deleted = 0;

        foreach ($tables as $table) {
            if ($DB->get_manager()->table_exists($table)) {
                $deleted += $DB->count_records($table);
                $DB->delete_records($table);
            }
        }

        return $deleted;
    }

    /**
     * Make sure the current user is a site administrator.
     *
     * @return void
     * @throws \require_login_exception
     * @throws \required_capability_exception
     */
    private static function require_admin(): void {
        global $USER;

        require_login();
        /** @var \context context */
        $context = \context_system::instance();

        // Require the standard Moodle site administration capability.
        require_capability('moodle/site:config', $context);

        // Require the actual site administrator.
        if (!is_siteadmin($USER)) {
            throw new \required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                'site administrator'
            );
        }
    }
}
