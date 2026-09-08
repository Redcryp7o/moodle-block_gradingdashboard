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
 * Main Block Renderable.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\output;

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
