<?php
/**
 * Plugin Name: Custom Page Content Manager
 * Plugin URI: https://portfolio-yossef-weal.netlify.app/
 * Description: Professional plugin to manage custom fields for WordPress pages with multilingual support
 * Version: 2.0.0
 * Author: Yossef Weal
 * Author URI: https://portfolio-yossef-weal.netlify.app/
 * Text Domain: custom-page-content-manager
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('CPCM_VERSION', '2.0.0');
define('CPCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CPCM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CPCM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_cpcm() {
    require_once CPCM_PLUGIN_DIR . 'includes/class-cpcm-activator.php';
    CPCM_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_cpcm() {
    require_once CPCM_PLUGIN_DIR . 'includes/class-cpcm-deactivator.php';
    CPCM_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_cpcm');
register_deactivation_hook(__FILE__, 'deactivate_cpcm');

/**
 * The core plugin class.
 */
require CPCM_PLUGIN_DIR . 'includes/class-cpcm-core.php';

/**
 * Begins execution of the plugin.
 */
function run_cpcm() {
    $plugin = new CPCM_Core();
    $plugin->run();
}
run_cpcm();
