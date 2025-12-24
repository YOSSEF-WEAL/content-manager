<?php
/**
 * Admin pages list view.
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
        'deleted' => array(
            'type' => 'success',
            'text' => __('Field deleted successfully!', 'custom-page-content-manager')
        ),
        'error' => array(
            'type' => 'error',
            'text' => __('An error occurred. Please try again.', 'custom-page-content-manager')
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
            <?php echo esc_html__('Page Content Manager', 'custom-page-content-manager'); ?>
        </h1>
        <p class="cpcm-subtitle"><?php echo esc_html__('Manage custom fields for your WordPress pages', 'custom-page-content-manager'); ?></p>
    </div>

    <div class="cpcm-card">
        <table class="wp-list-table widefat fixed striped cpcm-table">
            <thead>
                <tr>
                    <th class="cpcm-col-id"><?php echo esc_html__('ID', 'custom-page-content-manager'); ?></th>
                    <th class="cpcm-col-title"><?php echo esc_html__('Page Title', 'custom-page-content-manager'); ?></th>
                    <th class="cpcm-col-status"><?php echo esc_html__('Status', 'custom-page-content-manager'); ?></th>
                    <th class="cpcm-col-fields"><?php echo esc_html__('Fields', 'custom-page-content-manager'); ?></th>
                    <th class="cpcm-col-actions"><?php echo esc_html__('Actions', 'custom-page-content-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pages): ?>
                    <?php foreach ($pages as $page): ?>
                        <?php
                        $fields = get_post_meta($page->ID, '_cpcm_fields', true);
                        $field_count = is_array($fields) ? count($fields) : 0;
                        ?>
                        <tr>
                            <td class="cpcm-col-id">
                                <strong><?php echo esc_html($page->ID); ?></strong>
                            </td>
                            <td class="cpcm-col-title">
                                <strong><?php echo esc_html($page->post_title); ?></strong>
                            </td>
                            <td class="cpcm-col-status">
                                <?php if ($page->post_status === 'publish'): ?>
                                    <span class="cpcm-status cpcm-status-published">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php echo esc_html__('Published', 'custom-page-content-manager'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="cpcm-status cpcm-status-draft">
                                        <span class="dashicons dashicons-edit"></span>
                                        <?php echo esc_html__('Draft', 'custom-page-content-manager'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="cpcm-col-fields">
                                <span class="cpcm-field-count">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                    <?php 
                                    printf(
                                        _n('%s field', '%s fields', $field_count, 'custom-page-content-manager'),
                                        number_format_i18n($field_count)
                                    );
                                    ?>
                                </span>
                            </td>
                            <td class="cpcm-col-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=page-content-manager&action=edit&page_id=' . $page->ID)); ?>" 
                                   class="button button-primary cpcm-btn-edit">
                                    <span class="dashicons dashicons-admin-generic"></span>
                                    <?php echo esc_html__('Manage Fields', 'custom-page-content-manager'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="cpcm-no-pages">
                            <span class="dashicons dashicons-info"></span>
                            <?php echo esc_html__('No pages found. Create a page first to manage its custom fields.', 'custom-page-content-manager'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
