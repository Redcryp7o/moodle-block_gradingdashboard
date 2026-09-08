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
 * Grading Dashboard Service.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\service;

use block_gradingdashboard\repository\grading_repository;
use stdClass;
use context_course;
use Exception;

/**
 * Class grading_dashboard_service.
 *
 * Implements business logic, permission verification, and data coordination.
 */
class grading_dashboard_service {
    /** @var grading_repository The repository instance. */
    protected grading_repository $repository;

    /**
     * Constructor.
     *
     * @param grading_repository|null $repository Optional repository instance.
     */
    public function __construct(?grading_repository $repository = null) {
        $this->repository = $repository ?? new grading_repository();
    }

    /**
     * Retrieve courses with pending grading, checking visibility and capabilities.
     *
     * Groups assignments under sections, and sections under courses.
     * Loads student submissions only for the currently expanded assignment module.
     *
     * @return array Array of stdClass course objects containing nested sections and assignments.
     */
    public function get_courses_with_pending_grading(): array {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        // Read the optionally expanded assignment parameter from the URL.
        $expandassign = optional_param('expandassign', 0, PARAM_INT);

        try {
            $courseids = null;

            // Performance optimization: restrict queries to enrolled courses for non-admins.
            if (!is_siteadmin()) {
                $mycourses = enrol_get_users_courses($USER->id, true, 'id');
                if (empty($mycourses)) {
                    return [];
                }
                $courseids = array_keys($mycourses);
            }

            $rawrecords = $this->repository->get_pending_grading_counts($courseids);
            $courses = [];
            $coursechecks = []; // Caches visibility/capability checks by course ID.
            $sectionchecks = []; // Caches section visibility/capability checks by section ID.
            $modinfocache = []; // Caches get_fast_modinfo instances.

            foreach ($rawrecords as $record) {
                $courseid = (int)$record->courseid;
                $sectionid = (int)$record->sectionid;
                $cmid = (int)$record->cmid;

                // 1. Course Level Checks.
                if (!isset($coursechecks[$courseid])) {
                    $coursecontext = context_course::instance($courseid);

                    // Respect course visibility: if hidden, user must have viewhiddencourses capability.
                    if (!$record->coursevisible && !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                        $coursechecks[$courseid] = false;
                        continue;
                    }

                    // Respect assignment grading capability.
                    if (!has_capability('mod/assign:grade', $coursecontext)) {
                        $coursechecks[$courseid] = false;
                        continue;
                    }

                    $coursechecks[$courseid] = [
                        'context'   => $coursecontext,
                        'fullname'  => format_string($record->coursefullname, true, ['context' => $coursecontext]),
                        'shortname' => $record->courseshortname ?? '',
                    ];
                }

                if ($coursechecks[$courseid] === false) {
                    continue;
                }

                $coursecontext = $coursechecks[$courseid]['context'];

                // 2. Section Level Checks.
                if (!isset($sectionchecks[$sectionid])) {
                    // Respect section visibility: if hidden, user must have viewhiddensections capability.
                    if (!$record->sectionvisible && !has_capability('moodle/course:viewhiddensections', $coursecontext)) {
                        $sectionchecks[$sectionid] = false;
                        continue;
                    }

                    // Get formatted section name using course format API.
                    $courserecord = (object)[
                        'id' => $courseid,
                        'fullname' => $record->coursefullname,
                        'visible' => $record->coursevisible,
                        'format' => $record->courseformat,
                    ];
                    $courseformat = course_get_format($courserecord);
                    $sectionrecord = (object)[
                        'id' => $sectionid,
                        'section' => (int)$record->sectionnum,
                        'name' => $record->sectionname,
                        'course' => $courseid,
                    ];
                    $sectionname = $courseformat->get_section_name($sectionrecord);

                    $sectionchecks[$sectionid] = [
                        'name' => $sectionname,
                    ];
                }

                if ($sectionchecks[$sectionid] === false) {
                    continue;
                }

                // 3. Activity / Assignment Level Checks (using Moodle's get_fast_modinfo).
                if (!isset($modinfocache[$courseid])) {
                    $modinfocache[$courseid] = get_fast_modinfo($courseid);
                }

                $modinfo = $modinfocache[$courseid];
                try {
                    $cm = $modinfo->get_cm($cmid);
                } catch (Exception $e) {
                    // Deleted or missing course module.
                    continue;
                }

                // Respect Moodle visibility, conditional access / availability rules.
                if (!$cm->uservisible) {
                    continue;
                }

                // Respect assignment-specific grading capability override.
                if (!has_capability('mod/assign:grade', $cm->context)) {
                    continue;
                }

                // Initialize course in list if not already present.
                if (!isset($courses[$courseid])) {
                    $courses[$courseid] = (object)[
                        'id'           => $courseid,
                        'fullname'     => $coursechecks[$courseid]['fullname'],
                        'shortname'    => $coursechecks[$courseid]['shortname'],
                        'pendingcount' => 0,
                        'freshcount'   => 0,
                        'waitingcount' => 0,
                        'overduecount' => 0,
                        'severity'     => 'success',
                        'sections'     => [],
                    ];
                }

                // Initialize section in course if not already present.
                if (!isset($courses[$courseid]->sections[$sectionid])) {
                    $courses[$courseid]->sections[$sectionid] = (object)[
                        'id' => $sectionid,
                        'name' => $sectionchecks[$sectionid]['name'],
                        'pendingcount' => 0,
                        'freshcount' => 0,
                        'waitingcount' => 0,
                        'overduecount' => 0,
                        'severity' => 'success',
                        'assignments' => [],
                    ];
                }

                // Add assignment to section.
                $assignment = (object)[
                    'id' => (int)$record->assignid, // Assignment ID.
                    'cmid' => $cmid,
                    'assignmentid' => (int)$record->assignid,
                    'title' => format_string($record->assignname, true, ['context' => $cm->context]),
                    'pendingcount' => (int)$record->pendingcount,
                    'freshcount' => (int)$record->freshcount,
                    'waitingcount' => (int)$record->waitingcount,
                    'overduecount' => (int)$record->overduecount,
                    'expanded' => false,
                    'severity' => self::compute_severity((int)$record->oldest_submission),
                    'oldest_submission' => (int)$record->oldest_submission,
                    'children' => [],
                ];

                // Lazy-load student submissions for the expanded assignment node.
                if ($cmid === $expandassign) {
                    $assignment->expanded = true;
                    $studentrecords = $this->repository->get_pending_submissions_for_assignment((int)$record->assignid);

                    foreach ($studentrecords as $studentrecord) {
                        // Check if student submission is late (submitted after assignment due date).
                        $islate = false;
                        if ($record->duedate > 0 && $studentrecord->submittedat > $record->duedate) {
                            $islate = true;
                        }

                        // Build a fully-populated user object for fullname() and user_picture() compatibility.
                        // The repository selects user fields with a 'u_' prefix (e.g. u_firstname, u_id)
                        // to prevent SQL column collisions with submission fields (e.g. s.userid, s.id).
                        // We map those prefixed fields back here into a clean stdClass with canonical names.
                        $userrecord = new \stdClass();
                        // The u_id alias holds {user}.id, distinct from s.id (submission PK) and s.userid.
                        $userrecord->id = (int)$studentrecord->u_id;

                        // Map all name fields back from their u_-prefixed aliases.
                        foreach (\core_user\fields::get_name_fields() as $namefield) {
                            $userrecord->$namefield = $studentrecord->{"u_$namefield"} ?? '';
                        }

                        // Map user picture fields back from their u_-prefixed aliases.
                        $userrecord->picture  = $studentrecord->u_picture ?? 0;
                        $userrecord->imagealt = $studentrecord->u_imagealt ?? '';
                        $userrecord->email    = $studentrecord->u_email ?? '';

                        $student = (object)[
                            'userid'        => (int)$studentrecord->userid,
                            'submissionid'  => (int)$studentrecord->submissionid,
                            'attemptnumber' => (int)$studentrecord->attemptnumber,
                            // Call fullname() on the complete, correctly-mapped user object.
                            'fullname'      => fullname($userrecord),
                            'userrecord'    => $userrecord,
                            'submittedat'   => userdate($studentrecord->submittedat),
                            'status'        => $studentrecord->status,
                            'islate'        => $islate,
                            'severity'      => self::compute_severity((int)$studentrecord->submittedat),
                            'submittedattimestamp' => (int)$studentrecord->submittedat,
                        ];

                        $assignment->children[] = $student;
                    }
                }

                $courses[$courseid]->sections[$sectionid]->assignments[] = $assignment;
                $courses[$courseid]->sections[$sectionid]->pendingcount += $assignment->pendingcount;
                $courses[$courseid]->sections[$sectionid]->freshcount += $assignment->freshcount;
                $courses[$courseid]->sections[$sectionid]->waitingcount += $assignment->waitingcount;
                $courses[$courseid]->sections[$sectionid]->overduecount += $assignment->overduecount;

                $courses[$courseid]->pendingcount += $assignment->pendingcount;
                $courses[$courseid]->freshcount += $assignment->freshcount;
                $courses[$courseid]->waitingcount += $assignment->waitingcount;
                $courses[$courseid]->overduecount += $assignment->overduecount;

                // Aggregate severity for section.
                $sectionseverity = $courses[$courseid]->sections[$sectionid]->severity;
                if (self::severity_rank($assignment->severity) > self::severity_rank($sectionseverity)) {
                    $courses[$courseid]->sections[$sectionid]->severity = $assignment->severity;
                }

                // Aggregate severity for course.
                $courseseverity = $courses[$courseid]->severity;
                $aggregatedseverity = $courses[$courseid]->sections[$sectionid]->severity;
                if (self::severity_rank($aggregatedseverity) > self::severity_rank($courseseverity)) {
                    $courses[$courseid]->severity = $aggregatedseverity;
                }
            }

            // Clean up associative keys and convert to sequential arrays.
            $finalcourses = [];
            foreach ($courses as $course) {
                $coursesections = [];
                foreach ($course->sections as $section) {
                    $coursesections[] = $section;
                }
                $course->sections = $coursesections;
                $finalcourses[] = $course;
            }

            return $finalcourses;
        } catch (Exception $e) {
            // Log exceptions and return empty array to prevent dashboard breakage.
            debugging('Grading Dashboard Service Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Compute SLA severity status (success, warning, danger) based on time age.
     *
     * @param int $timestamp Unix timestamp of submission or oldest submission.
     * @return string Severity class string: success, warning, or danger.
     */
    private static function compute_severity(int $timestamp): string {
        $age = time() - $timestamp;
        if ($age > 259200) {
            return 'danger';
        }
        if ($age > 86400) {
            return 'warning';
        }
        return 'success';
    }

    /**
     * Get sorting rank for a severity status (danger > warning > success).
     *
     * @param string $severity Severity status.
     * @return int Numeric severity rank.
     */
    private static function severity_rank(string $severity): int {
        $ranks = ['success' => 0, 'warning' => 1, 'danger' => 2];
        return $ranks[$severity] ?? 0;
    }
}
