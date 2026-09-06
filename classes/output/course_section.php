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
 * Course Section Renderable.
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

/**
 * Class course_section.
 *
 * Models a single course section displaying pending grades count.
 */
class course_section implements renderable, templatable {

    /** @var int Section ID. */
    protected int $id;

    /** @var string Section Name. */
    protected string $name;

    /** @var int Pending grading count. */
    protected int $pendingcount;

    /** @var bool Expanded state indicator. */
    protected bool $expanded = false;

    /** @var string Severity. */
    protected string $severity;

    /** @var int Fresh count. */
    protected int $freshcount;

    /** @var int Waiting count. */
    protected int $waitingcount;

    /** @var int Overdue count. */
    protected int $overduecount;

    /** @var array Nested assignments. */
    protected array $assignments = [];

    /**
     * Constructor.
     *
     * @param stdClass $section Section data object.
     */
    public function __construct(stdClass $section) {
        $this->id = (int)$section->id;
        $this->name = $section->name;
        $this->pendingcount = (int)$section->pendingcount;
        $this->severity = $section->severity ?? 'success';
        $this->freshcount = (int)($section->freshcount ?? 0);
        $this->waitingcount = (int)($section->waitingcount ?? 0);
        $this->overduecount = (int)($section->overduecount ?? 0);
        $this->expanded = false;

        if (isset($section->assignments) && is_array($section->assignments)) {
            foreach ($section->assignments as $assignment) {
                // If any child assignment is expanded, this section is expanded.
                if (isset($assignment->expanded) && $assignment->expanded) {
                    $this->expanded = true;
                }
                $this->assignments[] = new course_assignment($assignment);
            }
        }
    }

    /**
     * Get expanded state.
     *
     * @return bool
     */
    public function is_expanded(): bool {
        return $this->expanded;
    }

    /**
     * Export data for Mustache template.
     *
     * @param renderer_base $output The renderer.
     * @return array Exported data.
     */
    public function export_for_template(renderer_base $output): array {
        $assignmentsdata = [];
        foreach ($this->assignments as $assignment) {
            $assignmentsdata[] = $assignment->export_for_template($output);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
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
            'expanded' => $this->expanded,
            'hasassignments' => !empty($assignmentsdata),
            'assignments' => $assignmentsdata,
        ];
    }
}
