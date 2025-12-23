<?php
/**
 * Admin edit page view.
 *
 * @package    CPCM
 * @subpackage CPCM/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Display messages
if (isset($_GET['message'])) {
    $message_type = sanitize_key($_GET['message']);
    $messages = array(
        'saved' => array(
            'type' => 'success',
            'text' => __('Changes saved successfully!', 'custom-page-content-manager')
        ),
        'added' => array(
            'type' => 'success',
            'text' => __('Field added successfully!', 'custom-page-content-manager')
        ),
        'deleted' => array(
            'type' => 'success',
            'text' => __('Field deleted successfully!', 'custom-page-content-manager')
        ),
        'exists' => array(
            'type' => 'error',
            'text' => __('A field with this name already exists!', 'custom-page-content-manager')
        ),
        'error' => array(
            'type' => 'error',
            'text' => __('An error occurred. Please try again.', 'custom-page-content-manager')
        )
    );
    
    if (isset($messages[$message_type])) {
        echo '<div class="notice notice-' . esc_attr($messages[$message_type]['type']) . ' is-dismissible"><p>' . esc_html($messages[$message_type]['text']) . '</p></div>';
    }
}
?>

<div class="wrap cpcm-wrap">
    <div class="cpcm-header">
        <h1 class="cpcm-title">
            <span class="dashicons dashicons-edit-page"></span>
            <?php echo esc_html(sprintf(__('Edit Fields: %s', 'custom-page-content-manager'), $page->post_title)); ?>
        </h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=page-content-manager')); ?>" class="button cpcm-btn-back">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
            <?php echo esc_html__('Back to Pages List', 'custom-page-content-manager'); ?>
        </a>
    </div>

    <!-- Add New Field Section -->
    <div class="cpcm-card cpcm-add-field-card">
        <h2 class="cpcm-card-title">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php echo esc_html__('Add New Field', 'custom-page-content-manager'); ?>
        </h2>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cpcm-form">
            <?php wp_nonce_field('cpcm_add_field_' . $page_id); ?>
            <input type="hidden" name="action" value="cpcm_add_field">
            <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">
            
            <div class="cpcm-form-grid">
                <div class="cpcm-form-group">
                    <label for="field_name" class="cpcm-label">
                        <?php echo esc_html__('Field Name', 'custom-page-content-manager'); ?>
                        <span class="cpcm-required">*</span>
                    </label>
                    <input type="text" 
                           id="field_name" 
                           name="field_name" 
                           class="regular-text cpcm-input" 
                           required 
                           placeholder="<?php echo esc_attr__('e.g., Main Title, Hero Image, Description', 'custom-page-content-manager'); ?>">
                    <p class="description">
                        <?php echo esc_html__('Use a clear, descriptive name for this field', 'custom-page-content-manager'); ?>
                    </p>
                </div>
                
                <div class="cpcm-form-group">
                    <label for="field_type" class="cpcm-label">
                        <?php echo esc_html__('Field Type', 'custom-page-content-manager'); ?>
                        <span class="cpcm-required">*</span>
                    </label>
                    <select id="field_type" name="field_type" class="cpcm-select" required>
                        <option value="text"><?php echo esc_html__('Text (Short)', 'custom-page-content-manager'); ?></option>
                        <option value="longtext"><?php echo esc_html__('Text (Long)', 'custom-page-content-manager'); ?></option>
                        <option value="number"><?php echo esc_html__('Number', 'custom-page-content-manager'); ?></option>
                        <option value="single_image"><?php echo esc_html__('Single Image', 'custom-page-content-manager'); ?></option>
                        <option value="multi_images"><?php echo esc_html__('Multiple Images', 'custom-page-content-manager'); ?></option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="button button-primary button-large cpcm-btn-add">
                <span class="dashicons dashicons-plus"></span>
                <?php echo esc_html__('Add Field', 'custom-page-content-manager'); ?>
            </button>
        </form>
    </div>

    <!-- Existing Fields Section -->
    <?php if (!empty($fields)): ?>
    <div class="cpcm-card">
        <h2 class="cpcm-card-title">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php echo esc_html(sprintf(__('Existing Fields (%d)', 'custom-page-content-manager'), count($fields))); ?>
        </h2>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cpcm-form">
            <?php wp_nonce_field('cpcm_save_fields_' . $page_id); ?>
            <input type="hidden" name="action" value="cpcm_save_fields">
            <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">
            
            <div class="cpcm-fields-table-wrapper">
                <table class="wp-list-table widefat fixed striped cpcm-fields-table">
                    <thead>
                        <tr>
                            <th class="cpcm-th-name"><?php echo esc_html__('Field Name', 'custom-page-content-manager'); ?></th>
                            <th class="cpcm-th-type"><?php echo esc_html__('Type', 'custom-page-content-manager'); ?></th>
                            <th class="cpcm-th-value"><?php echo esc_html__('Value', 'custom-page-content-manager'); ?></th>
                            <th class="cpcm-th-shortcode"><?php echo esc_html__('Shortcode', 'custom-page-content-manager'); ?></th>
                            <th class="cpcm-th-actions"><?php echo esc_html__('Actions', 'custom-page-content-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fields as $field_key => $field): ?>
                        <tr>
                            <td class="cpcm-td-name">
                                <strong><?php echo esc_html($field['name']); ?></strong>
                            </td>
                            <td class="cpcm-td-type">
                                <span class="cpcm-type-badge cpcm-type-<?php echo esc_attr($field['type']); ?>">
                                    <?php
                                    $type_icons = array(
                                        'text' => 'editor-textcolor',
                                        'longtext' => 'media-text',
                                        'number' => 'calculator',
                                        'single_image' => 'format-image',
                                        'multi_images' => 'images-alt2'
                                    );
                                    $icon = isset($type_icons[$field['type']]) ? $type_icons[$field['type']] : 'admin-generic';
                                    ?>
                                    <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $field['type']))); ?>
                                </span>
                            </td>
                            <td class="cpcm-td-value">
                                <?php
                                $current_value = get_post_meta($page_id, 'cpcm_' . $field_key, true);
                                
                                if ($field['type'] === 'longtext') {
                                    echo '<textarea name="cpcm_fields[' . esc_attr($field_key) . ']" rows="3" class="large-text cpcm-textarea">' . esc_textarea($current_value) . '</textarea>';
                                } elseif ($field['type'] === 'number') {
                                    echo '<input type="number" name="cpcm_fields[' . esc_attr($field_key) . ']" value="' . esc_attr($current_value) . '" class="regular-text cpcm-input">';
                                } elseif ($field['type'] === 'single_image') {
                                    // Single Image Upload
                                    ?>
                                    <div class="cpcm-image-upload-wrapper">
                                        <input type="hidden" 
                                               name="cpcm_fields[<?php echo esc_attr($field_key); ?>]" 
                                               class="cpcm-image-id" 
                                               value="<?php echo esc_attr($current_value); ?>">
                                        
                                        <div class="cpcm-image-preview">
                                            <?php if ($current_value): 
                                                $image_url = wp_get_attachment_image_url($current_value, 'medium');
                                                if ($image_url):
                                            ?>
                                                <img src="<?php echo esc_url($image_url); ?>" alt="">
                                                <button type="button" class="cpcm-remove-image" title="<?php echo esc_attr__('Remove image', 'custom-page-content-manager'); ?>">
                                                    <span class="dashicons dashicons-no-alt"></span>
                                                </button>
                                            <?php 
                                                endif;
                                            endif; 
                                            ?>
                                        </div>
                                        
                                        <button type="button" class="button cpcm-upload-image">
                                            <span class="dashicons dashicons-upload"></span>
                                            <?php echo esc_html__('Choose Image', 'custom-page-content-manager'); ?>
                                        </button>
                                    </div>
                                    <?php
                                } elseif ($field['type'] === 'multi_images') {
                                    // Multiple Images Upload
                                    $image_ids = $current_value ? explode(',', $current_value) : array();
                                    ?>
                                    <div class="cpcm-multi-image-wrapper">
                                        <input type="hidden" 
                                               name="cpcm_fields[<?php echo esc_attr($field_key); ?>]" 
                                               class="cpcm-multi-image-ids" 
                                               value="<?php echo esc_attr($current_value); ?>">
                                        
                                        <div class="cpcm-multi-image-preview">
                                            <?php foreach ($image_ids as $img_id): 
                                                if ($img_id):
                                                    $image_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                                                    if ($image_url):
                                            ?>
                                                <div class="cpcm-multi-image-item" data-id="<?php echo esc_attr($img_id); ?>">
                                                    <img src="<?php echo esc_url($image_url); ?>" alt="">
                                                    <button type="button" class="cpcm-remove-multi-image">
                                                        <span class="dashicons dashicons-no-alt"></span>
                                                    </button>
                                                </div>
                                            <?php 
                                                    endif;
                                                endif;
                                            endforeach; 
                                            ?>
                                        </div>
                                        
                                        <button type="button" class="button cpcm-upload-multi-images">
                                            <span class="dashicons dashicons-images-alt2"></span>
                                            <?php echo esc_html__('Add Images', 'custom-page-content-manager'); ?>
                                        </button>
                                    </div>
                                    <?php
                                } else {
                                    echo '<input type="text" name="cpcm_fields[' . esc_attr($field_key) . ']" value="' . esc_attr($current_value) . '" class="regular-text cpcm-input">';
                                }
                                ?>
                            </td>
                            <td class="cpcm-td-shortcode">
                                <div class="cpcm-shortcode-wrapper">
                                    <code class="cpcm-shortcode" data-shortcode='[cpcm_field id="<?php echo esc_attr($page_id); ?>" field="<?php echo esc_attr($field_key); ?>"]'>
                                        [cpcm_field id="<?php echo esc_attr($page_id); ?>" field="<?php echo esc_attr($field_key); ?>"]
                                    </code>
                                    <button type="button" class="button button-small cpcm-btn-copy" data-clipboard='[cpcm_field id="<?php echo esc_attr($page_id); ?>" field="<?php echo esc_attr($field_key); ?>"]'>
                                        <span class="dashicons dashicons-clipboard"></span>
                                    </button>
                                </div>
                            </td>
                            <td class="cpcm-td-actions">
                                <a href="<?php echo esc_url(wp_nonce_url(
                                    admin_url('admin-post.php?action=cpcm_delete_field&page_id=' . $page_id . '&field_key=' . $field_key),
                                    'cpcm_delete_field_' . $page_id . '_' . $field_key
                                )); ?>" 
                                   class="button button-small cpcm-btn-delete"
                                   data-field-name="<?php echo esc_attr($field['name']); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php echo esc_html__('Delete', 'custom-page-content-manager'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cpcm-form-actions">
                <button type="submit" class="button button-primary button-large cpcm-btn-save">
                    <span class="dashicons dashicons-yes"></span>
                    <?php echo esc_html__('Save All Changes', 'custom-page-content-manager'); ?>
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="cpcm-card cpcm-empty-state">
        <div class="cpcm-empty-icon">
            <span class="dashicons dashicons-info"></span>
        </div>
        <h3><?php echo esc_html__('No fields yet', 'custom-page-content-manager'); ?></h3>
        <p><?php echo esc_html__('Add your first custom field using the form above to get started.', 'custom-page-content-manager'); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Developer Credits Footer -->
    <div class="cpcm-footer">
        <p>
            <?php 
            printf(
                esc_html__('Made with %s by %s', 'custom-page-content-manager'),
                '<span class="dashicons dashicons-heart cpcm-heart"></span>',
                '<a href="https://portfolio-yossef-weal.netlify.app/" target="_blank" rel="noopener noreferrer" class="cpcm-author-link">' . esc_html__('Yossef Weal', 'custom-page-content-manager') . '</a>'
            );
            ?>
        </p>
    </div>
</div>
