<?php
/**
 * Define the internationalization functionality.
 *
 * @package    CPCM
 * @subpackage CPCM/includes
 */

class CPCM_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    2.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'custom-page-content-manager',
            false,
            dirname(CPCM_PLUGIN_BASENAME) . '/languages/'
        );
    }
}
