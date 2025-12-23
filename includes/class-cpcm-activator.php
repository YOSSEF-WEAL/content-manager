<?php
/**
 * Fired during plugin activation.
 *
 * @package    CPCM
 * @subpackage CPCM/includes
 */

class CPCM_Activator {

    /**
     * Plugin activation logic.
     *
     * @since    2.0.0
     */
    public static function activate() {
        // Add any activation logic here (e.g., create database tables, set default options)
        // For now, we'll just flush rewrite rules
        flush_rewrite_rules();
    }
}
