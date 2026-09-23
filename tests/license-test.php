<?php
/** Isolated WordPress boundary tests; no real customer keys, database, or API calls. */
if (PHP_SAPI !== 'cli') exit;
define('ABSPATH', __DIR__ . '/');
define('UDNPC_PLUGIN_FILE', dirname(__DIR__) . '/news-photocard-pro.php');
class WP_Error {
    public $code;
    private $message;
    public function __construct($code, $message = '') { $this->code = $code; $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error($v) { return $v instanceof WP_Error; }
function __($v, $domain = '') { return $v; }
function get_option($k, $d = false) { return $GLOBALS['options'][$k] ?? $d; }
function update_option($k, $v, $autoload = null) { $GLOBALS['options'][$k] = $v; $GLOBALS['autoload'][$k] = $autoload; }
function add_option($k, $v, $deprecated = '', $autoload = null) { if (isset($GLOBALS['options'][$k])) return false; update_option($k, $v, $autoload); return true; }
function delete_option($k) { unset($GLOBALS['options'][$k]); }
function home_url() { return $GLOBALS['site']; }
function untrailingslashit($s) { return rtrim($s, '/'); }
function wp_generate_uuid4() { return sprintf('12345678-1234-4123-8123-%012d', ++$GLOBALS['uuid']); }
function wp_json_encode($v) { return json_encode($v); }
function wp_remote_retrieve_response_code($r) { return $r['status']; }
function wp_remote_retrieve_body($r) { return json_encode($r['body']); }
function wp_remote_retrieve_header($r, $h) { return $r['headers'][$h] ?? ''; }
function wp_remote_post($url, $args) {
    $GLOBALS['requests'][] = [$url, $args];
    if (isset($GLOBALS['response'])) return $GLOBALS['response'];
    $payload = json_decode($args['body'], true);
    if (substr($url, -10) === 'deactivate') return ['status' => 200, 'body' => ['active' => false]];
    return ['status' => 200, 'body' => array_merge($payload, ['active' => true, 'expires_at' => $GLOBALS['expiry'], 'check_after_seconds' => 21600], $GLOBALS['override'])];
}
function wp_next_scheduled($hook) { return $GLOBALS['events'][$hook] ?? false; }
function wp_schedule_event($time, $recurrence, $hook) { $GLOBALS['events'][$hook] = $time; $GLOBALS['scheduled']++; }
function wp_clear_scheduled_hook($hook) { unset($GLOBALS['events'][$hook]); }
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function register_deactivation_hook() {}
function current_user_can($cap) { return $GLOBALS['admin']; }
function check_admin_referer($action) { if (!$GLOBALS['nonce']) throw new RuntimeException('nonce'); }
function check_ajax_referer() { if (!$GLOBALS['nonce']) throw new RuntimeException('nonce'); }
function wp_unslash($v) { return $v; }
function sanitize_key($v) { return preg_replace('/[^a-z_-]/', '', $v); }
function wp_parse_url($url, $part) { return parse_url($url, $part); }
function wp_send_json_error($data, $status = 200) { throw new RuntimeException('json:' . $status); }
function verify($ok, $label) { if (!$ok) { fwrite(STDERR, "FAIL: $label\n"); exit(1); } echo "PASS: $label\n"; }
function reset_fixture() {
    $GLOBALS['options'] = $GLOBALS['requests'] = $GLOBALS['events'] = $GLOBALS['override'] = [];
    $GLOBALS['site'] = 'https://customer.example/news/';
    $GLOBALS['expiry'] = gmdate('c', time() + 86400);
    $GLOBALS['scheduled'] = 0;
    $GLOBALS['admin'] = $GLOBALS['nonce'] = true;
    unset($GLOBALS['response']);
}
function activate() { return UDNPC_License::perform('activate', 'NX-TEST-LICENSE'); }
function expire_backoff() {
    $state = get_option(UDNPC_License::STATE, []);
    $state['backoff_until'] = 0;
    update_option(UDNPC_License::STATE, $state, false);
    update_option(UDNPC_License::RETRY, 0, false);
}
require dirname(__DIR__) . '/includes/class-photocard-license.php';
$GLOBALS['uuid'] = 0;
reset_fixture();
verify(!UDNPC_License::can_use_premium_features() && !$GLOBALS['requests'], 'Unlicensed site is blocked without HTTP requests');
verify(activate() === true && UDNPC_License::can_use_premium_features(), 'Valid activation enables features');
$request = $GLOBALS['requests'][0];
$payload = json_decode($request[1]['body'], true);
verify($request[0] === 'https://nexolbd.com/api/v1/licenses/activate' && $payload['product_code'] === 'photocard-pro' && $payload['site_url'] === 'https://customer.example/news', 'Production API contract preserves subdirectory URL');
verify($request[1]['sslverify'] && $request[1]['redirection'] === 0 && strpos($request[0], 'NX-') === false, 'TLS verified, no redirects or key in URL');
verify($GLOBALS['autoload'][UDNPC_License::KEY] === false, 'Credential is not autoloaded');
UDNPC_License::schedule(); UDNPC_License::schedule();
verify($GLOBALS['scheduled'] === 1, 'Cron registration is idempotent');
verify(count($GLOBALS['requests']) === 1, 'Valid cache does not issue repeated HTTP calls');
activate();
verify(json_decode($GLOBALS['requests'][1][1]['body'], true)['instance_id'] === $payload['instance_id'], 'Activation retry uses persistent UUID');
verify(is_wp_error(UDNPC_License::perform('activate', 'NX-OTHER')), 'Changing saved key requires deactivation');

foreach (['product_code' => 'wrong', 'instance_id' => 'wrong', 'site_url' => 'https://other.example', 'active' => 'true', 'expires_at' => 'tomorrow', 'check_after_seconds' => '21600'] as $field => $value) {
    reset_fixture(); $GLOBALS['override'] = [$field => $value];
    verify(is_wp_error(activate()) && !UDNPC_License::can_use_premium_features(), 'Reject malformed response: ' . $field);
}
reset_fixture(); $GLOBALS['expiry'] = gmdate('c', time() - 1);
verify(is_wp_error(activate()), 'Expired activation is rejected');
reset_fixture(); $GLOBALS['expiry'] = null; activate();
$state = get_option(UDNPC_License::STATE);
verify($state['valid_until'] <= time() + 21600 && UDNPC_License::can_use_premium_features(), 'Lifetime entitlement still has six-hour cache limit');
reset_fixture(); $GLOBALS['expiry'] = gmdate('c', time() + 100); activate();
verify(get_option(UDNPC_License::STATE)['valid_until'] <= time() + 100, 'Cache cannot outlive subscription expiry');

foreach ([403, 409, 422] as $status) {
    reset_fixture(); activate(); $GLOBALS['response'] = ['status' => $status, 'body' => []];
    verify(is_wp_error(UDNPC_License::perform('check')) && !UDNPC_License::can_use_premium_features(), 'Explicit rejection immediately disables entitlement: ' . $status);
}
foreach ([0, 500, 429] as $status) {
    reset_fixture(); activate(); $until = get_option(UDNPC_License::STATE)['valid_until'];
    $GLOBALS['response'] = $status ? ['status' => $status, 'body' => [], 'headers' => ['retry-after' => '900']] : new WP_Error('network');
    UDNPC_License::perform('check');
    verify(UDNPC_License::can_use_premium_features() && get_option(UDNPC_License::STATE)['valid_until'] === $until, 'Outage preserves only original cache: ' . $status);
    $count = count($GLOBALS['requests']); UDNPC_License::perform('check');
    verify(count($GLOBALS['requests']) === $count, 'Backoff prevents repeated requests: ' . $status);
    $state = get_option(UDNPC_License::STATE); $state['valid_until'] = time() - 1; update_option(UDNPC_License::STATE, $state, false);
    verify(!UDNPC_License::can_use_premium_features(), 'Outage cannot extend expired cache: ' . $status);
    reset_fixture(); $GLOBALS['response'] = new WP_Error('network');
    verify(is_wp_error(activate()) && !UDNPC_License::can_use_premium_features(), 'No offline activation');
}
foreach (['key', 'uuid', 'url'] as $change) {
    reset_fixture(); activate();
    if ($change === 'key') update_option(UDNPC_License::KEY, 'NX-DIFFERENT', false);
    if ($change === 'uuid') { $instance = get_option(UDNPC_License::INSTANCE); $instance['instance_id'] = 'other'; update_option(UDNPC_License::INSTANCE, $instance, false); }
    if ($change === 'url') $GLOBALS['site'] = 'https://clone.example';
    verify(!UDNPC_License::can_use_premium_features(), 'Copied state invalidated by changed ' . $change);
}
reset_fixture(); activate(); $old = get_option(UDNPC_License::INSTANCE); $GLOBALS['site'] = 'https://new.example';
verify(is_wp_error(activate()), 'Migration requires release of old binding');
verify(UDNPC_License::perform('deactivate') === true, 'Old installation can be released after migration');
$request = end($GLOBALS['requests']); $sent = json_decode($request[1]['body'], true);
verify($sent['site_url'] === $old['site_url'] && $sent['instance_id'] === $old['instance_id'], 'Migration deactivation uses original identity');
verify(!get_option(UDNPC_License::KEY) && !$GLOBALS['events'], 'Deactivation removes key and scheduled jobs');
activate();
verify(get_option(UDNPC_License::INSTANCE)['instance_id'] !== $old['instance_id'], 'Migrated installation receives fresh UUID');
UDNPC_License::cleanup();
verify(!$GLOBALS['events'] && get_option(UDNPC_License::KEY), 'Plugin deactivation clears cron but retains recovery credentials');

reset_fixture(); $GLOBALS['admin'] = false; $_SERVER['REQUEST_METHOD'] = 'POST'; $_POST = ['license_action' => 'activate', 'license_key' => 'NX-TEST'];
UDNPC_License::page();
verify(!$GLOBALS['requests'] && !$GLOBALS['options'], 'Non-administrator cannot change license');
$GLOBALS['admin'] = true; $GLOBALS['nonce'] = false;
try { UDNPC_License::page(); verify(false, 'Missing nonce'); } catch (RuntimeException $e) { verify($e->getMessage() === 'nonce' && !$GLOBALS['requests'], 'Missing nonce blocks activation'); }
$GLOBALS['nonce'] = true;
require dirname(__DIR__) . '/includes/class-photocard-core.php';
$core = new UDNPC_Core();
verify($core->inject_button('Original article') === 'Original article' && $core->shortcode_button() === '', 'Unlicensed rendering preserves article and hides shortcode');
try { $core->ajax_get_card_data(); verify(false, 'Download guard'); } catch (RuntimeException $e) { verify($e->getMessage() === 'json:403', 'Direct download AJAX is blocked server-side'); }
$GLOBALS['admin'] = false;
try { $core->ajax_get_saved_options(); verify(false, 'Admin AJAX guard'); } catch (RuntimeException $e) { verify($e->getMessage() === 'json:403', 'Non-admin settings AJAX is forbidden'); }
