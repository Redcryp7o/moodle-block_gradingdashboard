<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Grading Dashboard Repository.
 *
 * Provides optimised DML queries for the Grading Dashboard block.
 * All queries use named parameter bindings and are compatible with MySQL,
 * MariaDB, PostgreSQL, MSSQL, and Oracle via Moodle DML.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\repository;

defined('MOODLE_INTERNAL') || die();

use dml_exception;

/**
 * Class grading_repository.
 *
 * Direct database access class using Moodle DML APIs.
 */
class grading_repository {

    /**
     * Fetch raw pending grading counts grouped by course, section, and assignment from the database.
     *
     * Utilizes an optimized query joining courses, sections, course modules, assignments,
     * submissions, users, enrolments, grades, and workflow flags. Optionally filters by a list
     * of permitted course IDs.
     * Gracefully catches database exceptions to prevent dashboard crashes.
     *
     * @param array|null $courseids Optional array of course IDs to restrict the query.
     * @return array Array of raw database records.
     */
    public function get_pending_grading_counts(?array $courseids = null): array {
        global $DB;

        $params = [
            'modulename'    => 'assign',
            'status'        => 'submitted',
            'latest'        => 1,
            'enrolstatus'   => 0, // ENROL_INSTANCE_ENABLED
            'uestatus'      => 0, // ENROL_USER_ACTIVE
            'releasedstate' => 'released',
            'now1'          => time(),
            'now2'          => time(),
            'now3'          => time(),
            'now4'          => time(),
            'now5'          => time(),
        ];

        $where = "s.status = :status 
                  AND s.latest = :latest 
                  AND u.deleted = 0 
                  AND u.suspended = 0
                  AND EXISTS (
                      SELECT 1
                        FROM {user_enrolments} ue
                        JOIN {enrol} e ON e.id = ue.enrolid
                       WHERE ue.userid = s.userid
                         AND e.courseid = c.id
                         AND e.status = :enrolstatus
                         AND ue.status = :uestatus
                  )
                  AND (
                      (a.markingworkflow = 1 AND (uf.workflowstate IS NULL OR uf.workflowstate <> :releasedstate))
                      OR
                      (a.markingworkflow = 0 AND (g.grade IS NULL OR g.grade < 0 OR g.timemodified IS NULL OR s.timemodified > g.timemodified))
                  )";

        if ($courseids !== null) {
            // Safe, database-portable mapping of array parameters.
            list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $where .= " AND c.id $insql";
            $params = array_merge($params, $inparams);
        }

        $sql = "SELECT cm.id AS uniqueid,
                       c.id AS courseid, c.fullname AS coursefullname, c.shortname AS courseshortname, c.visible AS coursevisible, c.format AS courseformat,
                       cs.id AS sectionid, cs.section AS sectionnum, cs.name AS sectionname, cs.visible AS sectionvisible,
                       a.id AS assignid, a.name AS assignname, cm.id AS cmid, a.duedate AS duedate,
                       COUNT(s.id) AS pendingcount,
                       MIN(s.timemodified) AS oldest_submission,
                       SUM(CASE WHEN (:now1 - s.timemodified) <= 86400 THEN 1 ELSE 0 END) AS freshcount,
                       SUM(CASE WHEN (:now2 - s.timemodified) > 86400 AND (:now3 - s.timemodified) <= 259200 THEN 1 ELSE 0 END) AS waitingcount,
                       SUM(CASE WHEN (:now4 - s.timemodified) > 259200 THEN 1 ELSE 0 END) AS overduecount
                  FROM {course} c
                  JOIN {course_sections} cs ON cs.course = c.id
                  JOIN {course_modules} cm ON cm.course = c.id AND cm.section = cs.id
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {assign} a ON a.id = cm.instance
                  JOIN {assign_submission} s ON s.assignment = a.id
                  JOIN {user} u ON u.id = s.userid
             LEFT JOIN {assign_grades} g ON g.assignment = a.id 
                                       AND g.userid = s.userid 
                                       AND g.attemptnumber = s.attemptnumber
             LEFT JOIN {assign_user_flags} uf ON uf.assignment = a.id AND uf.userid = s.userid
                 WHERE $where
              GROUP BY cm.id, c.id, c.fullname, c.shortname, c.visible, c.format, cs.id, cs.section, cs.name, cs.visible, a.id, a.name, a.duedate
              ORDER BY c.fullname ASC, cs.section ASC, a.name ASC";

        try {
            // Moodle's get_records_sql requires the first column of the query to be unique.
            return $DB->get_records_sql($sql, $params);
        } catch (dml_exception $e) {
            // Log database exceptions to Moodle developer debugging while preventing fatal UI crashes.
            debugging('Grading Dashboard DB Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Fetch raw pending submissions for a specific assignment.
     *
     * Joins submissions, users, enrolments, grades, and workflow tables to detail pending items.
     * Gracefully catches database exceptions to prevent dashboard crashes.
     *
     * All user fields are selected with a 'u_' prefix alias (e.g. u_firstname, u_id)
     * to prevent column name collisions with submission fields (e.g. s.userid, s.id).
     * The service layer maps these prefixed fields back when building the user object.
     *
     * @param int $assignmentid The assignment ID.
     * @return array Array of raw submission and student records.
     */
    public function get_pending_submissions_for_assignment(int $assignmentid): array {
        global $DB;

        // Build an explicit, collision-free SELECT fragment for all required user fields.
        // Using core_user\fields::get_name_fields() ensures we retrieve every field that
        // fullname() may require regardless of the site's name display format configuration.
        // Each field is aliased with a 'u_' prefix to prevent collision with submission columns.
        $namefieldselects = [];
        foreach (\core_user\fields::get_name_fields() as $field) {
            $namefieldselects[] = "u.$field AS u_$field";
        }
        // Add user picture fields required by user_picture().
        $namefieldselects[] = 'u.id AS u_id';
        $namefieldselects[] = 'u.picture AS u_picture';
        $namefieldselects[] = 'u.imagealt AS u_imagealt';
        $namefieldselects[] = 'u.email AS u_email';

        $userfieldssql = implode(', ', $namefieldselects);

        $sql = "SELECT s.id AS uniqueid,
                       s.id AS submissionid,
                       s.userid,
                       s.attemptnumber,
                       s.timemodified AS submittedat,
                       s.status,
                       $userfieldssql
                  FROM {assign_submission} s
                  JOIN {assign} a ON a.id = s.assignment
                  JOIN {user} u ON u.id = s.userid
             LEFT JOIN {assign_grades} g ON g.assignment = s.assignment
                                       AND g.userid = s.userid
                                       AND g.attemptnumber = s.attemptnumber
             LEFT JOIN {assign_user_flags} uf ON uf.assignment = s.assignment AND uf.userid = s.userid
                 WHERE s.assignment = :assignmentid
                   AND s.status = :status
                   AND s.latest = :latest
                   AND u.deleted = 0
                   AND u.suspended = 0
                   AND EXISTS (
                       SELECT 1
                         FROM {user_enrolments} ue
                         JOIN {enrol} e ON e.id = ue.enrolid
                        WHERE ue.userid = s.userid
                          AND e.courseid = a.course
                          AND e.status = :enrolstatus
                          AND ue.status = :uestatus
                   )
                   AND (
                       (a.markingworkflow = 1 AND (uf.workflowstate IS NULL OR uf.workflowstate <> :releasedstate))
                       OR
                       (a.markingworkflow = 0 AND (g.grade IS NULL OR g.grade < 0 OR g.timemodified IS NULL OR s.timemodified > g.timemodified))
                   )
              ORDER BY s.timemodified ASC";

        $params = [
            'assignmentid'  => $assignmentid,
            'status'        => 'submitted',
            'latest'        => 1,
            'enrolstatus'   => 0, // ENROL_INSTANCE_ENABLED
            'uestatus'      => 0, // ENROL_USER_ACTIVE
            'releasedstate' => 'released',
        ];

        try {
            // Moodle's get_records_sql requires the first column to be unique (s.id is primary key).
            return $DB->get_records_sql($sql, $params);
        } catch (dml_exception $e) {
            // Log database exceptions to Moodle developer debugging.
            debugging('Grading Dashboard Student Fetch DB Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }
}
