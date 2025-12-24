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

        if (isset($_POST['cpcm_fields']) && is_array($_POST['cpcm_fields'])) {
            foreach ($_POST['cpcm_fields'] as $field_key => $field_value) {
                update_post_meta($page_id, 'cpcm_' . sanitize_key($field_key), sanitize_textarea_field($field_value));
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
}
