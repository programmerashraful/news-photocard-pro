<?php
/** Isolated updater contract checks: php tests/updater-test.php. No database or network. */
if (PHP_SAPI !== 'cli') exit;
define('ABSPATH', __DIR__ . '/');
define('UDNPC_PLUGIN_FILE', dirname(__DIR__) . '/news-photocard-pro.php');
define('UDNPC_VERSION', '3.8.1');
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);

class WP_Error { public $code; public function __construct($code, $message = '') { $this->code = $code; } }
class UDNPC_License { public static $active = true; public static function can_use_premium_features() { return self::$active; } }
function is_wp_error($value) { return $value instanceof WP_Error; }
function add_filter() {}
function home_url($path = '') { return 'https://customer.example/' ; }
function plugin_dir_url($file) { return 'https://customer.example/wp-content/plugins/news-photocard-pro/'; }
function __($value, $domain = '') { return $value; }
function current_user_can($cap) { return true; }
function is_multisite() { return false; }
function wp_parse_url($url, $part = -1) { return parse_url($url, $part); }
function esc_url_raw($url) { return $url; }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html__($value, $domain = '') { return esc_html($value); }
function get_site_option($key, $default = '') { return $GLOBALS['repository'] ?? $default; }
function get_site_transient($key) { return $GLOBALS['cache'][$key] ?? false; }
function set_site_transient($key, $value, $ttl) { $GLOBALS['cache'][$key] = $value; $GLOBALS['ttl'] = $ttl; }
function plugin_basename($file) { return basename(dirname($file)) . '/' . basename($file); }
function trailingslashit($value) { return rtrim($value, '/\\') . '/'; }
function untrailingslashit($value) { return rtrim($value, '/\\'); }
function wp_safe_remote_get($url, $args) { $GLOBALS['requests'][] = [$url, $args]; return $GLOBALS['response']; }
function wp_remote_retrieve_body($response) { return $response['body']; }
function wp_remote_retrieve_response_code($response) { return $response['code']; }
require dirname(__DIR__) . '/includes/class-github-updater.php';
function verify($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } echo "PASS: $message\n"; }
function release_fixture($tag, $extra = []) {
    $GLOBALS['cache'] = [];
    $GLOBALS['response'] = ['code' => 200, 'body' => json_encode(array_merge(['tag_name' => $tag, 'body' => '<script>test</script>', 'draft' => false, 'prerelease' => false, 'published_at' => '2026-01-01T00:00:00Z'], $extra))];
}
$updater = new UDNPC_GitHub_Updater();
$plugin = plugin_basename(UDNPC_PLUGIN_FILE);
UDNPC_License::$active = false;
verify($updater->check_update(false, [], $plugin) === false, 'Unlicensed site cannot discover paid updates');
verify(is_wp_error($updater->authorize_download(false, 'https://example.com/cached.zip', null, ['plugin' => $plugin])), 'Cached update download is blocked for inactive subscription');
verify($updater->authorize_download(false, 'https://example.com/other.zip', null, ['plugin' => 'other/plugin.php']) === false, 'Unrelated plugin downloads are unaffected');
UDNPC_License::$active = true;
verify($updater->authorize_download(false, 'https://example.com/cached.zip', null, ['plugin' => $plugin]) === false, 'Active subscription permits update download');
verify(UDNPC_GitHub_Updater::normalize_repository('https://github.com/team/plugin.git/') === 'https://github.com/team/plugin', 'GitHub URL and .git normalization');
foreach (['http://github.com/team/plugin', 'https://user:pass@github.com/team/plugin', 'https://github.com/team/plugin?token=secret', 'https://github.com/team/plugin/-/tree/main', 'https://github.com/team/../plugin'] as $url) {
    verify(is_wp_error(UDNPC_GitHub_Updater::normalize_repository($url)), 'Reject invalid or credential-bearing project URL');
}
release_fixture('v3.8.1');
$update = $updater->check_update(false, [], $plugin);
verify($update['version'] === '3.8.1', 'Stable release version reaches WordPress');
verify($update['package'] === 'https://github.com/programmerashraful/news-photocard-pro/archive/refs/tags/v3.8.1.zip', 'Archive is pinned to the release tag');
$count = count($GLOBALS['requests']);
$updater->release();
verify(count($GLOBALS['requests']) === $count && $GLOBALS['ttl'] === 3600, 'Successful metadata is cached');
verify(strpos(json_encode($update), 'fixture-token') === false, 'Update payload never contains credentials');
verify($updater->check_update('untouched', [], 'other/plugin.php') === 'untouched', 'Other GitHub plugins are not modified');
$info = $updater->plugin_information(false, 'plugin_information', (object) ['slug' => 'news-photocard-pro']);
verify(strpos($info->sections['changelog'], '<script>') === false, 'Release notes are escaped');
verify($info->author === '<a href="https://nexolbd.com/">Nexol BD</a>' && isset($info->icons['2x']), 'Creator and icon appear in plugin details');
verify($GLOBALS['requests'][0][0] === 'https://api.github.com/repos/programmerashraful/news-photocard-pro/releases/latest', 'Only intended GitHub releases endpoint is queried');
foreach (['https://github.com.evil.test/team/plugin', 'https://gitlab.com/team/plugin', 'https://github.com/team/sub/plugin', 'https://github.com/team/plugin/tree/main', 'https://github.com/team/plugin#tag'] as $url) {
    verify(is_wp_error(UDNPC_GitHub_Updater::normalize_repository($url)), 'Reject non-GitHub or non-repository URLs');
}
foreach (['draft', 'prerelease'] as $flag) {
    release_fixture('v3.8.1', [$flag => true]);
    verify($updater->release() === false, 'Ignore ' . $flag . ' releases');
}
$asset = ['name' => 'news-photocard-pro.zip', 'state' => 'uploaded', 'browser_download_url' => UDNPC_GitHub_Updater::DEFAULT_REPOSITORY . '/releases/download/v3.8.1/news-photocard-pro.zip'];
release_fixture('v3.8.1', ['assets' => [$asset]]);
verify($updater->release()['package'] === $asset['browser_download_url'], 'Prefer packaged plugin asset over source archive');
$asset['browser_download_url'] = 'https://evil.example/plugin.zip';
release_fixture('v3.8.1', ['assets' => [$asset]]);
verify($updater->release()['package'] === $update['package'], 'Untrusted release asset URLs are ignored');
UDNPC_License::$active = false;
verify(is_wp_error($updater->authorize_download(false, $update['package'], null, [])), 'Cached GitHub source archive cannot bypass license guard');
verify(is_wp_error($updater->authorize_download(false, UDNPC_GitHub_Updater::DEFAULT_REPOSITORY . '/releases/download/v3.8.1/news-photocard-pro.zip', null, [])), 'Packaged release cannot bypass license guard');
UDNPC_License::$active = true;
foreach (['v3.9.0-rc1', 'main', 'v3.8.1/evil'] as $tag) {
    release_fixture($tag);
    verify($updater->release() === false, 'Unstable or malformed release tags are ignored');
}
release_fixture('v9.0.0', ['published_at' => '2099-01-01T00:00:00Z']);
verify($updater->release() === false, 'Future releases are ignored');
$GLOBALS['cache'] = [];
$GLOBALS['response'] = ['code' => 404, 'body' => '{}'];
verify($updater->release() === false && $GLOBALS['ttl'] === 300, 'Missing release is a cached nonfatal result');
$GLOBALS['cache'] = [];
$GLOBALS['response'] = new WP_Error('network');
verify($updater->release() === false, 'Network failure does not break updates');
class FixtureFilesystem {
    public $exists = true;
    public $moves = [];
    public function exists($path) { return $this->exists; }
    public function move($from, $to) { $this->moves[] = [$from, $to]; return true; }
}
$wp_filesystem = new FixtureFilesystem();
$normalized = $updater->normalize_directory('/tmp/update/repo-v381-hash/', '/tmp/update/', null, ['plugin' => $plugin]);
verify($normalized === '/tmp/update/news-photocard-pro/', 'GitHub archive preserves installed directory name');
verify($updater->normalize_directory('/other/', '/tmp/', null, ['plugin' => 'other/plugin.php']) === '/other/', 'Other plugin archives are untouched');
$wp_filesystem->exists = false;
verify(is_wp_error($updater->normalize_directory('/tmp/bad/', '/tmp/', null, ['plugin' => $plugin])), 'Reject archives without the main plugin file');
$prior_error = new WP_Error('prior');
verify($updater->normalize_directory($prior_error, '/tmp/', null, ['plugin' => $plugin]) === $prior_error, 'Preserve earlier upgrader failures');
$GLOBALS['repository'] = '';
$count = count($GLOBALS['requests']);
verify($updater->release() === false && count($GLOBALS['requests']) === $count, 'Disabled repository makes no network request');
