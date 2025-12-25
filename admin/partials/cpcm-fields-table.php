<?php
/**
 * Fields table partial.
 */
if (!defined('WPINC')) {
    die;
}
?>
<div id="cpcm-fields-container" <?php echo empty($fields) ? 'style="display:none;"' : ''; ?>>
    <div class="cpcm-card">
        <div class="cpcm-card-header-with-actions">
            <h2 class="cpcm-card-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <span class="cpcm-fields-count-text">
                    <?php echo esc_html(sprintf(__('Existing Fields (%d)', 'custom-page-content-manager'), count($fields))); ?>
                </span>
            </h2>
            <div class="cpcm-card-actions">
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
                    <?php if (!empty($fields)): ?>
                        <?php 
                        $page_content = get_post_field('post_content', $page_id);
                        foreach ($fields as $field_key => $field): 
                            $in_use = false;
                            if (!empty($page_content) && strpos($page_content, '[cpcm_field') !== false) {
                                if (strpos($page_content, 'field="' . $field_key . '"') !== false) {
                                    $in_use = true;
                                }
                            }
                        ?>
                        <tr data-field-key="<?php echo esc_attr($field_key); ?>" data-in-use="<?php echo $in_use ? '1' : '0'; ?>">
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
                                $preview_data = '';
                                
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
