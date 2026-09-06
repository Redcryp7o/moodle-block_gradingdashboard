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
 * Course Student Renderable.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;

/**
 * Class course_student.
 *
 * Models a single student submission node.
 */
class course_student implements renderable, templatable {

    /** @var int Student User ID. */
    protected int $userid;

    /** @var int Submission ID. */
    protected int $submissionid;

    /** @var int Attempt Number. */
    protected int $attemptnumber;

    /** @var string Localized Full Name. */
    protected string $fullname;

    /** @var stdClass User database record (for rendering pictures). */
    protected stdClass $userrecord;

    /** @var string Formatted submission date. */
    protected string $submittedat;

    /** @var string Submission status. */
    protected string $status;

    /** @var bool Is submission late? */
    protected bool $islate;

    /** @var int Course module ID (assignment context). */
    protected int $cmid;

    /** @var string Severity. */
    protected string $severity;

    /** @var int Unix timestamp of submission. */
    protected int $submittedattimestamp;

    /**
     * Constructor.
     *
     * @param stdClass $student Student data object.
     * @param int $cmid Course module ID of the assignment.
     */
    public function __construct(stdClass $student, int $cmid) {
        $this->userid = (int)$student->userid;
        $this->submissionid = (int)$student->submissionid;
        $this->attemptnumber = (int)$student->attemptnumber;
        $this->fullname = $student->fullname;
        $this->userrecord = $student->userrecord;
        $this->submittedat = $student->submittedat;
        $this->status = $student->status;
        $this->islate = (bool)$student->islate;
        $this->severity = $student->severity ?? 'success';
        $this->submittedattimestamp = (int)($student->submittedattimestamp ?? 0);
        $this->cmid = $cmid;
    }

    /**
     * Export data for Mustache template.
     *
     * @param renderer_base $output The renderer.
     * @return array Exported data.
     */
    public function export_for_template(renderer_base $output): array {
        global $OUTPUT;

        // Render localized profile picture using core Moodle output API.
        $profileimage = $OUTPUT->user_picture($this->userrecord, [
            'size' => 32,
            'link' => true,
            'visibletoscreenreaders' => false,
        ]);

        // Construct Moodle's native grading screen URL for this student.
        $gradeurl = new moodle_url('/mod/assign/view.php', [
            'id' => $this->cmid,
            'action' => 'grader',
            'userid' => $this->userid,
        ]);

        return [
            'userid'           => $this->userid,
            'submissionid'     => $this->submissionid,
            'attemptnumber'    => $this->attemptnumber,
            'fullname'         => $this->fullname,
            'profileimage'     => $profileimage,
            'submittedat'      => $this->submittedat,
            'status'           => $this->status,
            'islate'           => $this->islate,
            'severity'         => $this->severity,
            'submittedattimestamp' => $this->submittedattimestamp,
            'gradeurl'         => $gradeurl->out(false),
            'submissionstatus' => get_string('submissionstatus_' . $this->status, 'mod_assign'),
        ];
    }
}
