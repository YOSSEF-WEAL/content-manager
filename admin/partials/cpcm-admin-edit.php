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
        ),
        'imported' => array(
            'type' => 'success',
            'text' => __('Fields imported successfully!', 'custom-page-content-manager')
        ),
        'no_source_fields' => array(
            'type' => 'error',
            'text' => __('The source language has no fields to import.', 'custom-page-content-manager')
        )
    );
    
    if (isset($messages[$message_type])) {
        echo '<div class="notice cpcm-notice notice-' . esc_attr($messages[$message_type]['type']) . ' is-dismissible"><p>' . esc_html($messages[$message_type]['text']) . '</p></div>';
    }
}
?>

<div class="wrap cpcm-wrap">
    <div class="cpcm-header">
        <h1 class="cpcm-title">
            <span class="dashicons dashicons-edit-page"></span>
            <?php echo esc_html(sprintf(__('Edit Fields: %s', 'custom-page-content-manager'), $page->post_title)); ?>
        </h1>
        <div class="cpcm-header-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=page-content-manager')); ?>" class="button cpcm-btn-back">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php echo esc_html__('Back to Pages List', 'custom-page-content-manager'); ?>
            </a>
        </div>
    </div>



    <!-- Existing Fields Section -->
    <?php if (!empty($fields)): ?>
    <div class="cpcm-card">
        <div class="cpcm-card-header-with-actions">
            <h2 class="cpcm-card-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php echo esc_html(sprintf(__('Existing Fields (%d)', 'custom-page-content-manager'), count($fields))); ?>
            </h2>
            <div class="cpcm-card-actions">
                <button type="button" class="button cpcm-btn-header-action cpcm-btn-add-modal-trigger">
                    <span class="dashicons dashicons-plus"></span>
                    <?php echo esc_html__('Add Field', 'custom-page-content-manager'); ?>
                </button>
                
                <?php if (!empty($translations)): ?>
                    <?php foreach ($translations as $lang_code => $translation): ?>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;">
                            <?php wp_nonce_field('cpcm_import_fields_' . $page_id); ?>
                            <input type="hidden" name="action" value="cpcm_import_fields">
                            <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">
                            <input type="hidden" name="source_page_id" value="<?php echo esc_attr($translation['id']); ?>">
                            <button type="submit" class="button cpcm-btn-header-action cpcm-btn-import-lang" onclick="return confirm('<?php echo esc_js(sprintf(__('Are you sure you want to import fields from %s? This will overwrite existing fields with the same name.', 'custom-page-content-manager'), $translation['name'])); ?>');">
                                <span class="dashicons dashicons-translation"></span>
                                <?php echo esc_html(sprintf(__('Import (%s)', 'custom-page-content-manager'), strtoupper($lang_code))); ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                <?php endif; ?>

                <button type="submit" form="cpcm-main-save-form" class="button cpcm-btn-header-action cpcm-btn-save-all" disabled>
                    <span class="dashicons dashicons-saved"></span>
                    <?php echo esc_html__('Save', 'custom-page-content-manager'); ?>
                </button>

                <button type="button" class="button cpcm-btn-header-action cpcm-btn-reset-fields" disabled>
                    <span class="dashicons dashicons-undo"></span>
                    <?php echo esc_html__('Reset', 'custom-page-content-manager'); ?>
                </button>
            </div>
        </div>
        
        <form id="cpcm-main-save-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cpcm-form">
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
                    <tbody id="cpcm-fields-tbody">
                        <?php foreach ($fields as $field_key => $field): ?>
                        <tr data-field-key="<?php echo esc_attr($field_key); ?>">
                            <td class="cpcm-td-name">
                                <strong><?php echo esc_html($field['name']); ?></strong>
                                <input type="hidden" name="cpcm_field_registry[<?php echo esc_attr($field_key); ?>][name]" value="<?php echo esc_attr($field['name']); ?>">
                                <input type="hidden" name="cpcm_field_registry[<?php echo esc_attr($field_key); ?>][type]" value="<?php echo esc_attr($field['type']); ?>">
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
                                $preview_data = ''; // For passing to JS
                                
                                if ($field['type'] === 'single_image') {
                                    if ($current_value) {
                                        $image_url = wp_get_attachment_image_url($current_value, 'thumbnail');
                                        if ($image_url) {
                                            echo '<div class="cpcm-row-preview-container"><img src="' . esc_url($image_url) . '" alt="" class="cpcm-table-row-preview"></div>';
                                            $preview_data = $image_url;
                                        } else {
                                            echo '<span class="description">' . esc_html__('Image not found', 'custom-page-content-manager') . '</span>';
                                        }
                                    } else {
                                        echo '<span class="description">' . esc_html__('No image selected', 'custom-page-content-manager') . '</span>';
                                    }
                                } elseif ($field['type'] === 'multi_images') {
                                    $image_ids = $current_value ? explode(',', $current_value) : array();
                                    $gallery_previews = array();
                                    
                                    if (!empty($image_ids)) {
                                        echo '<div class="cpcm-table-gallery-preview">';
                                        $count = 0;
                                        foreach ($image_ids as $img_id) {
                                            if ($img_id) {
                                                $image_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                                                if ($image_url) {
                                                    $gallery_previews[] = array('id' => $img_id, 'url' => $image_url);
                                                    if ($count < 3) {
                                                        echo '<img src="' . esc_url($image_url) . '" alt="">';
                                                    }
                                                    $count++;
                                                }
                                            }
                                        }
                                        if (count($image_ids) > 3) {
                                            echo '<span class="cpcm-gallery-more">+' . (count($image_ids) - 3) . '</span>';
                                        }
                                        echo '</div>';
                                    } else {
                                        echo '<span class="description">' . esc_html__('No images selected', 'custom-page-content-manager') . '</span>';
                                    }
                                    $preview_data = json_encode($gallery_previews);
                                } elseif ($field['type'] === 'longtext') {
                                    echo '<div class="cpcm-table-text-preview">' . nl2br(esc_html(mb_strimwidth($current_value, 0, 100, '...'))) . '</div>';
                                } else {
                                    echo '<div class="cpcm-table-text-preview">' . esc_html($current_value) . '</div>';
                                }
                                ?>
                                <input type="hidden" name="cpcm_fields[<?php echo esc_attr($field_key); ?>]" value="<?php echo esc_attr($current_value); ?>" class="cpcm-row-value-input">
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
                                <button type="button" 
                                        class="button button-small cpcm-btn-edit-field"
                                        data-field-key="<?php echo esc_attr($field_key); ?>"
                                        data-field-name="<?php echo esc_attr($field['name']); ?>"
                                        data-field-type="<?php echo esc_attr($field['type']); ?>"
                                        data-field-value="<?php echo esc_attr($current_value); ?>"
                                        data-preview='<?php echo esc_attr($preview_data); ?>'>
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php echo esc_html__('Edit', 'custom-page-content-manager'); ?>
                                </button>
                                <button type="button" 
                                   class="button button-small cpcm-btn-delete-local"
                                   data-field-name="<?php echo esc_attr($field['name']); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php echo esc_html__('Delete', 'custom-page-content-manager'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="cpcm-card cpcm-empty-state">
        <div class="cpcm-empty-icon">
            <span class="dashicons dashicons-info"></span>
        </div>
        <h3><?php echo esc_html__('No fields yet', 'custom-page-content-manager'); ?></h3>
        <p><?php echo esc_html__('Click the "Add New Field" button to get started.', 'custom-page-content-manager'); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Add Field Modal -->
    <div id="cpcm-add-modal" class="cpcm-modal" style="display: none;">
        <div class="cpcm-modal-overlay"></div>
        <div class="cpcm-modal-content">
            <div class="cpcm-modal-header">
                <h2>
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php echo esc_html__('Add New Field', 'custom-page-content-manager'); ?>
                </h2>
                <button type="button" class="cpcm-modal-close">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <div class="cpcm-modal-form">
                <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">
                
                <div class="cpcm-modal-body">
                    <div class="cpcm-form-group">
                        <label for="add_field_name" class="cpcm-label">
                            <?php echo esc_html__('Field Name', 'custom-page-content-manager'); ?>
                            <span class="cpcm-required">*</span>
                        </label>
                        <input type="text" 
                               id="add_field_name" 
                               name="field_name" 
                               class="regular-text cpcm-input" 
                               required 
                               placeholder="<?php echo esc_attr__('e.g., Main Title', 'custom-page-content-manager'); ?>">
                    </div>
                    
                    <div class="cpcm-form-group">
                        <label for="add_field_type" class="cpcm-label">
                            <?php echo esc_html__('Field Type', 'custom-page-content-manager'); ?>
                            <span class="cpcm-required">*</span>
                        </label>
                        <select id="add_field_type" name="field_type" class="cpcm-select" required>
                            <option value="text"><?php echo esc_html__('Text (Short)', 'custom-page-content-manager'); ?></option>
                            <option value="longtext"><?php echo esc_html__('Text (Long)', 'custom-page-content-manager'); ?></option>
                            <option value="number"><?php echo esc_html__('Number', 'custom-page-content-manager'); ?></option>
                            <option value="single_image"><?php echo esc_html__('Single Image', 'custom-page-content-manager'); ?></option>
                            <option value="multi_images"><?php echo esc_html__('Multiple Images', 'custom-page-content-manager'); ?></option>
                        </select>
                    </div>

                    <!-- Dynamic Content Input Container -->
                    <div id="add_field_content_container" class="cpcm-form-group">
                        <label class="cpcm-label">
                            <?php echo esc_html__('Field Content', 'custom-page-content-manager'); ?>
                        </label>
                        
                        <!-- Text Input -->
                        <div class="cpcm-input-wrapper cpcm-input-text">
                            <input type="text" name="field_value_text" class="regular-text cpcm-input" placeholder="<?php echo esc_attr__('Enter text...', 'custom-page-content-manager'); ?>">
                        </div>

                        <!-- Long Text Input -->
                        <div class="cpcm-input-wrapper cpcm-input-longtext" style="display:none;">
                            <textarea name="field_value_longtext" rows="4" class="large-text cpcm-textarea" placeholder="<?php echo esc_attr__('Enter details...', 'custom-page-content-manager'); ?>"></textarea>
                        </div>

                        <!-- Number Input -->
                        <div class="cpcm-input-wrapper cpcm-input-number" style="display:none;">
                            <input type="number" name="field_value_number" class="regular-text cpcm-input" placeholder="0">
                        </div>

                        <!-- Single Image Input -->
                        <div class="cpcm-input-wrapper cpcm-input-single_image" style="display:none;">
                             <div class="cpcm-image-upload-wrapper">
                                <input type="hidden" name="field_value_image" class="cpcm-image-id" value="">
                                <div class="cpcm-image-preview"></div>
                                <button type="button" class="button cpcm-upload-image">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php echo esc_html__('Choose Image', 'custom-page-content-manager'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Multi Image Input -->
                        <div class="cpcm-input-wrapper cpcm-input-multi_images" style="display:none;">
                            <div class="cpcm-multi-image-wrapper">
                                <input type="hidden" name="field_value_gallery" class="cpcm-multi-image-ids" value="">
                                <div class="cpcm-multi-image-preview"></div>
                                <button type="button" class="button cpcm-upload-multi-images">
                                    <span class="dashicons dashicons-images-alt2"></span>
                                    <?php echo esc_html__('Add Images', 'custom-page-content-manager'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="cpcm-modal-footer">
                    <button type="button" class="button cpcm-modal-cancel">
                        <?php echo esc_html__('Cancel', 'custom-page-content-manager'); ?>
                    </button>
                    <button type="button" class="button button-primary cpcm-btn-apply-add">
                        <span class="dashicons dashicons-plus"></span>
                        <?php echo esc_html__('Apply Changes', 'custom-page-content-manager'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Field Modal -->
    <div id="cpcm-edit-modal" class="cpcm-modal" style="display: none;">
        <div class="cpcm-modal-overlay"></div>
        <div class="cpcm-modal-content">
            <div class="cpcm-modal-header">
                <h2>
                    <span class="dashicons dashicons-edit"></span>
                    <?php echo esc_html__('Edit Field', 'custom-page-content-manager'); ?>
                </h2>
                <button type="button" class="cpcm-modal-close">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <div class="cpcm-modal-form">
                <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">
                <input type="hidden" name="field_key" id="edit_field_key" value="">
                
                <div class="cpcm-modal-body">
                    <div class="cpcm-form-group">
                        <label for="edit_field_name" class="cpcm-label">
                            <?php echo esc_html__('Field Name', 'custom-page-content-manager'); ?>
                            <span class="cpcm-required">*</span>
                        </label>
                        <input type="text" 
                               id="edit_field_name" 
                               name="field_name" 
                               class="regular-text cpcm-input" 
                               required>
                    </div>
                    
                    <div class="cpcm-form-group">
                        <label for="edit_field_type" class="cpcm-label">
                            <?php echo esc_html__('Field Type', 'custom-page-content-manager'); ?>
                            <span class="cpcm-required">*</span>
                        </label>
                        <select id="edit_field_type" name="field_type" class="cpcm-select" required>
                            <option value="text"><?php echo esc_html__('Text (Short)', 'custom-page-content-manager'); ?></option>
                            <option value="longtext"><?php echo esc_html__('Text (Long)', 'custom-page-content-manager'); ?></option>
                            <option value="number"><?php echo esc_html__('Number', 'custom-page-content-manager'); ?></option>
                            <option value="single_image"><?php echo esc_html__('Single Image', 'custom-page-content-manager'); ?></option>
                            <option value="multi_images"><?php echo esc_html__('Multiple Images', 'custom-page-content-manager'); ?></option>
                        </select>
                        <p class="description cpcm-warning" style="display: none;">
                            <span class="dashicons dashicons-warning"></span>
                            <?php echo esc_html__('Warning: Changing field type may cause data loss if the types are incompatible.', 'custom-page-content-manager'); ?>
                        </p>
                    </div>

                    <!-- Dynamic Content Input Container (Edit Modal) -->
                    <div id="edit_field_content_container" class="cpcm-form-group">
                        <label class="cpcm-label">
                            <?php echo esc_html__('Field Content', 'custom-page-content-manager'); ?>
                        </label>
                        
                        <!-- Text Input -->
                        <div class="cpcm-input-wrapper cpcm-input-text">
                            <input type="text" id="edit_field_value_text" name="field_value_text" class="regular-text cpcm-input" placeholder="<?php echo esc_attr__('Enter text...', 'custom-page-content-manager'); ?>">
                        </div>

                        <!-- Long Text Input -->
                        <div class="cpcm-input-wrapper cpcm-input-longtext" style="display:none;">
                            <textarea id="edit_field_value_longtext" name="field_value_longtext" rows="4" class="large-text cpcm-textarea" placeholder="<?php echo esc_attr__('Enter details...', 'custom-page-content-manager'); ?>"></textarea>
                        </div>

                        <!-- Number Input -->
                        <div class="cpcm-input-wrapper cpcm-input-number" style="display:none;">
                            <input type="number" id="edit_field_value_number" name="field_value_number" class="regular-text cpcm-input" placeholder="0">
                        </div>

                        <!-- Single Image Input -->
                        <div class="cpcm-input-wrapper cpcm-input-single_image" style="display:none;">
                             <div class="cpcm-image-upload-wrapper">
                                <input type="hidden" id="edit_field_value_image" name="field_value_image" class="cpcm-image-id" value="">
                                <div class="cpcm-image-preview"></div>
                                <button type="button" class="button cpcm-upload-image">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php echo esc_html__('Choose Image', 'custom-page-content-manager'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Multi Image Input -->
                        <div class="cpcm-input-wrapper cpcm-input-multi_images" style="display:none;">
                            <div class="cpcm-multi-image-wrapper">
                                <input type="hidden" id="edit_field_value_gallery" name="field_value_gallery" class="cpcm-multi-image-ids" value="">
                                <div class="cpcm-multi-image-preview"></div>
                                <button type="button" class="button cpcm-upload-multi-images">
                                    <span class="dashicons dashicons-images-alt2"></span>
                                    <?php echo esc_html__('Add Images', 'custom-page-content-manager'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="cpcm-modal-footer">
                    <button type="button" class="button cpcm-modal-cancel">
                        <?php echo esc_html__('Cancel', 'custom-page-content-manager'); ?>
                    </button>
                    <button type="button" class="button button-primary cpcm-btn-apply-edit">
                        <span class="dashicons dashicons-yes"></span>
                        <?php echo esc_html__('Apply Changes', 'custom-page-content-manager'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
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
