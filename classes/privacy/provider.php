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
 * Privacy Subsystem implementation provider.
 *
 * @package    block_gradingdashboard
 * @category   privacy
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\privacy;

use core_privacy\local\metadata\null_provider;

/**
 * Privacy provider for the Grading Dashboard block.
 *
 * This block only reads and displays existing assignment submission data from core
 * Moodle tables. It does not store, transmit, or process any personal data of its own.
 */
class provider implements null_provider {
    /**
     * Returns the language string key that explains why no personal data is stored.
     *
     * @return string Language string identifier within block_gradingdashboard.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
