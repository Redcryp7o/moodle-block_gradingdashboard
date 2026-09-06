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
 * Language strings for the Grading Dashboard block.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core block strings.
$string['pluginname'] = 'Grading Dashboard';
$string['gradingdashboard:addinstance'] = 'Add a new Grading Dashboard block';
$string['gradingdashboard:myaddinstance'] = 'Add a new Grading Dashboard block to the Dashboard';

// Privacy.
$string['privacy:metadata'] = 'The Grading Dashboard block reads existing assignment submission data to display grading workload. It does not store, modify, or transmit any personal data of its own.';

// Block content strings.
$string['emptyqueue'] = 'All caught up! No assignments are currently pending grading.';
$string['allcaughtup'] = 'All caught up — no pending submissions.';
$string['grade'] = 'Grade';
$string['late'] = 'Late';
$string['gradesubmissionfor'] = 'Grade submission for {$a}';

// Severity labels (used in tooltips).
$string['severity_fresh'] = 'Fresh (< 24h)';
$string['severity_waiting'] = 'Waiting (1–3 days)';
$string['severity_overdue'] = 'Overdue (> 3 days)';

// Pending badge accessibility / tooltip strings.
$string['pendingcount'] = 'Pending: {$a}';
$string['pendingtooltip'] = 'Pending: {$a->pending}<br>🟢 {$a->fresh}: {$a->freshcount}<br>🟠 {$a->waiting}: {$a->waitingcount}<br>🔴 {$a->overdue}: {$a->overduecount}';

// Accessibility strings for expand / collapse controls.
$string['expandcourse'] = 'Expand course: {$a}';
$string['expandsection'] = 'Expand section: {$a}';
$string['expandassignment'] = 'Expand assignment: {$a}';
