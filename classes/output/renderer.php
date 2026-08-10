<?php
/**
 * Block Renderer.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Class renderer.
 *
 * Custom plugin renderer that handles output rendering via Mustache templates.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the main block content.
     *
     * @param block $block The block renderable.
     * @return string Rendered HTML.
     */
    protected function render_block(block $block): string {
        return $this->render_from_template('block_gradingdashboard/block', $block->export_for_template($this));
    }
}
