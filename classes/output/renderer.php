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
