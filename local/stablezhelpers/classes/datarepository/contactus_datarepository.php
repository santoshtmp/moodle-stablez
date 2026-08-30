<?php
// This file is part of Moodle - http://moodle.org.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published
// by the Free Software Foundation, either version 3 of the License,
// or (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contact Us form data repository.
 *
 * Provides database operations for Contact Us form submissions.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stablezhelpers\datarepository;

defined('MOODLE_INTERNAL') || die();

/**
 * Contact Us form data repository.
 */
class contactus_datarepository {

    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = 'local_stablezhelpers_contact';

    /**
     * Create a Contact Us submission.
     *
     * @param object $data Submission data.
     * @return int New submission ID.
     */
    public function create($data) {
        global $DB;

        $record = new \stdClass();

        $record->userid = $data->userid ?? 0;
        $record->name = $data->name;
        $record->email = $data->email;
        $record->subject = $data->subject ?? '';
        $record->message = $data->message;
        $record->other = $data->other ?? null;
        $record->status = $data->status ?? 0;
        $record->timecreated = time();
        $record->timemodified = time();

        return $DB->insert_record($this->table, $record);
    }

    /**
     * Get a submission by ID.
     *
     * @param int $id Submission ID.
     * @param bool $mustexist Whether the record must exist.
     * @return object|false Submission record.
     */
    public function get($id, $mustexist = false) {
        global $DB;

        return $DB->get_record(
            $this->table,
            ['id' => $id],
            '*',
            $mustexist ? MUST_EXIST : IGNORE_MISSING
        );
    }

    /**
     * Get all submissions.
     *
     * @param int $limitfrom Starting record.
     * @param int $limitnum Number of records.
     * @param string $sort Sort order.
     * @return array Submission records.
     */
    public function get_all($limitfrom = 0, $limitnum = 0, $sort = 'timecreated DESC') {
        global $DB;

        return $DB->get_records(
            $this->table,
            [],
            $sort,
            '*',
            $limitfrom,
            $limitnum
        );
    }

    /**
     * Count all submissions.
     *
     * @return int Number of submissions.
     */
    public function count() {
        global $DB;

        return $DB->count_records($this->table);
    }

    /**
     * Count submissions by status.
     *
     * @param int $status Submission status.
     * @return int Number of submissions.
     */
    public function count_by_status($status) {
        global $DB;

        return $DB->count_records(
            $this->table,
            ['status' => $status]
        );
    }

    /**
     * Get filtered submissions.
     *
     * Name and email use partial matching.
     *
     * @param array $filters Filter values.
     * @param int $limitfrom Starting record.
     * @param int $limitnum Number of records.
     * @return array Submission records.
     */
    public function get_filtered($filters, $limitfrom = 0, $limitnum = 20) {
        global $DB;

        $conditions = [];
        $params = [];

        if (!empty($filters['name'])) {
            $conditions[] = $DB->sql_like(
                'name',
                ':name',
                false,
                false
            );

            $params['name'] = '%' . $DB->sql_like_escape($filters['name']) . '%';
        }

        if (!empty($filters['email'])) {
            $conditions[] = $DB->sql_like(
                'email',
                ':email',
                false,
                false
            );

            $params['email'] = '%' . $DB->sql_like_escape($filters['email']) . '%';
        }

        $where = '';

        if ($conditions) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT *
                  FROM {" . $this->table . "}
                 $where
              ORDER BY timemodified DESC, id DESC";

        return $DB->get_records_sql(
            $sql,
            $params,
            $limitfrom,
            $limitnum
        );
    }

    /**
     * Count filtered submissions.
     *
     * @param array $filters Filter values.
     * @return int Number of submissions.
     */
    public function count_filtered($filters) {
        global $DB;

        $conditions = [];
        $params = [];

        if (!empty($filters['name'])) {
            $conditions[] = $DB->sql_like(
                'name',
                ':name',
                false,
                false
            );

            $params['name'] = '%' . $DB->sql_like_escape($filters['name']) . '%';
        }

        if (!empty($filters['email'])) {
            $conditions[] = $DB->sql_like(
                'email',
                ':email',
                false,
                false
            );

            $params['email'] = '%' . $DB->sql_like_escape($filters['email']) . '%';
        }

        $where = '';

        if ($conditions) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT COUNT(1)
                  FROM {" . $this->table . "}
                 $where";

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Update submission status.
     *
     * @param int $id Submission ID.
     * @param int $status Submission status.
     * @return bool True on success.
     */
    public function update_status($id, $status) {
        global $DB;

        $record = new \stdClass();
        $record->id = $id;
        $record->status = $status;
        $record->timemodified = time();

        return $DB->update_record($this->table, $record);
    }

    /**
     * Delete submission.
     *
     * @param int $id Submission ID.
     * @return bool True on success.
     */
    public function delete($id) {
        global $DB;

        return $DB->delete_records(
            $this->table,
            ['id' => $id]
        );
    }
}
