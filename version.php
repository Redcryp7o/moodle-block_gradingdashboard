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
 * Version details.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026090600;            // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2026042000;            // Requires Moodle 5.2+ (build 2026042000).
$plugin->component = 'block_gradingdashboard'; // Full name of the plugin (Frankenstyle).
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.1';
$plugin->supported = [502, 502];            // Supported Moodle major version range (5.2).
