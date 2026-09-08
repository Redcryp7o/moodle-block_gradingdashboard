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
 * Grading Dashboard Block.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block gradingdashboard class.
 *
 * Displays all pending student submissions grouped by course, section, and assignment.
 * Only shows courses where the current user has the mod/assign:grade capability.
 */
class block_gradingdashboard extends block_base {
    /**
     * Initialise the block title.
     *
     * @return void
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_gradingdashboard');
    }

    /**
     * Return the block content.
     *
     * Returns null if the user does not have any courses with pending grading.
     * The result is cached in $this->content after first call.
     *
     * @return stdClass|null Block content object, or null on error.
     */
    public function get_content(): ?stdClass {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $dataprovider = new \block_gradingdashboard\data_provider();
        $courses = $dataprovider->get_courses_with_pending_grading();

        $renderer = $this->page->get_renderer('block_gradingdashboard');
        $blockrenderable = new \block_gradingdashboard\output\block($courses);
        $this->content->text = $renderer->render($blockrenderable);

        return $this->content;
    }

    /**
     * Define which page formats the block can be added to.
     *
     * @return array Associative array of format => allowed.
     */
    public function applicable_formats(): array {
        return [
            'all'       => false,
            'my'        => true,
            'site-index' => true,
            'course-view' => false,
        ];
    }

    /**
     * Allow multiple instances of this block on the same page.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }
}
