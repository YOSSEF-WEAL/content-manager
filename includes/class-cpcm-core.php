<?php
/**
 * The core plugin class.
 *
 * @package    CPCM
 * @subpackage CPCM/includes
 */

class CPCM_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    2.0.0
     * @access   protected
     * @var      CPCM_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    2.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    2.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    2.0.0
     */
    public function __construct() {
        $this->version = CPCM_VERSION;
        $this->plugin_name = 'custom-page-content-manager';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    2.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once CPCM_PLUGIN_DIR . 'includes/class-cpcm-loader.php';
        require_once CPCM_PLUGIN_DIR . 'includes/class-cpcm-i18n.php';
        require_once CPCM_PLUGIN_DIR . 'admin/class-cpcm-admin.php';
        require_once CPCM_PLUGIN_DIR . 'public/class-cpcm-public.php';

        $this->loader = new CPCM_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    2.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new CPCM_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    2.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new CPCM_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_post_cpcm_save_fields', $plugin_admin, 'save_fields');
        $this->loader->add_action('admin_post_cpcm_add_field', $plugin_admin, 'add_field');
        $this->loader->add_action('admin_post_cpcm_delete_field', $plugin_admin, 'delete_field');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    2.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new CPCM_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    2.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     2.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     2.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
