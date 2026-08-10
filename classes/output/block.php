<?php
/**
 * Main Block Renderable.
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

/**
 * Class block.
 *
 * Main renderable block that holds course cards or empty state information.
 */
class block implements renderable, templatable {

    /** @var array List of course cards. */
    protected array $coursecards = [];

    /**
     * Constructor.
     *
     * @param array $courses Array of course records.
     */
    public function __construct(array $courses) {
        foreach ($courses as $course) {
            $this->coursecards[] = new course_card($course);
        }
    }

    /**
     * Export data for Mustache template.
     *
     * @param renderer_base $output The renderer.
     * @return array Exported data.
     */
    public function export_for_template(renderer_base $output): array {
        $cardsdata = [];
        foreach ($this->coursecards as $card) {
            $cardsdata[] = $card->export_for_template($output);
        }

        return [
            'hascourses' => !empty($cardsdata),
            'courses' => $cardsdata,
        ];
    }
}
