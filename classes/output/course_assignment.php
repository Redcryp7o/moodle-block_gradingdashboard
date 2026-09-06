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
 * Course Assignment Renderable.
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
 * Class course_assignment.
 *
 * Models a single assignment node displaying pending grades count.
 */
class course_assignment implements renderable, templatable {

    /** @var int Assignment ID. */
    protected int $id;

    /** @var int Course Module ID. */
    protected int $cmid;

    /** @var int Core Assignment ID. */
    protected int $assignmentid;

    /** @var string Assignment Title. */
    protected string $title;

    /** @var int Pending grading count. */
    protected int $pendingcount;

    /** @var string Severity. */
    protected string $severity;

    /** @var int Fresh count. */
    protected int $freshcount;

    /** @var int Waiting count. */
    protected int $waitingcount;

    /** @var int Overdue count. */
    protected int $overduecount;

    /** @var array Child student nodes list. */
    protected array $children = [];

    /** @var bool Expanded state indicator. */
    protected bool $expanded = false;

    /**
     * Constructor.
     *
     * @param stdClass $assignment Assignment data object.
     */
    public function __construct(stdClass $assignment) {
        $this->id = (int)$assignment->id;
        $this->cmid = (int)$assignment->cmid;
        $this->assignmentid = (int)$assignment->assignmentid;
        $this->title = $assignment->title;
        $this->pendingcount = (int)$assignment->pendingcount;
        $this->severity = $assignment->severity ?? 'success';
        $this->freshcount = (int)($assignment->freshcount ?? 0);
        $this->waitingcount = (int)($assignment->waitingcount ?? 0);
        $this->overduecount = (int)($assignment->overduecount ?? 0);
        if (isset($assignment->expanded)) {
            $this->expanded = (bool)$assignment->expanded;
        }
        if (isset($assignment->children) && is_array($assignment->children)) {
            foreach ($assignment->children as $student) {
                $this->children[] = new course_student($student, $this->cmid);
            }
        }
    }

    /**
     * Export data for Mustache template.
     *
     * @param renderer_base $output The renderer.
     * @return array Exported data.
     */
    public function export_for_template(renderer_base $output): array {
        global $PAGE;

        $childrendata = [];
        foreach ($this->children as $child) {
            $childrendata[] = $child->export_for_template($output);
        }

        // Generate stateless URL toggles for server-side loading.
        if ($this->expanded) {
            $toggleurl = new moodle_url($PAGE->url);
            $toggleurl->remove_params('expandassign');
        } else {
            $toggleurl = new moodle_url($PAGE->url, ['expandassign' => $this->cmid]);
        }
        // UX fix: set URL anchor so browser scrolls back to the expanded assignment on page reload.
        $toggleurl->set_anchor('block-gradingdashboard-assign-' . $this->cmid);

        return [
            'id' => $this->id,
            'cmid' => $this->cmid,
            'assignmentid' => $this->assignmentid,
            'title' => $this->title,
            'pendingcount' => $this->pendingcount,
            'pendingarialabel' => get_string('pendingcount', 'block_gradingdashboard', $this->pendingcount),
            'pendingtooltip' => get_string('pendingtooltip', 'block_gradingdashboard', (object) [
                'pending' => $this->pendingcount,
                'fresh' => get_string('severity_fresh', 'block_gradingdashboard'),
                'freshcount' => $this->freshcount,
                'waiting' => get_string('severity_waiting', 'block_gradingdashboard'),
                'waitingcount' => $this->waitingcount,
                'overdue' => get_string('severity_overdue', 'block_gradingdashboard'),
                'overduecount' => $this->overduecount,
            ]),
            'severity' => $this->severity,
            'freshcount' => $this->freshcount,
            'waitingcount' => $this->waitingcount,
            'overduecount' => $this->overduecount,
            'icon' => $output->pix_icon('monologo', get_string('pluginname', 'mod_assign'), 'mod_assign', [
                'class' => 'activityicon block-gradingdashboard-activity-icon',
                'aria-hidden' => 'true'
            ]),
            'expanded' => $this->expanded,
            'haschildren' => !empty($childrendata),
            'children' => $childrendata,
            'toggleurl' => $toggleurl->out(false),
            'assignmenturl' => (new moodle_url('/mod/assign/view.php', [
                'id' => $this->cmid,
                'action' => 'grading'
            ]))->out(false),
        ];
    }
}
