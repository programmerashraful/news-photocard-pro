<?php
/** Run with php tests/post-list-test.php. */
if (PHP_SAPI !== 'cli') exit;
define('ABSPATH', __DIR__ . '/');
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function __($text, $domain = '') { return $text; }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html__($text, $domain = '') { return esc_html($text); }
function current_user_can($cap, $id) { return $GLOBALS['can_edit'] && $id === 42; }
function has_post_thumbnail($id) { return $GLOBALS['has_image']; }
function home_url() { return 'https://example.org'; }
function wp_parse_url($url, $component) { return parse_url($url, $component); }
function check_ajax_referer() {}
function wp_unslash($value) { return $value; }
function get_post($id) { return $id === 42 ? $GLOBALS['post'] : null; }
function is_post_type_viewable($type) { return $type === 'post'; }
function get_the_title($id) { return 'Test post'; }
function wp_strip_all_tags($value) { return strip_tags($value); }
function get_bloginfo($key) { return $key === 'charset' ? 'UTF-8' : 'Test site'; }
function date_i18n($format, $time) { return date($format, $time); }
function get_post_time() { return 1; }
function get_the_post_thumbnail_url() { return false; }
function wp_send_json_error($data, $status = 200) { throw new RuntimeException($data['message'], $status); }
class UDNPC_License {
    public static $active = true;
    public static function can_use_premium_features() { return self::$active; }
}
require dirname(__DIR__) . '/includes/class-photocard-core.php';
function verify($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    echo "PASS: $message\n";
}
$core = new UDNPC_Core();
$can_edit = $has_image = true;
$columns = $core->post_columns(['title' => 'Title']);
verify(isset($columns['title'], $columns['udnpc_photocard']), 'PhotoCard column preserves existing columns');
function render_button($core) {
    ob_start();
    $core->post_column('udnpc_photocard', 42);
    return ob_get_clean();
}
$button = render_button($core);
verify(strpos($button, 'data-post-id="42"') !== false && strpos($button, ' disabled') === false, 'Download button targets its own post');
$has_image = false;
verify(strpos(render_button($core), ' disabled') !== false, 'Missing featured image disables download');
$has_image = true;
UDNPC_License::$active = false;
verify(strpos(render_button($core), ' disabled') !== false, 'Inactive subscription disables download');
UDNPC_License::$active = true;
$can_edit = false;
verify(strpos(render_button($core), ' disabled') !== false, 'Users without edit permission cannot use the row button');
$_POST['post_id'] = 42;
foreach (['draft', 'private', 'publish'] as $status) {
    foreach ([false, true] as $protected) {
        foreach ([false, true] as $can_edit) {
            $post = (object) ['post_status' => $status, 'post_password' => $protected ? 'secret' : '', 'post_type' => 'post'];
            $allowed = $can_edit || ($status === 'publish' && !$protected);
            try { $core->ajax_get_card_data(); }
            catch (RuntimeException $error) {
                verify($allowed ? $error->getMessage() === 'No featured image on this post.' : $error->getCode() === 403,
                    "$status / protected=" . (int) $protected . ' / editor=' . (int) $can_edit . ': download access enforced');
            }
        }
    }
}
