<?php
/**
 * Course Card Renderable.
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
 * Class course_card.
 *
 * Models a single course card displaying pending grades count.
 */
class course_card implements renderable, templatable {

    /** @var int Course ID. */
    protected int $id;

    /** @var string Course Fullname. */
    protected string $fullname;

    /** @var string Course Shortname. */
    protected string $shortname = '';

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

    /** @var array Nested course sections. */
    protected array $sections = [];

    /**
     * Constructor.
     *
     * @param stdClass $course Course data object.
     */
    public function __construct(stdClass $course) {
        $this->id = (int)$course->id;
        $this->fullname = $course->fullname;
        $this->shortname = $course->shortname ?? '';
        $this->pendingcount = (int)$course->pendingcount;
        $this->severity = $course->severity ?? 'success';
        $this->freshcount = (int)($course->freshcount ?? 0);
        $this->waitingcount = (int)($course->waitingcount ?? 0);
        $this->overduecount = (int)($course->overduecount ?? 0);
        $this->expanded = false;

        if (isset($course->sections) && is_array($course->sections)) {
            foreach ($course->sections as $section) {
                $sectionobj = new course_section($section);
                // If any child section is expanded, this course is expanded.
                if ($sectionobj->is_expanded()) {
                    $this->expanded = true;
                }
                $this->sections[] = $sectionobj;
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
        $sectionsdata = [];
        foreach ($this->sections as $section) {
            $sectionsdata[] = $section->export_for_template($output);
        }

        // Derive the icon letter: first non-whitespace character of shortname, else fullname.
        $iconletter = strtoupper(mb_substr(trim($this->shortname ?: $this->fullname), 0, 1, 'UTF-8'));

        return [
            'id'           => $this->id,
            'fullname'     => $this->fullname,
            'iconletter'   => $iconletter,
            'severity'     => $this->severity,
            'pendingcount' => $this->pendingcount,
            'freshcount'   => $this->freshcount,
            'waitingcount' => $this->waitingcount,
            'overduecount' => $this->overduecount,
            'expanded'     => $this->expanded,
            'courseurl'    => (new moodle_url('/course/view.php', ['id' => $this->id]))->out(false),
            'hassections'  => !empty($sectionsdata),
            'sections'     => $sectionsdata,
        ];
    }
}
