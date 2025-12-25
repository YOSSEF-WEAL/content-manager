<?php
/**
 * Add Field Modal partial.
 */
if (!defined('WPINC')) {
    die;
}
?>
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

                <div id="add_field_content_container" class="cpcm-form-group">
                    <label class="cpcm-label"><?php echo esc_html__('Field Content', 'custom-page-content-manager'); ?></label>
                    
                    <div class="cpcm-input-wrapper cpcm-input-text">
                        <input type="text" name="field_value_text" class="regular-text cpcm-input" placeholder="<?php echo esc_attr__('Enter text...', 'custom-page-content-manager'); ?>">
                    </div>

                    <div class="cpcm-input-wrapper cpcm-input-longtext" style="display:none;">
                        <textarea name="field_value_longtext" rows="4" class="large-text cpcm-textarea" placeholder="<?php echo esc_attr__('Enter details...', 'custom-page-content-manager'); ?>"></textarea>
                    </div>

                    <div class="cpcm-input-wrapper cpcm-input-number" style="display:none;">
                        <input type="number" name="field_value_number" class="regular-text cpcm-input" placeholder="0">
                    </div>

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
                <button type="button" class="button cpcm-modal-cancel"><?php echo esc_html__('Cancel', 'custom-page-content-manager'); ?></button>
                <button type="button" class="button button-primary cpcm-btn-apply-add">
                    <span class="dashicons dashicons-plus"></span>
                    <?php echo esc_html__('Apply Changes', 'custom-page-content-manager'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
