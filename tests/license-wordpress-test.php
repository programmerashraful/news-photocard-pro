<?php
/** Read-only rendering smoke test against local WordPress. CLI only. */
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__, 4) . '/wp-load.php';
if (!class_exists('UDNPC_License')) require dirname(__DIR__) . '/news-photocard-pro.php';
function verify($ok, $message) { if (!$ok) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } echo "PASS: $message\n"; }
$admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
verify((bool) $admins, 'Administrator available for in-process rendering');
wp_set_current_user($admins[0]);
$_SERVER['REQUEST_METHOD'] = 'GET';
$fixture = [UDNPC_License::KEY => '', UDNPC_License::INSTANCE => [], UDNPC_License::STATE => [], UDNPC_License::RETRY => time() + 21600];
foreach (array_keys($fixture) as $option) {
    add_filter('pre_option_' . $option, function () use (&$fixture, $option) { return $fixture[$option]; });
}
ob_start(); UDNPC_License::page(); $html = ob_get_clean();
verify(strpos($html, 'Activate subscription') !== false && strpos($html, 'name="_wpnonce"') !== false, 'License screen renders activation and nonce');
verify(strpos($html, 'admin.php?page=news-photocard-license') !== false, 'Form always posts to the dedicated license route');
$core = new UDNPC_Core();
ob_start(); $core->settings_page(); $html = ob_get_clean();
verify(strpos($html, 'Activate subscription') !== false && strpos($html, 'udnpc-settings-form') === false, 'Inactive studio renders license screen');
$identity = ['site_url' => untrailingslashit(home_url()), 'instance_id' => '12345678-1234-4123-8123-123456789012'];
$fixture[UDNPC_License::KEY] = 'NX-WORDPRESS-FIXTURE';
$fixture[UDNPC_License::INSTANCE] = $identity;
$fixture[UDNPC_License::STATE] = array_merge($identity, ['active' => true, 'fingerprint' => hash('sha256', $fixture[UDNPC_License::KEY]), 'last_verified_at' => time(), 'valid_until' => time() + 21600, 'expires_at' => null]);
ob_start(); UDNPC_License::page(); $html = ob_get_clean();
verify(strpos($html, '>Active<') !== false && strpos($html, 'Lifetime') !== false && strpos($html, 'Deactivate and release installation') !== false, 'Active license screen renders status and controls');
verify(strpos($html, 'NX-WORDPRESS-FIXTURE') === false, 'Saved key never appears in rendered HTML');
ob_start(); $core->settings_page(); $html = ob_get_clean();
verify(strpos($html, 'udnpc-settings-form') !== false, 'Active license unlocks existing studio');
