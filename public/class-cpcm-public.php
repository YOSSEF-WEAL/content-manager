<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    CPCM
 * @subpackage CPCM/public
 */

class CPCM_Public {

    /**
     * The ID of this plugin.
     *
     * @since    2.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    2.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    2.0.0
     */
    public function enqueue_styles() {
        // Add public styles if needed
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    2.0.0
     */
    public function enqueue_scripts() {
        // Add public scripts if needed
    }

    /**
     * Register shortcodes.
     *
     * @since    2.0.0
     */
    public function register_shortcodes() {
        add_shortcode('cpcm_field', array($this, 'display_field_shortcode'));
    }

    /**
     * Display field shortcode.
     *
     * @since    2.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The field value or image HTML.
     */
    public function display_field_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'field' => '',
            'size' => 'full',  // Image size: thumbnail, medium, large, full
            'class' => ''      // Additional CSS class
        ), $atts);
        
        if (empty($atts['id']) || empty($atts['field'])) {
            return '';
        }
        
        // Get field value
        $value = get_post_meta($atts['id'], 'cpcm_' . $atts['field'], true);
        
        if (empty($value)) {
            return '';
        }
        
        // Get field type
        $fields = get_post_meta($atts['id'], '_cpcm_fields', true);
        $field_key = sanitize_title($atts['field']);
        
        if (is_array($fields) && isset($fields[$field_key])) {
            $field_type = $fields[$field_key]['type'];
            
            // Handle single image
            if ($field_type === 'single_image' && is_numeric($value)) {
                $image_url = wp_get_attachment_image_url($value, $atts['size']);
                if ($image_url) {
                    $alt = get_post_meta($value, '_wp_attachment_image_alt', true);
                    $class = !empty($atts['class']) ? ' class="' . esc_attr($atts['class']) . '"' : '';
                    return '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt) . '"' . $class . '>';
                }
            }
            
            // Handle multiple images
            if ($field_type === 'multi_images') {
                $image_ids = explode(',', $value);
                $output = '<div class="cpcm-image-gallery' . (!empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '') . '">';
                
                foreach ($image_ids as $image_id) {
                    if (is_numeric($image_id)) {
                        $image_url = wp_get_attachment_image_url($image_id, $atts['size']);
                        if ($image_url) {
                            $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt) . '">';
                        }
                    }
                }
                
                $output .= '</div>';
                return $output;
            }
        }
        
        // Return text value for other field types
        return wp_kses_post($value);
    }
}
