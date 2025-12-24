<?php
/**
 * Elementor Widget: CPCM Field
 *
 * @package    CPCM
 * @subpackage CPCM/includes/elementor/widgets
 */

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * CPCM Field Widget
 */
class CPCM_Field_Widget extends Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'cpcm_field_widget';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('حقل من Content Manager', 'custom-page-content-manager');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-text-area';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['cpcm-category'];
    }

    /**
     * Register controls
     */
    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('المحتوى', 'custom-page-content-manager'),
            ]
        );

        $this->add_control(
            'field_key',
            [
                'label' => __('اختر الفيلد', 'custom-page-content-manager'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_available_fields(),
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get available fields for current page
     */
    private function get_available_fields() {
        $options = ['' => __('-- اختر فيلد --', 'custom-page-content-manager')];
        
        $post_id = 0;
        
        // 1. Try Elementor Main ID
        if (class_exists('\Elementor\Plugin')) {
            $post_id = \Elementor\Plugin::instance()->editor->get_post_id();
        }

        // 2. Fallback to global ID
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        // 3. Last resort fallback from request
        if (!$post_id) {
            $post_id = isset($_REQUEST['post']) ? intval($_REQUEST['post']) : (isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0);
        }

        if (!$post_id) {
            return $options;
        }

        $fields = get_post_meta($post_id, '_cpcm_fields', true);
        
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $key => $field) {
                $options[$key] = $field['name'] . ' (' . ucfirst($field['type']) . ')';
            }
        }

        return $options;
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $field_key = $settings['field_key'];

        if (empty($field_key)) {
            return;
        }

        $post_id = get_the_ID();
        $value = get_post_meta($post_id, 'cpcm_' . $field_key, true);

        if (empty($value)) {
            return;
        }

        // Check field type to handle output
        $fields = get_post_meta($post_id, '_cpcm_fields', true);
        $field_type = isset($fields[$field_key]['type']) ? $fields[$field_key]['type'] : 'text';

        echo '<div class="cpcm-field-output">';
        
        if ($field_type === 'single_image') {
            echo wp_get_attachment_image($value, 'full');
        } elseif ($field_type === 'multi_images') {
            $ids = explode(',', $value);
            echo '<div class="cpcm-gallery-output">';
            foreach ($ids as $id) {
                echo wp_get_attachment_image(trim($id), 'medium');
            }
            echo '</div>';
        } else {
            echo wp_kses_post($value);
        }

        echo '</div>';
    }
}
