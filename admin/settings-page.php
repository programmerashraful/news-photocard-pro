<?php
/** Settings view; loaded by UDNPC_Core after validating and saving the form. */
if (!defined('ABSPATH')) exit;
$template_names = [
    'editorial-cream' => 'Editorial Cream', 'midnight-focus' => 'Midnight Focus',
    'red-bulletin' => 'Red Bulletin', 'split-dispatch' => 'Split Dispatch', 'emerald-frame' => 'Emerald Frame',
    'jugantor-teal-left-logo' => 'Jugantor Teal',
    'default' => 'Classic Red', 'dhaka-post' => 'Dhaka Post',
    'jagonews-unique-1080' => 'JagoNews', 'jugantor-10820-pixel' => 'Jugantor',
    'kalerkantho-1080-classic' => 'Kaler Kantho', 'news-prothomalo-wc-auto-banner-cta' => 'Prothom Alo',
];
$template_thumbnails = [
    'editorial-cream' => 8, 'midnight-focus' => 9, 'red-bulletin' => 10,
    'split-dispatch' => 11, 'emerald-frame' => 12,
    'default' => 1, 'dhaka-post' => 2, 'jagonews-unique-1080' => 3,
    'jugantor-10820-pixel' => 4, 'kalerkantho-1080-classic' => 5,
    'news-prothomalo-wc-auto-banner-cta' => 6, 'jugantor-teal-left-logo' => 7,
];
$repository = UDNPC_GitHub_Updater::repository();
if ($error && isset($_POST['github_repository']) && is_string($_POST['github_repository'])) {
    $repository = sanitize_text_field(wp_unslash($_POST['github_repository']));
}
?>
<div class="wrap udnpc-studio">
    <header class="udnpc-header">
        <div class="udnpc-brand"><img class="udnpc-plugin-icon" src="<?php echo esc_url(plugin_dir_url(UDNPC_PLUGIN_FILE) . 'assets/plugin-icon.png'); ?>" width="46" height="46" alt=""><div><a href="https://nexolbd.com/" target="_blank" rel="noopener noreferrer">Nexol BD</a><span class="udnpc-brand-caption">nexolbd.com · <?php esc_html_e('PUBLISHER TOOLS', 'news-photocard-pro'); ?></span></div></div>
        <div class="udnpc-header-meta"><span class="udnpc-version">v<?php echo esc_html(UDNPC_VERSION); ?></span><a href="mailto:info@nexolbd.com"><?php esc_html_e('Get support', 'news-photocard-pro'); ?> <span aria-hidden="true">↗</span></a></div>
    </header>
    <div class="udnpc-intro"><div><p class="udnpc-eyebrow"><?php esc_html_e('YOUR STORIES. BEAUTIFULLY SHARED.', 'news-photocard-pro'); ?></p><h1><?php esc_html_e('PhotoCard Studio', 'news-photocard-pro'); ?><span class="udnpc-pro">PRO</span></h1><p><?php esc_html_e('Turn your headlines into a design worth sharing.', 'news-photocard-pro'); ?></p></div><div class="udnpc-format"><span class="dashicons dashicons-format-image" aria-hidden="true"></span><span><strong>1080 × 1080</strong><small><?php esc_html_e('Social-ready PNG', 'news-photocard-pro'); ?></small></span></div></div>
    <?php if ($notice): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="notice notice-error" role="alert"><p><?php echo esc_html($error); ?></p></div><?php endif; ?>
    <noscript><div class="notice notice-warning"><p><?php esc_html_e('Enable JavaScript to customize templates and preview your design.', 'news-photocard-pro'); ?></p></div></noscript>
    <form method="post" id="udnpc-settings-form">
        <?php wp_nonce_field('udnpcv36_save', 'udnpcv36_nonce'); ?>
        <div class="udnpc-workspace">
            <div class="udnpc-editor">
                <section class="udnpc-panel" aria-labelledby="udnpc-design-heading">
                    <div class="udnpc-section-heading"><span class="udnpc-step">01</span><div><h2 id="udnpc-design-heading"><?php esc_html_e('Choose your design', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('A starting point for your next great story.', 'news-photocard-pro'); ?></p></div><span class="udnpc-count"><?php echo esc_html(count($tpls)); ?> <?php esc_html_e('designs', 'news-photocard-pro'); ?></span></div>
                    <label class="screen-reader-text" for="udnpc-active-template"><?php esc_html_e('Design template', 'news-photocard-pro'); ?></label>
                    <select id="udnpc-active-template" name="active_template" class="udnpc-template-select">
                        <?php foreach ($tpls as $key => $template): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($key, $active); ?>><?php echo esc_html($template['name']); ?></option><?php endforeach; ?>
                    </select>
                    <div class="udnpc-template-grid" role="group" aria-label="<?php esc_attr_e('Available designs', 'news-photocard-pro'); ?>">
                        <?php foreach ($tpls as $key => $template): ?>
                        <button type="button" class="udnpc-template-tile" data-template="<?php echo esc_attr($key); ?>" aria-pressed="<?php echo $key === $active ? 'true' : 'false'; ?>">
                            <span class="udnpc-mini udnpc-mini-<?php echo esc_attr($template_thumbnails[$key] ?? 1); ?>" aria-hidden="true"><span class="udnpc-mini-brand"></span><span class="udnpc-mini-photo"></span><span class="udnpc-mini-title"></span><span class="udnpc-mini-footer"></span></span>
                            <span class="udnpc-tile-caption"><span><?php echo esc_html($template_names[$key] ?? preg_replace('/\s*\(.*/', '', $template['name'])); ?></span><span class="udnpc-selected-mark" aria-hidden="true">✓</span></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="udnpc-panel" aria-labelledby="udnpc-customize-heading">
                    <div class="udnpc-section-heading"><span class="udnpc-step">02</span><div><h2 id="udnpc-customize-heading"><?php esc_html_e('Make it yours', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('Your logo, your colors, your voice.', 'news-photocard-pro'); ?></p></div></div>
                    <?php include __DIR__ . '/font-selector.php'; ?>
                    <div id="udnpc-dynamic-fields"></div>
                </section>
                <section class="udnpc-panel" aria-labelledby="udnpc-button-heading">
                    <div class="udnpc-section-heading"><span class="udnpc-step">03</span><div><h2 id="udnpc-button-heading"><?php esc_html_e('Download button', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('Let readers take your stories with them.', 'news-photocard-pro'); ?></p></div></div>
                    <label class="udnpc-toggle-row"><span><strong><?php esc_html_e('Show automatically on posts', 'news-photocard-pro'); ?></strong><small><?php esc_html_e('Appears on posts with a featured image.', 'news-photocard-pro'); ?></small></span><input type="checkbox" name="show_button" value="1" <?php checked($core['show_button'], '1'); ?> role="switch" /></label>
                    <div class="udnpc-field-row"><label for="udnpc-button-text"><?php esc_html_e('Button label', 'news-photocard-pro'); ?></label><input type="text" id="udnpc-button-text" name="button_text" value="<?php echo esc_attr($core['button_text']); ?>" /></div>
                    <fieldset class="udnpc-position"><legend><?php esc_html_e('Position in post', 'news-photocard-pro'); ?></legend><label><input type="radio" name="button_position" value="start" <?php checked($core['button_position'], 'start'); ?> /><span><?php esc_html_e('Before content', 'news-photocard-pro'); ?></span></label><label><input type="radio" name="button_position" value="end" <?php checked($core['button_position'], 'end'); ?> /><span><?php esc_html_e('After content', 'news-photocard-pro'); ?></span></label></fieldset>
                </section>
                <section class="udnpc-panel udnpc-update-panel" aria-labelledby="udnpc-update-heading">
                    <div class="udnpc-section-heading"><span class="dashicons dashicons-update" aria-hidden="true"></span><div><h2 id="udnpc-update-heading"><?php esc_html_e('Plugin updates', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('Receive new versions through GitHub releases.', 'news-photocard-pro'); ?></p></div></div>
                    <?php if (UDNPC_GitHub_Updater::can_configure()): ?>
                    <div class="udnpc-field-row"><label for="udnpc-github-repository"><?php esc_html_e('GitHub repository URL', 'news-photocard-pro'); ?></label><input type="url" id="udnpc-github-repository" name="github_repository" value="<?php echo esc_attr($repository); ?>" placeholder="https://github.com/your-account/your-plugin" aria-describedby="udnpc-update-help" /><p id="udnpc-update-help" class="udnpc-help"><?php esc_html_e('Checks this repository for stable releases. Leave blank to disable GitHub update checks.', 'news-photocard-pro'); ?></p></div>
                    <?php else: ?><p class="udnpc-help"><?php esc_html_e('Your site or network administrator manages the update source.', 'news-photocard-pro'); ?></p><?php endif; ?>
                    <a class="udnpc-text-link" href="<?php echo esc_url(admin_url('update-core.php')); ?>"><?php esc_html_e('Open WordPress Updates', 'news-photocard-pro'); ?> <span aria-hidden="true">↗</span></a>
                </section>
            </div>
            <aside class="udnpc-sidebar">
                <section class="udnpc-panel udnpc-preview-panel" aria-labelledby="udnpc-preview-heading">
                    <div class="udnpc-preview-heading"><h2 id="udnpc-preview-heading"><?php esc_html_e('Live preview', 'news-photocard-pro'); ?></h2><span class="udnpc-live"><i></i><?php esc_html_e('LIVE', 'news-photocard-pro'); ?></span></div>
                    <div class="udnpc-preview-stage"><div id="udnpc-preview-viewport"><iframe id="udnpc-preview" title="<?php esc_attr_e('Photo card design preview', 'news-photocard-pro'); ?>" sandbox="allow-same-origin" scrolling="no"></iframe></div></div>
                    <div class="udnpc-preview-meta"><span id="udnpc-preview-name"></span><span>PNG · 1080 px</span></div>
                    <label for="udnpc-preview-title"><?php esc_html_e('Try a headline', 'news-photocard-pro'); ?></label><textarea id="udnpc-preview-title" rows="2">সিলেটের সবুজ প্রকৃতিতে নতুন দিনের গল্প</textarea>
                    <p class="udnpc-help"><?php esc_html_e('Preview only. Downloads use the post headline and featured image. Export appearance may vary slightly.', 'news-photocard-pro'); ?></p>
                    <p id="udnpc-font-status" class="udnpc-help" role="status"></p>
                </section>
                <section class="udnpc-panel udnpc-embed-panel"><span class="udnpc-eyebrow"><?php esc_html_e('PLACE IT YOUR WAY', 'news-photocard-pro'); ?></span><h2><?php esc_html_e('One button. Any story.', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('Add this shortcode to a post or page, or use the News PhotoCard Button widget in Elementor.', 'news-photocard-pro'); ?></p><div class="udnpc-copy-row"><code id="udnpc-shortcode">[news_photocard_button]</code><button type="button" id="udnpc-copy" aria-label="<?php esc_attr_e('Copy shortcode', 'news-photocard-pro'); ?>"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span></button></div><span id="udnpc-copy-status" role="status"></span></section>
                <section class="udnpc-support"><span class="udnpc-support-icon dashicons dashicons-sos" aria-hidden="true"></span><h2><?php esc_html_e('A little help goes a long way.', 'news-photocard-pro'); ?></h2><p><?php esc_html_e('Built and supported by Nexol BD.', 'news-photocard-pro'); ?></p><a href="mailto:info@nexolbd.com">info@nexolbd.com <span aria-hidden="true">↗</span></a><a href="tel:+8801737963893">+8801737963893</a><a href="https://nexolbd.com/" target="_blank" rel="noopener noreferrer">nexolbd.com <span aria-hidden="true">↗</span></a></section>
            </aside>
        </div>
        <div class="udnpc-savebar"><span id="udnpc-save-status" role="status"><?php esc_html_e('All changes saved', 'news-photocard-pro'); ?></span><span class="udnpc-save-hint"><?php esc_html_e('Save applies the selected design to your posts.', 'news-photocard-pro'); ?></span><button type="submit" class="button button-primary udnpc-save" disabled><?php esc_html_e('Save changes', 'news-photocard-pro'); ?> <span aria-hidden="true">→</span></button></div>
    </form>
    <footer class="udnpc-footer"><span>News PhotoCard Pro <span aria-hidden="true">/</span> <?php esc_html_e('Crafted by Nexol BD', 'news-photocard-pro'); ?></span><a href="https://nexolbd.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Build something great', 'news-photocard-pro'); ?> ↗</a></footer>
    <script type="application/json" id="udnpc-settings-data"><?php echo wp_json_encode($all_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
</div>
