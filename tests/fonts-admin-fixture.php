<?php
/** Read-only WordPress fixture for font-render-test.js; never served over HTTP. */
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__, 4) . '/wp-load.php';
if (!class_exists('UDNPC_Core')) require dirname(__DIR__) . '/news-photocard-pro.php';
// In-process entitlement fixture only; no saved options or license service calls.
$license_site = untrailingslashit(home_url());
$license_instance = ['site_url' => $license_site, 'instance_id' => '12345678-1234-4123-8123-123456789012'];
foreach ([
    UDNPC_License::KEY => 'NX-RENDER-FIXTURE',
    UDNPC_License::INSTANCE => $license_instance,
    UDNPC_License::RETRY => time() + 21600,
    UDNPC_License::STATE => array_merge($license_instance, ['active' => true, 'fingerprint' => hash('sha256', 'NX-RENDER-FIXTURE'), 'last_verified_at' => time(), 'valid_until' => time() + 21600]),
] as $option => $value) {
    add_filter('pre_option_' . $option, function () use ($value) { return $value; });
}
$admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
if (!$admins) { fwrite(STDERR, "An administrator is needed to render settings.\n"); exit(1); }
wp_set_current_user($admins[0]);
add_filter('pre_option_' . UDNPC_Core::OPT_FONT, function () { return 'SolaimanLipi'; });
$core = new UDNPC_Core();
$core->admin_enqueue('toplevel_page_news-photocard-pro');
ob_start(); $core->fonts_page(); $fonts = ob_get_clean();
ob_start(); $core->settings_page(); $studio = ob_get_clean();
echo wp_json_encode(['fonts' => $fonts, 'studio' => $studio, 'config' => wp_scripts()->get_data('udnpc-admin', 'data')]);
