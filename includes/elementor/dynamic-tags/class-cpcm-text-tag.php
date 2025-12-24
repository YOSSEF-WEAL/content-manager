<?php
/**
 * Text Dynamic Tag for Elementor
 *
 * @package    CPCM
 * @subpackage CPCM/includes/elementor/dynamic-tags
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * CPCM Text Dynamic Tag
 */
class CPCM_Text_Tag extends \Elementor\Core\DynamicTags\Tag {

    /**
     * Get tag name
     */
    public function get_name() {
        return 'cpcm-text-field';
    }

    /**
     * Get tag title
     */
    public function get_title() {
        return __('محتوى نصي من الفيلدات', 'custom-page-content-manager');
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
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
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
                'options' => $this->get_text_fields(),
                'default' => '',
            ]
        );
    }

    /**
     * Get available text fields for current page
     */
    private function get_text_fields() {
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
                // Only show text and longtext fields
                if (in_array($field['type'], ['text', 'longtext'])) {
                    $options[$key] = $field['name'] . ' (' . ucfirst($field['type']) . ')';
                }
            }
        }

        return $options;
    }

    /**
     * Render tag output
     */
    public function render() {
        $field_key = $this->get_settings('field_key');
        
        if (empty($field_key)) {
            return;
        }

        $post_id = 0;
        if (class_exists('\Elementor\Plugin')) {
            $post_id = \Elementor\Plugin::instance()->editor->get_post_id();
        }
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $value = get_post_meta($post_id, 'cpcm_' . $field_key, true);
        
        echo wp_kses_post($value);
    }
}
