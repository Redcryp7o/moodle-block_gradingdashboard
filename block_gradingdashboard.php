<?php
/**
 * Grading Dashboard Block.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
