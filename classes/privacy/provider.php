<?php
/**
 * Privacy Subsystem implementation provider.
 *
 * @package    block_gradingdashboard
 * @category   privacy
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gradingdashboard\privacy;

defined('MOODLE_INTERNAL') || die();

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
