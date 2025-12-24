<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    CPCM
 * @subpackage CPCM/admin
 */

class CPCM_Admin {

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
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    2.0.0
     */
    public function enqueue_styles() {
        if (isset($_GET['page']) && $_GET['page'] === 'page-content-manager') {
            wp_enqueue_style(
                $this->plugin_name . '-core',
                CPCM_PLUGIN_URL . 'admin/css/cpcm-core.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-fields',
                CPCM_PLUGIN_URL . 'admin/css/cpcm-fields.css',
                array($this->plugin_name . '-core'),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-modal',
                CPCM_PLUGIN_URL . 'admin/css/cpcm-modal.css',
                array($this->plugin_name . '-core'),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-notifications',
                CPCM_PLUGIN_URL . 'admin/css/cpcm-notifications.css',
                array($this->plugin_name . '-core'),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    2.0.0
     */
    public function enqueue_scripts() {
        if (isset($_GET['page']) && $_GET['page'] === 'page-content-manager') {
            // Enqueue WordPress media library
            wp_enqueue_media();
            
            wp_enqueue_script(
                $this->plugin_name,
                CPCM_PLUGIN_URL . 'admin/js/cpcm-admin.js',
                array('jquery', 'media-upload', 'media-views'),
                $this->version,
                true  // Load in footer
            );
            
            wp_localize_script($this->plugin_name, 'cpcmAdmin', array(
                'confirmDelete' => __('Are you sure you want to delete this field? This will also delete its value!', 'custom-page-content-manager'),
                'copiedToClipboard' => __('Copied to clipboard!', 'custom-page-content-manager')
            ));
        }
    }

    /**
     * Add admin menu.
     *
     * @since    2.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Page Content Manager', 'custom-page-content-manager'),
            __('Page Content', 'custom-page-content-manager'),
            'manage_options',
            'page-content-manager',
            array($this, 'display_admin_page'),
            'dashicons-edit-page',
            25
        );
    }

    /**
     * Display the admin page.
     *
     * @since    2.0.0
     */
    public function display_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if we're editing a specific page
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['page_id'])) {
            $this->display_edit_page();
        } else {
            $this->display_pages_list();
        }
    }

    /**
     * Display the list of pages.
     *
     * @since    2.0.0
     */
    private function display_pages_list() {
        $pages = get_pages(array(
            'sort_column' => 'post_title',
            'sort_order' => 'ASC'
        ));

        include CPCM_PLUGIN_DIR . 'admin/partials/cpcm-admin-list.php';
    }

    /**
     * Display the edit page for managing fields.
     *
     * @since    2.0.0
     */
    private function display_edit_page() {
        $page_id = intval($_GET['page_id']);
        $page = get_post($page_id);
        
        if (!$page || $page->post_type !== 'page') {
            echo '<div class="wrap"><h1>' . esc_html__('Page not found', 'custom-page-content-manager') . '</h1></div>';
            return;
        }

        // Get custom fields
        $fields = get_post_meta($page_id, '_cpcm_fields', true);
        if (!is_array($fields)) {
            $fields = array();
        }

        include CPCM_PLUGIN_DIR . 'admin/partials/cpcm-admin-edit.php';
    }

