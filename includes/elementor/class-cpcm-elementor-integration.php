<?php
/**
 * Elementor Integration for Custom Page Content Manager
 *
 * @package    CPCM
 * @subpackage CPCM/includes/elementor
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Main Elementor Integration Class
 */
class CPCM_Elementor_Integration {

    /**
     * Initialize the class
     */
    public function __construct() {
        // Register Dynamic Tags and Group
        add_action('elementor/dynamic_tags/register', array($this, 'register_dynamic_tags'), 20);
        
        // Register Widget Category
        add_action('elementor/elements/categories_registered', array($this, 'register_widget_categories'));

        // Register Widgets
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
    }

    /**
     * Register Custom Widget Categories
     */
    public function register_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'cpcm-category',
            [
                'title' => __('فيلدات Content Manager', 'custom-page-content-manager'),
                'icon' => 'eicon-font',
            ]
        );
    }

    /**
     * Register Custom Widgets
     *
     * @param object $widgets_manager Elementor widgets manager
     */
    public function register_widgets($widgets_manager) {
        require_once CPCM_PLUGIN_DIR . 'includes/elementor/widgets/class-cpcm-field-widget.php';
        $widgets_manager->register(new \CPCM_Field_Widget());
    }

    /**
     * Register Custom Dynamic Tags and Groups
     *
     * @param object $dynamic_tags_manager Elementor dynamic tags manager
     */
    public function register_dynamic_tags($dynamic_tags_manager) {
        // 1. Register Group
        $dynamic_tags_manager->register_group('cpcm-fields', [
            'title' => __('محتوى من الفيلدات', 'custom-page-content-manager'),
        ]);

        // 2. Load Classes
        require_once CPCM_PLUGIN_DIR . 'includes/elementor/dynamic-tags/class-cpcm-text-tag.php';
        require_once CPCM_PLUGIN_DIR . 'includes/elementor/dynamic-tags/class-cpcm-number-tag.php';
        require_once CPCM_PLUGIN_DIR . 'includes/elementor/dynamic-tags/class-cpcm-image-tag.php';
        require_once CPCM_PLUGIN_DIR . 'includes/elementor/dynamic-tags/class-cpcm-gallery-tag.php';

        // 3. Register Tags
        $dynamic_tags_manager->register(new CPCM_Text_Tag());
        $dynamic_tags_manager->register(new CPCM_Number_Tag());
        $dynamic_tags_manager->register(new CPCM_Image_Tag());
        $dynamic_tags_manager->register(new CPCM_Gallery_Tag());
    }

    /**
     * Admin notice if Elementor is not installed
     */
    public function elementor_missing_notice() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" for Elementor integration.', 'custom-page-content-manager'),
            '<strong>' . esc_html__('Custom Page Content Manager', 'custom-page-content-manager') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'custom-page-content-manager') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }
}
