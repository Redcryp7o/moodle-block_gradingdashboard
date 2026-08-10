<?php
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

// Accessibility strings for expand / collapse controls.
$string['expandcourse'] = 'Expand course: {$a}';
$string['expandsection'] = 'Expand section: {$a}';
$string['expandassignment'] = 'Expand assignment: {$a}';
