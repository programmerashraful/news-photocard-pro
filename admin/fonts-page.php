<?php
/** Global font settings, with the saved active design as a live preview. */
if (!defined('ABSPATH')) exit;
?>
<div class="wrap udnpc-studio">
    <div class="udnpc-intro"><div><p class="udnpc-eyebrow">PHOTOCARD STUDIO</p><h1><?php esc_html_e('Font settings', 'news-photocard-pro'); ?></h1><p><?php esc_html_e('Choose the typeface for your stories.', 'news-photocard-pro'); ?></p></div></div>
    <?php if ($notice): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="notice notice-error" role="alert"><p><?php echo esc_html($error); ?></p></div><?php endif; ?>
    <form method="post" id="udnpc-settings-form" data-fonts-only="true">
        <?php wp_nonce_field('udnpc_fonts_save', 'udnpc_fonts_nonce'); ?>
        <input type="hidden" id="udnpc-active-template" value="<?php echo esc_attr($active); ?>">
        <div class="udnpc-workspace">
            <div class="udnpc-editor">
                <section class="udnpc-panel">
                    <div class="udnpc-section-heading"><div><h2><?php esc_html_e('Card typography', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('Bengali fonts, ready for your photo cards.', 'news-photocard-pro'); ?></p></div></div>
                    <?php include __DIR__ . '/font-selector.php'; ?>
                    <p class="udnpc-help"><?php esc_html_e('Shurjo includes regular and bold weights. Each design keeps its existing text sizes and weights.', 'news-photocard-pro'); ?></p>
                    <a class="udnpc-text-link" href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG)); ?>"><?php esc_html_e('Edit designs and text sizes', 'news-photocard-pro'); ?> →</a>
                </section>
            </div>
            <aside class="udnpc-sidebar">
                <section class="udnpc-panel udnpc-preview-panel">
                    <div class="udnpc-preview-heading"><h2><?php esc_html_e('Live preview', 'news-photocard-pro'); ?></h2><span class="udnpc-live"><i></i>LIVE</span></div>
                    <div class="udnpc-preview-stage"><div id="udnpc-preview-viewport"><iframe id="udnpc-preview" title="<?php esc_attr_e('Photo card font preview', 'news-photocard-pro'); ?>" sandbox="allow-same-origin" scrolling="no"></iframe></div></div>
                    <div class="udnpc-preview-meta"><span id="udnpc-preview-name"></span><span>PNG · 1080 px</span></div>
                    <label for="udnpc-preview-title"><?php esc_html_e('Try a headline', 'news-photocard-pro'); ?></label><textarea id="udnpc-preview-title" rows="2">সিলেটের সবুজ প্রকৃতিতে নতুন দিনের গল্প</textarea>
                    <p id="udnpc-font-status" class="udnpc-help" role="status"></p>
                </section>
            </aside>
        </div>
        <div class="udnpc-savebar"><span id="udnpc-save-status" role="status"><?php esc_html_e('All changes saved', 'news-photocard-pro'); ?></span><button type="submit" class="button button-primary udnpc-save"><?php esc_html_e('Save font settings', 'news-photocard-pro'); ?> →</button></div>
    </form>
    <script type="application/json" id="udnpc-settings-data"><?php echo wp_json_encode($all_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
</div>
