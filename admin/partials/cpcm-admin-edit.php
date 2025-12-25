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
    <?php
    ob_start();
    ?>
    <!-- Global Header Actions -->
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

    <a href="<?php echo esc_url(admin_url('admin.php?page=page-content-manager')); ?>" class="button cpcm-btn-back">
        <span class="dashicons dashicons-arrow-left-alt2"></span>
        <?php echo esc_html__('Back to Pages List', 'custom-page-content-manager'); ?>
    </a>
    <?php
    $header_actions = ob_get_clean();
    $header_title = sprintf(__('Edit Fields: %s', 'custom-page-content-manager'), $page->post_title);
    include CPCM_PLUGIN_DIR . 'admin/partials/cpcm-admin-header.php';
    ?>



    <form id="cpcm-main-save-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cpcm-form">
        <?php wp_nonce_field('cpcm_save_fields_' . $page_id); ?>
        <input type="hidden" name="action" value="cpcm_save_fields">
        <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">

        <!-- Fields Table Section -->
        <?php include CPCM_PLUGIN_DIR . 'admin/partials/cpcm-fields-table.php'; ?>

        <!-- Empty State Section -->
        <div id="cpcm-empty-state-wrapper" <?php echo !empty($fields) ? 'style="display:none;"' : ''; ?>>
            <div class="cpcm-card cpcm-empty-state">
                <div class="cpcm-empty-icon">
                    <span class="dashicons dashicons-info"></span>
                </div>
                <h3><?php echo esc_html__('No fields yet', 'custom-page-content-manager'); ?></h3>
                <p><?php echo esc_html__('Click the "Add New Field" button to get started.', 'custom-page-content-manager'); ?></p>
                <div class="cpcm-empty-state-actions">
                    <button type="button" class="button button-primary button-hero cpcm-btn-add-modal-trigger">
                        <span class="dashicons dashicons-plus"></span>
                        <?php echo esc_html__('Add New Field', 'custom-page-content-manager'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Modals -->
    <?php include CPCM_PLUGIN_DIR . 'admin/partials/cpcm-modal-add-field.php'; ?>
    <?php include CPCM_PLUGIN_DIR . 'admin/partials/cpcm-modal-edit-field.php'; ?>
    
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
