<?php
/**
 * Admin header component partial.
 *
 * @var string $header_title
 * @var string $header_subtitle
 * @var string $header_actions HTML content for actions
 */
?>

<h1 class="screen-reader-text"><?php echo esc_html($header_title); ?></h1>

<div class="cpcm-header">
    <div class="cpcm-header-main">
        <h2 class="cpcm-title">
            <span class="dashicons dashicons-edit-page"></span>
            <?php echo esc_html($header_title); ?>
        </h2>
        <?php if (!empty($header_subtitle)): ?>
            <p class="cpcm-subtitle"><?php echo esc_html($header_subtitle); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($header_actions)): ?>
        <div class="cpcm-header-actions">
            <?php echo $header_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    <?php endif; ?>
</div>
