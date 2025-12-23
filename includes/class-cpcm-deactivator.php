<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    CPCM
 * @subpackage CPCM/includes
 */

class CPCM_Deactivator {

    /**
     * Plugin deactivation logic.
     *
     * @since    2.0.0
     */
    public static function deactivate() {
        // Add any deactivation logic here
        flush_rewrite_rules();
    }
}
