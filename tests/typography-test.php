<?php
/** php tests/typography-test.php — loads all real template schemas without WordPress/DB. */
if (PHP_SAPI !== 'cli') exit;
define('ABSPATH', __DIR__ . '/');
function __($text, $domain = '') { return $text; }
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function plugin_dir_path($file) { return dirname($file) . '/'; }
function get_option($key, $default = false) { return $GLOBALS['test_options'][$key] ?? $default; }
function update_option($key, $value) { $GLOBALS['test_options'][$key] = $value; }
function sanitize_key($key) { return strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $key)); }
function wp_unslash($value) { return $value; }
require dirname(__DIR__) . '/includes/class-photocard-core.php';
function verify($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } echo "PASS: $message\n"; }
$core = new UDNPC_Core();
verify($core->get_font() === 'SolaimanLipi', 'Existing installations default to Solaiman Lipi');
$save_font = new ReflectionMethod(UDNPC_Core::class, 'save_font');
$save_font->setAccessible(true);
$_POST['font_family'] = 'Shurjo';
verify($save_font->invoke($core) === '' && $core->get_font() === 'Shurjo', 'Shurjo selection persists');
verify($core->get_template_options('default')['font_family'] === 'Shurjo', 'Download options receive the saved font');
foreach (['Arial', 'Shurjo; color:red', ['Shurjo']] as $invalid_font) {
    $_POST['font_family'] = $invalid_font;
    verify($save_font->invoke($core) !== '' && $core->get_font() === 'Shurjo', 'Invalid font is rejected without changing the saved selection');
}
$_POST['font_family'] = 'SolaimanLipi';
verify($save_font->invoke($core) === '' && $core->get_font() === 'SolaimanLipi', 'Can switch back to Solaiman Lipi');
update_option(UDNPC_Core::OPT_FONT, ['invalid']);
verify($core->get_font() === 'SolaimanLipi', 'Malformed stored font falls back safely');
unset($_POST['font_family']);
$templates = $core->list_templates();
verify(count($templates) === 12, 'All twelve templates load with typography controls');
foreach ($templates as $key => $template) {
    foreach (['title', 'date', 'cta'] as $role) {
        verify(isset($template['fields'][$role . '_font_size']), $key . ': ' . $role . ' sizing is available');
        verify($template['fields'][$role . '_font_size']['default'] === '', $key . ': existing automatic sizing stays the default');
    }
    $has_site = strpos($template['html'], '{{opt.domain}}') !== false;
    verify(isset($template['fields']['site_font_size']) === $has_site, $key . ': website control matches visible website text');
}
$field = $templates['default']['fields']['title_font_size'];
foreach (['', '24', '48', '120'] as $size) verify($core->sanitize_template_value($size, 'font_size', $field) === $size, 'Accept valid headline size or Auto');
foreach (['23', '121', '48.5', '-1', '48px', '48; color:red', ['48']] as $size) verify($core->sanitize_template_value($size, 'font_size', $field) === null, 'Reject invalid/out-of-range headline size');
$field = $templates['default']['fields']['date_font_size'];
verify($core->sanitize_template_value('60', 'font_size', $field) === '60', 'Accept maximum footer/date size');
verify($core->sanitize_template_value('61', 'font_size', $field) === null, 'Reject oversized footer/date text');
