<?php
/**
 * Services definition.
 *
 * @package    block_gradingdashboard
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_gradingdashboard_get_assignment_students' => [
        'classname'   => 'block_gradingdashboard\external\get_assignment_students',
        'description' => 'Get pending student submissions for an assignment',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'mod/assign:grade',
    ],
];
