<?php
/**
 * Gallery Dynamic Tag for Elementor
 *
 * @package    CPCM
 * @subpackage CPCM/includes/elementor/dynamic-tags
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * CPCM Gallery Dynamic Tag
 */
class CPCM_Gallery_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

    /**
     * Get tag name
     */
    public function get_name() {
        return 'cpcm-gallery-field';
    }

    /**
     * Get tag title
     */
    public function get_title() {
        return __('معرض صور من الفيلدات', 'custom-page-content-manager');
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
            \Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY,
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
                'options' => $this->get_gallery_fields(),
                'default' => '',
            ]
        );
    }

    /**
     * Get available gallery fields for current page
     */
    private function get_gallery_fields() {
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
                // Only show multi_images fields
                if ($field['type'] === 'multi_images') {
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
        $image_ids_string = get_post_meta($post_id, 'cpcm_' . $field_key, true);
        
        if (empty($image_ids_string)) {
            return [];
        }

        $image_ids = explode(',', $image_ids_string);
        $gallery = [];

        foreach ($image_ids as $image_id) {
            $image_id = trim($image_id);
            if (!$image_id) {
                continue;
            }

            $image_url = wp_get_attachment_image_url($image_id, 'full');
            
            if ($image_url) {
                $gallery[] = [
                    'id' => $image_id,
                    'url' => $image_url,
                ];
            }
        }

        return $gallery;
    }
}
