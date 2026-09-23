<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <p><img src="<?php echo esc_url(plugin_dir_url(UDNPC_PLUGIN_FILE) . 'assets/plugin-icon.png'); ?>" width="80" height="80" alt="<?php esc_attr_e('News PhotoCard Pro icon', 'news-photocard-pro'); ?>"></p>
    <h1><?php esc_html_e('PhotoCard Pro — Subscription license', 'news-photocard-pro'); ?></h1>
    <p><?php esc_html_e('Created by', 'news-photocard-pro'); ?> <a href="https://nexolbd.com/" target="_blank" rel="noopener noreferrer">Nexol BD · nexolbd.com</a></p>
    <?php if ($result !== null): ?>
        <div class="notice <?php echo is_wp_error($result) ? 'notice-error' : 'notice-success'; ?>"><p><?php echo esc_html(is_wp_error($result) ? $result->get_error_message() : __('License updated successfully.', 'news-photocard-pro')); ?></p></div>
    <?php endif; ?>
    <p><?php esc_html_e('Activate the Nexol license from your order to enable PhotoCard Pro on this website.', 'news-photocard-pro'); ?></p>
    <table class="widefat striped" style="max-width:800px"><tbody>
        <tr><th><?php esc_html_e('Status', 'news-photocard-pro'); ?></th><td><strong><?php echo esc_html($active ? __('Active', 'news-photocard-pro') : __('Inactive — activation or verification required', 'news-photocard-pro')); ?></strong></td></tr>
        <tr><th><?php esc_html_e('Subscription expiry (UTC)', 'news-photocard-pro'); ?></th><td><?php echo esc_html(array_key_exists('expires_at', $state) ? ($state['expires_at'] === null ? __('Lifetime', 'news-photocard-pro') : $state['expires_at']) : '—'); ?></td></tr>
        <tr><th><?php esc_html_e('Last verified (UTC)', 'news-photocard-pro'); ?></th><td><?php echo esc_html(!empty($state['last_verified_at']) ? gmdate('Y-m-d H:i:s', $state['last_verified_at']) : '—'); ?></td></tr>
        <tr><th><?php esc_html_e('Registered site', 'news-photocard-pro'); ?></th><td><?php echo esc_html($instance['site_url'] ?? '—'); ?></td></tr>
    </tbody></table>
    <?php if (!empty($state['message'])): ?><p role="status"><?php echo esc_html($state['message']); ?></p><?php endif; ?>
    <?php if (!empty($instance['site_url']) && $instance['site_url'] !== untrailingslashit(home_url())): ?><p><?php esc_html_e('This site URL differs from the saved installation. Deactivate the old binding below, then activate on this site. For a clone that must run alongside the original, use a separate license and contact support to release the copied binding.', 'news-photocard-pro'); ?></p><?php endif; ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=news-photocard-license')); ?>" style="margin-top:24px">
        <?php wp_nonce_field('udnpc_license_save'); ?>
        <?php if (!get_option(UDNPC_License::KEY)): ?>
            <p><label for="udnpc-license-key"><?php esc_html_e('Nexol activation key', 'news-photocard-pro'); ?></label></p>
            <input type="password" id="udnpc-license-key" name="license_key" class="regular-text" autocomplete="new-password" placeholder="NX-…" required maxlength="100">
            <button class="button button-primary" name="license_action" value="activate"><?php esc_html_e('Activate subscription', 'news-photocard-pro'); ?></button>
        <?php else: ?>
            <p><?php esc_html_e('Your activation key is saved in this site’s WordPress options and is not displayed here. Deactivate to replace it.', 'news-photocard-pro'); ?></p>
            <?php if (!$active): ?><button class="button button-primary" name="license_action" value="activate"><?php esc_html_e('Retry activation', 'news-photocard-pro'); ?></button><?php endif; ?>
            <button class="button button-primary" name="license_action" value="check"><?php esc_html_e('Check subscription / renewal', 'news-photocard-pro'); ?></button>
            <button class="button" name="license_action" value="deactivate"><?php esc_html_e('Deactivate and release installation', 'news-photocard-pro'); ?></button>
            <?php if (!$active): ?><p><?php esc_html_e('If the key is incorrect or the service cannot release it, you can remove it locally. This does not free any server installation slot.', 'news-photocard-pro'); ?> <button class="button" name="license_action" value="forget"><?php esc_html_e('Remove local key', 'news-photocard-pro'); ?></button></p><?php endif; ?>
        <?php endif; ?>
    </form>
    <p><?php esc_html_e('Verification lasts up to six hours and never beyond your subscription expiry. A service outage does not extend this period. Deactivation preserves your posts, media, and designs.', 'news-photocard-pro'); ?></p>
    <p><a href="https://nexolbd.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Orders and renewals at Nexol BD', 'news-photocard-pro'); ?></a> · <a href="mailto:info@nexolbd.com"><?php esc_html_e('Contact support', 'news-photocard-pro'); ?></a></p>
</div>