    /**
     * Save fields via admin-post action.
     *
     * @since    2.0.0
     */
    public function save_fields() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'custom-page-content-manager'));
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_die(__('Invalid page ID.', 'custom-page-content-manager'));
        }

        check_admin_referer('cpcm_save_fields_' . $page_id);

        // 1. Rebuild Field Registry
        $new_fields_registry = array();
        if (isset($_POST['cpcm_field_registry']) && is_array($_POST['cpcm_field_registry'])) {
            foreach ($_POST['cpcm_field_registry'] as $field_key => $field_data) {
                $new_fields_registry[sanitize_key($field_key)] = array(
                    'name' => sanitize_text_field($field_data['name']),
                    'type' => sanitize_text_field($field_data['type'])
                );
            }
        }
        
        // Save the updated registry
        update_post_meta($page_id, '_cpcm_fields', $new_fields_registry);

        // 2. Save Individual Field Contents
        if (isset($_POST['cpcm_fields']) && is_array($_POST['cpcm_fields'])) {
            foreach ($_POST['cpcm_fields'] as $field_key => $field_value) {
                // Ensure the field exists in our new registry before saving its value
                if (isset($new_fields_registry[$field_key])) {
                    $field_type = $new_fields_registry[$field_key]['type'];
                    
                    // Sanitize based on type if needed
                    if ($field_type === 'longtext') {
                        $sanitized_value = sanitize_textarea_field($field_value);
                    } else {
                        $sanitized_value = sanitize_text_field($field_value);
                    }
                    
                    update_post_meta($page_id, 'cpcm_' . sanitize_key($field_key), $sanitized_value);
                }
            }
        }

        // 3. Cleanup: Remove meta for fields that were deleted from the registry
        // Get all post meta keys
        $all_meta = get_post_meta($page_id);
        foreach ($all_meta as $meta_key => $meta_value) {
            if (strpos($meta_key, 'cpcm_') === 0 && $meta_key !== '_cpcm_fields') {
                $actual_key = substr($meta_key, 5); // remove 'cpcm_'
                if (!isset($new_fields_registry[$actual_key])) {
                    delete_post_meta($page_id, $meta_key);
                }
            }
        }

        wp_redirect(add_query_arg(array(
            'page' => 'page-content-manager',
            'action' => 'edit',
            'page_id' => $page_id,
            'message' => 'saved'
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Add a new field via admin-post action.
     *
     * @since    2.0.0
     */
    public function add_field() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'custom-page-content-manager'));
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_die(__('Invalid page ID.', 'custom-page-content-manager'));
        }

        check_admin_referer('cpcm_add_field_' . $page_id);

        $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
        $field_type = isset($_POST['field_type']) ? sanitize_text_field($_POST['field_type']) : '';
        
        $message = 'error';
        
        if (!empty($field_name)) {
            $fields = get_post_meta($page_id, '_cpcm_fields', true);
            if (!is_array($fields)) {
                $fields = array();
            }
            
            $field_key = sanitize_title($field_name);
            
            if (!isset($fields[$field_key])) {
                $fields[$field_key] = array(
                    'name' => $field_name,
                    'type' => $field_type
                );
                
                update_post_meta($page_id, '_cpcm_fields', $fields);
                
                // Save Field Content
                $field_value = '';
                switch ($field_type) {
                    case 'text':
                        $field_value = isset($_POST['field_value_text']) ? sanitize_text_field($_POST['field_value_text']) : '';
                        break;
                    case 'longtext':
                        $field_value = isset($_POST['field_value_longtext']) ? sanitize_textarea_field($_POST['field_value_longtext']) : '';
                        break;
                    case 'number':
                        $field_value = isset($_POST['field_value_number']) ? sanitize_text_field($_POST['field_value_number']) : '';
                        break;
                    case 'single_image':
                        $field_value = isset($_POST['field_value_image']) ? sanitize_text_field($_POST['field_value_image']) : '';
                        break;
                    case 'multi_images':
                        $field_value = isset($_POST['field_value_gallery']) ? sanitize_text_field($_POST['field_value_gallery']) : '';
                        break;
                }
                
                if ($field_value !== '') {
                    update_post_meta($page_id, 'cpcm_' . $field_key, $field_value);
                }

                $message = 'added';
            } else {
                $message = 'exists';
            }
        }

        wp_redirect(add_query_arg(array(
            'page' => 'page-content-manager',
            'action' => 'edit',
            'page_id' => $page_id,
            'message' => $message
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Delete a field via admin-post action.
     *
     * @since    2.0.0
     */
    public function delete_field() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'custom-page-content-manager'));
        }

        $page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;
        $field_key = isset($_GET['field_key']) ? sanitize_key($_GET['field_key']) : '';
        
        if (!$page_id || !$field_key) {
            wp_die(__('Invalid parameters.', 'custom-page-content-manager'));
        }

        check_admin_referer('cpcm_delete_field_' . $page_id . '_' . $field_key);

        $fields = get_post_meta($page_id, '_cpcm_fields', true);
        if (is_array($fields) && isset($fields[$field_key])) {
            unset($fields[$field_key]);
            update_post_meta($page_id, '_cpcm_fields', $fields);
            delete_post_meta($page_id, 'cpcm_' . $field_key);
        }

        wp_redirect(add_query_arg(array(
            'page' => 'page-content-manager',
            'action' => 'edit',
            'page_id' => $page_id,
            'message' => 'deleted'
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Edit a field via admin-post action.
     *
     * @since    2.1.0
     */
    public function edit_field() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'custom-page-content-manager'));
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_die(__('Invalid page ID.', 'custom-page-content-manager'));
        }

        check_admin_referer('cpcm_edit_field_' . $page_id);

        $field_key = isset($_POST['field_key']) ? sanitize_text_field($_POST['field_key']) : '';
        $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
        $field_type = isset($_POST['field_type']) ? sanitize_text_field($_POST['field_type']) : '';
        
        $message = 'error';

        if (!empty($field_key) && !empty($field_name) && !empty($field_type)) {
            $fields = get_post_meta($page_id, '_cpcm_fields', true);
            if (!is_array($fields)) {
                $fields = array();
            }

            if (isset($fields[$field_key])) {
                // Update field details
                $fields[$field_key]['name'] = $field_name;
                $fields[$field_key]['type'] = $field_type;

                update_post_meta($page_id, '_cpcm_fields', $fields);

                // Update Field Content
                $field_value = '';
                switch ($field_type) {
                    case 'text':
                        $field_value = isset($_POST['field_value_text']) ? sanitize_text_field($_POST['field_value_text']) : '';
                        break;
                    case 'longtext':
                        $field_value = isset($_POST['field_value_longtext']) ? sanitize_textarea_field($_POST['field_value_longtext']) : '';
                        break;
                    case 'number':
                        $field_value = isset($_POST['field_value_number']) ? sanitize_text_field($_POST['field_value_number']) : '';
                        break;
                    case 'single_image':
                        $field_value = isset($_POST['field_value_image']) ? sanitize_text_field($_POST['field_value_image']) : '';
                        break;
                    case 'multi_images':
                        $field_value = isset($_POST['field_value_gallery']) ? sanitize_text_field($_POST['field_value_gallery']) : '';
                        break;
                }
                
                update_post_meta($page_id, 'cpcm_' . $field_key, $field_value);

                $message = 'saved';
            }
        }

        wp_redirect(add_query_arg(array(
            'page' => 'page-content-manager',
            'action' => 'edit',
            'page_id' => $page_id,
            'message' => $message
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Import fields from another post (translation).
     *
     * @since    2.2.0
     */
    public function import_fields() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'custom-page-content-manager'));
        }

        $target_page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $source_page_id = isset($_POST['source_page_id']) ? intval($_POST['source_page_id']) : 0;

        if (!$target_page_id || !$source_page_id) {
            wp_die(__('Invalid parameters.', 'custom-page-content-manager'));
        }

        check_admin_referer('cpcm_import_fields_' . $target_page_id);

        $source_fields = get_post_meta($source_page_id, '_cpcm_fields', true);
        if (!is_array($source_fields)) {
            $source_fields = array();
        }

        if (empty($source_fields)) {
            wp_redirect(add_query_arg(array(
                'page' => 'page-content-manager',
                'action' => 'edit',
                'page_id' => $target_page_id,
                'message' => 'no_source_fields'
            ), admin_url('admin.php')));
            exit;
        }

        $target_fields = get_post_meta($target_page_id, '_cpcm_fields', true);
        if (!is_array($target_fields)) {
            $target_fields = array();
        }

        // Merge fields and copy values
        foreach ($source_fields as $field_key => $field_data) {
            $target_fields[$field_key] = $field_data;
            
            // Copy specific field value
            $source_value = get_post_meta($source_page_id, 'cpcm_' . $field_key, true);
            update_post_meta($target_page_id, 'cpcm_' . $field_key, $source_value);
        }

        update_post_meta($target_page_id, '_cpcm_fields', $target_fields);

        wp_redirect(add_query_arg(array(
            'page' => 'page-content-manager',
            'action' => 'edit',
            'page_id' => $target_page_id,
            'message' => 'imported'
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Get translations for a post.
     *
     * @since    2.2.0
     * @param    int    $post_id    The post ID.
     * @return   array  $translations  Array of translation post IDs and their language info.
     */
    public function get_post_translations($post_id) {
        $translations = array();

        // Check for WPML
        if (defined('ICL_SITEPRESS_VERSION')) {
            $trid = apply_filters('wpml_element_trid', null, $post_id, 'post_page');
            if ($trid) {
                $element_translations = apply_filters('wpml_get_element_translations', array(), $trid, 'post_page');
                foreach ($element_translations as $lang_code => $translation) {
                    if (intval($translation->element_id) !== intval($post_id)) {
                        $translations[$lang_code] = array(
                            'id' => $translation->element_id,
                            'name' => isset($translation->display_name) ? $translation->display_name : $lang_code,
                            'code' => $lang_code
                        );
                    }
                }
            }
        }
        // Check for Polylang
        elseif (function_exists('pll_get_post_translations')) {
            $pll_translations = pll_get_post_translations($post_id);
            foreach ($pll_translations as $lang_code => $translation_id) {
                if (intval($translation_id) !== intval($post_id)) {
                    $translations[$lang_code] = array(
                        'id' => $translation_id,
                        'name' => function_exists('pll_get_language_name') ? pll_get_language_name($lang_code) : $lang_code,
                        'code' => $lang_code
                    );
                }
            }
        }

        return $translations;
    }
}
