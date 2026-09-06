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
 * Grading Dashboard Data Provider.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard;

defined('MOODLE_INTERNAL') || die();

use block_gradingdashboard\service\grading_dashboard_service;

/**
 * Class data_provider.
 *
 * Fetches course data and pending grading counts efficiently.
 */
class data_provider {

    /** @var grading_dashboard_service The service instance. */
    protected grading_dashboard_service $service;

    /**
     * Constructor.
     *
     * @param grading_dashboard_service|null $service Optional service instance.
     */
    public function __construct(?grading_dashboard_service $service = null) {
        $this->service = $service ?? new grading_dashboard_service();
    }

    /**
     * Get all courses with assignments containing pending submissions.
     *
     * Only returns courses where the current user has permission to grade.
     * Respects course visibility and capability checks.
     *
     * @return array Array of stdClass objects containing course id, fullname, and pendingcount.
     */
    public function get_courses_with_pending_grading(): array {
        return $this->service->get_courses_with_pending_grading();
    }
}
