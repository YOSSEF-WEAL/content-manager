<?php
/**
 * Image Dynamic Tag for Elementor
 *
 * @package    CPCM
 * @subpackage CPCM/includes/elementor/dynamic-tags
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * CPCM Image Dynamic Tag
 */
class CPCM_Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

    /**
     * Get tag name
     */
    public function get_name() {
        return 'cpcm-image-field';
    }

    /**
     * Get tag title
     */
    public function get_title() {
        return __('صورة من الفيلدات', 'custom-page-content-manager');
    }

    /**
     * Get tag group
     */
    public function get_group() {
        return 'cpcm-fields';
    }

    /**
     * Get tag categories
     */
    public function get_categories() {
        return [
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::BASE_GROUP,
        ];
    }

    /**
     * Register controls
     */
    protected function register_controls() {
        $this->add_control(
            'field_key',
            [
                'label' => __('اختر الفيلد', 'custom-page-content-manager'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_image_fields(),
                'default' => '',
            ]
        );
    }

    /**
     * Get available image fields for current page
     */
    private function get_image_fields() {
        $options = ['' => __('-- اختر فيلد --', 'custom-page-content-manager')];
        
        $post_id = 0;
        if (class_exists('\Elementor\Plugin')) {
            $post_id = \Elementor\Plugin::instance()->editor->get_post_id();
        }
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        if (!$post_id) {
            $post_id = isset($_REQUEST['post']) ? intval($_REQUEST['post']) : (isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0);
        }

        if (!$post_id) {
            return $options;
        }

        $fields = get_post_meta($post_id, '_cpcm_fields', true);
        
        if (is_array($fields)) {
            foreach ($fields as $key => $field) {
                // Only show single_image fields
                if ($field['type'] === 'single_image') {
                    $options[$key] = $field['name'];
                }
            }
        }

        return $options;
    }

    /**
     * Get tag value
     */
    public function get_value(array $options = []) {
        $field_key = $this->get_settings('field_key');
        
        if (empty($field_key)) {
            return [];
        }

        $post_id = 0;
        if (class_exists('\Elementor\Plugin')) {
            $post_id = \Elementor\Plugin::instance()->editor->get_post_id();
        }
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $image_id = get_post_meta($post_id, 'cpcm_' . $field_key, true);
        
        if (!$image_id) {
            return [];
        }

        $image_url = wp_get_attachment_image_url($image_id, 'full');
        
        if (!$image_url) {
            return [];
        }

        return [
            'id' => $image_id,
            'url' => $image_url,
        ];
    }
}
