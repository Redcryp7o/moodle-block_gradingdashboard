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
 * AJAX External API for fetching assignment students.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use context_module;

/**
 * Class get_assignment_students.
 *
 * Fetches pending submissions for an assignment.
 */
class get_assignment_students extends external_api {

    /**
     * Define parameters for external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID of the assignment'),
        ]);
    }

    /**
     * Execute the external function to fetch pending submissions.
     *
     * @param int $cmid Course module ID.
     * @return array Array of student submissions.
     */
    public static function execute(int $cmid): array {
        global $DB, $OUTPUT, $PAGE;

        $params = self::validate_parameters(self::execute_parameters(), ['cmid' => $cmid]);
        $cmid = $params['cmid'];

        $cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cmid);
        self::validate_context($context);
        require_capability('mod/assign:grade', $context);

        // Required to render user pictures correctly via OUTPUT.
        $PAGE->set_context($context);

        $repository = new \block_gradingdashboard\repository\grading_repository();
        $records = $repository->get_pending_submissions_for_assignment((int)$cm->instance);

        $assign = $DB->get_record('assign', ['id' => $cm->instance], 'duedate', MUST_EXIST);

        $students = [];
        foreach ($records as $record) {
            $islate = false;
            if ($assign->duedate > 0 && $record->submittedat > $assign->duedate) {
                $islate = true;
            }

            $userrecord = new \stdClass();
            $userrecord->id = (int)$record->u_id;
            foreach (\core_user\fields::get_name_fields() as $namefield) {
                $userrecord->$namefield = $record->{"u_$namefield"} ?? '';
            }
            $userrecord->picture = $record->u_picture ?? 0;
            $userrecord->imagealt = $record->u_imagealt ?? '';
            $userrecord->email = $record->u_email ?? '';

            $profileimage = $OUTPUT->user_picture($userrecord, [
                'size' => 32,
                'link' => true,
                'visibletoscreenreaders' => false,
            ]);

            $gradeurl = new \moodle_url('/mod/assign/view.php', [
                'id' => $cmid,
                'action' => 'grader',
                'userid' => (int)$record->userid,
            ]);

            // Compute SLA severity based on submission age.
            $age = time() - (int)$record->submittedat;
            if ($age > 259200) {
                $severity = 'danger';
            } else if ($age > 86400) {
                $severity = 'warning';
            } else {
                $severity = 'success';
            }

            $students[] = [
                'userid' => (int)$record->userid,
                'submissionid' => (int)$record->submissionid,
                'attemptnumber' => (int)$record->attemptnumber,
                'fullname' => fullname($userrecord),
                'profileimage' => $profileimage,
                'submittedat' => userdate((int)$record->submittedat),
                'submittedattimestamp' => (int)$record->submittedat,
                'status' => $record->status,
                'islate' => $islate,
                'gradeurl' => $gradeurl->out(false),
                'submissionstatus' => get_string('submissionstatus_' . $record->status, 'mod_assign'),
                'severity' => $severity,
            ];
        }

        return ['students' => $students];
    }

    /**
     * Define return structure for external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'students' => new external_multiple_structure(
                new external_single_structure([
                    'userid' => new external_value(PARAM_INT, 'User ID'),
                    'submissionid' => new external_value(PARAM_INT, 'Submission ID'),
                    'attemptnumber' => new external_value(PARAM_INT, 'Attempt number'),
                    'fullname' => new external_value(PARAM_TEXT, 'Student full name'),
                    'profileimage' => new external_value(PARAM_RAW, 'Profile image HTML'),
                    'submittedat' => new external_value(PARAM_TEXT, 'Formatted submission date'),
                    'submittedattimestamp' => new external_value(PARAM_INT, 'Unix timestamp of submission'),
                    'status' => new external_value(PARAM_ALPHANUMEXT, 'Submission status'),
                    'islate' => new external_value(PARAM_BOOL, 'Whether submission is late'),
                    'gradeurl' => new external_value(PARAM_URL, 'URL to grade this submission'),
                    'submissionstatus' => new external_value(PARAM_TEXT, 'Localized submission status'),
                    'severity' => new external_value(PARAM_ALPHA, 'SLA severity'),
                ])
            ),
        ]);
    }
}
