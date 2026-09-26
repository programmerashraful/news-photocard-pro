<?php
if (!defined('ABSPATH')) exit;

class UDNPC_Core {
    const OPT_CORE   = 'udnpc_core_v3_6';
    const OPT_PREFIX = 'udnpc_tpl_opts_v3_6_';
    const MENU_SLUG  = 'news-photocard-pro';
    const OPT_FONT  = 'udnpc_font_family';

    public function list_fonts() {
        return ['SolaimanLipi' => 'Solaiman Lipi', 'Shurjo' => 'Shurjo'];
    }

    public function get_font() {
        $font = get_option(self::OPT_FONT, 'SolaimanLipi');
        return is_string($font) && isset($this->list_fonts()[$font]) ? $font : 'SolaimanLipi';
    }

    private function save_font() {
        $font = isset($_POST['font_family']) ? wp_unslash($_POST['font_family']) : $this->get_font();
        if (!is_string($font) || !isset($this->list_fonts()[$font])) {
            return __('Please select an available font.', 'news-photocard-pro');
        }
        update_option(self::OPT_FONT, $font);
        return '';
    }

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue']);
        add_filter('manage_post_posts_columns', [$this, 'post_columns']);
        add_action('manage_post_posts_custom_column', [$this, 'post_column'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
        add_filter('the_content', [$this, 'inject_button']);
        add_shortcode('news_photocard_button', [$this, 'shortcode_button']);

        add_action('wp_ajax_udnpc_get_card', [$this, 'ajax_get_card_data']);
        add_action('wp_ajax_nopriv_udnpc_get_card', [$this, 'ajax_get_card_data']);
        add_action('wp_ajax_udnpcv36_get_saved_options', [$this, 'ajax_get_saved_options']);
    }

    /* ---------- Templates ---------- */
    public function templates_dir() {
        return plugin_dir_path(__FILE__) . '../templates/';
    }

    public function list_templates() {
        $files = glob($this->templates_dir() . '*.php');
        $out = [];
        if ($files) {
            foreach ($files as $file) {
                $def = include $file;
                if (is_array($def) && !empty($def['key']) && !empty($def['name']) && !empty($def['fields']) && !empty($def['css']) && !empty($def['html'])) {
                    $sizes = [
                        'title' => [__('Headline size', 'news-photocard-pro'), 24, 120, 64],
                        'date' => [__('Date size', 'news-photocard-pro'), 12, 60, 28],
                        'cta' => [__('CTA / footer size', 'news-photocard-pro'), 12, 60, 26],
                        'site' => [__('Website size', 'news-photocard-pro'), 12, 60, 28],
                    ];
                    foreach ($sizes as $role => $size) {
                        if (strpos($def['html'], 'data-udnpc-text="' . $role . '"') !== false) {
                            $def['fields'][$role . '_font_size'] = [
                                'type' => 'font_size', 'label' => $size[0], 'default' => '',
                                'min' => $size[1], 'max' => $size[2], 'suggested' => $size[3],
                            ];
                        }
                    }
                    $out[$def['key']] = $def;
                }
            }
        }
        return $out;
    }

    private function core_defaults() {
        return [
            'active_template' => array_key_first($this->list_templates()),
            'show_button'     => '1',
            'button_position' => 'end',
            'button_text'     => '📸 Download News PhotoCard (1080×1080)',
        ];
    }

    public function get_core() {
        $core = get_option(self::OPT_CORE, []);
        if (!is_array($core)) {
            $core = [];
        }
        return wp_parse_args($core, $this->core_defaults());
    }

    public function get_active_template_key() {
        $core = $this->get_core();
        $tpls = $this->list_templates();
        $first = array_key_first($tpls);
        return (!empty($core['active_template']) && isset($tpls[$core['active_template']])) ? $core['active_template'] : $first;
    }

    public function get_template_options($tpl_key) {
        $opt_key = self::OPT_PREFIX . sanitize_key($tpl_key);
        $opts = get_option($opt_key, []);
        if (!is_array($opts)) {
            $opts = [];
        }

        $tpls = $this->list_templates();
        if (isset($tpls[$tpl_key])) {
            foreach ($tpls[$tpl_key]['fields'] as $k => $field) {
                if (!isset($opts[$k]) && isset($field['default'])) {
                    $opts[$k] = $field['default'];
                }
            }
        }
        $opts['font_family'] = $this->get_font();
        return $opts;
    }

    /* ---------- Admin ---------- */
    public function add_menu() {
        add_menu_page(
            __('News PhotoCard', 'news-photocard-pro'),
            __('PhotoCard', 'news-photocard-pro'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'settings_page'],
            plugin_dir_url(UDNPC_PLUGIN_FILE) . 'assets/plugin-icon.png',
            56
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'news-photocard-pro'),
            __('Settings', 'news-photocard-pro'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'settings_page']
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Font settings', 'news-photocard-pro'),
            __('Fonts', 'news-photocard-pro'),
            'manage_options',
            self::MENU_SLUG . '-fonts',
            [$this, 'fonts_page']
        );
    }

    public function admin_enqueue($hook) {
        // The sidebar appears on every admin screen, including unlicensed installs.
        wp_add_inline_style('common', '#adminmenu .toplevel_page_news-photocard-pro .wp-menu-image img{width:20px;height:20px;object-fit:contain;padding:7px 0 0;}');

        if ($hook === 'edit.php' && get_current_screen()->post_type === 'post') {
            wp_add_inline_style('common', '
                .column-udnpc_photocard{width:80px}
                .column-udnpc_photocard .udnpc-wrap{display:block;margin:0;text-align:left}
                .column-udnpc_photocard .udnpc-generate{
                    display:inline-flex;align-items:center;justify-content:center;
                    width:34px;height:34px;min-height:34px;padding:0;
                    border:1px solid #ded5fa;border-radius:10px;
                    background:linear-gradient(145deg,#faf8ff,#eee8ff);color:#6846d5;
                    box-shadow:0 2px 4px #48318a0d;cursor:pointer;
                    transition:background .15s,border-color .15s,color .15s,box-shadow .15s,transform .15s;
                }
                .column-udnpc_photocard .udnpc-generate .dashicons{font-size:18px;width:18px;height:18px}
                .column-udnpc_photocard .udnpc-generate:hover:not(:disabled){
                    background:#6b48e8;border-color:#6b48e8;color:#fff;
                    box-shadow:0 4px 9px #6b48e82b;transform:translateY(-1px);
                }
                .column-udnpc_photocard .udnpc-generate:focus-visible{outline:2px solid #6b48e8;outline-offset:3px;box-shadow:none}
                .column-udnpc_photocard .udnpc-generate:active:not(:disabled){background:#5937d1;transform:translateY(0);box-shadow:none}
                .column-udnpc_photocard .udnpc-generate:disabled{background:#f6f7f7!important;border-color:#dcdcde!important;color:#a7aaad!important;box-shadow:none!important;cursor:not-allowed;transform:none}
                .column-udnpc_photocard .udnpc-generate[aria-busy="true"]{cursor:wait}
                .column-udnpc_photocard .udnpc-download-error{text-align:left}
                @media(prefers-reduced-motion:reduce){.column-udnpc_photocard .udnpc-generate{transition:none;transform:none!important}}
            ');
            if (UDNPC_License::can_use_premium_features()) $this->enqueue_download_assets();
        }

        if (strpos($hook, self::MENU_SLUG) === false) return;
        if (!UDNPC_License::can_use_premium_features()) return;

        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('udnpc-admin', plugin_dir_url(__FILE__) . '../admin/admin.css', [], UDNPC_VERSION);
        wp_enqueue_script('udnpc-template', plugin_dir_url(__FILE__) . '../assets/template.js', [], UDNPC_VERSION, true);
        wp_enqueue_script('udnpc-admin', plugin_dir_url(__FILE__) . '../admin/admin.js', ['jquery', 'wp-color-picker', 'udnpc-template'], UDNPC_VERSION, true);

        wp_localize_script('udnpc-admin', 'UDNPCAdmin', [
            'templates' => $this->list_templates(),
            'core'      => $this->get_core(),
            'opts'      => $this->get_template_options($this->get_active_template_key()),
            'nonce'     => wp_create_nonce('udnpcv36_nonce'),
            'ajax'      => admin_url('admin-ajax.php'),
            'assets'    => plugin_dir_url(__FILE__) . '../assets/',
            'fontsUrl'  => plugin_dir_url(__FILE__) . '../assets/fonts.css?ver=' . UDNPC_VERSION,
            'site'      => get_bloginfo('name'),
            'domain'    => wp_parse_url(home_url(), PHP_URL_HOST),
            'date'      => date_i18n('d F Y'),
            'i18n'      => [
                'selectImage' => __('Choose image', 'news-photocard-pro'),
                'useImage' => __('Use this image', 'news-photocard-pro'),
                'remove' => __('Clear', 'news-photocard-pro'),
                'unsaved' => __('Unsaved changes', 'news-photocard-pro'),
                'saved' => __('All changes saved', 'news-photocard-pro'),
                'copied' => __('Copied!', 'news-photocard-pro'),
                'copyFailed' => __('Select and copy the shortcode below.', 'news-photocard-pro'),
                'images' => __('Images & branding', 'news-photocard-pro'),
                'colors' => __('Color palette', 'news-photocard-pro'),
                'text' => __('Card text', 'news-photocard-pro'),
                'typography' => __('Text sizing', 'news-photocard-pro'),
                'fontLoading' => __('Loading selected font…', 'news-photocard-pro'),
                'fontError' => __('The selected font could not be loaded. Refresh the page to try again.', 'news-photocard-pro'),
                'auto' => __('Auto', 'news-photocard-pro'),
                'sizeHelp' => __('Sizes are in pixels on the full 1080 × 1080 card. Auto keeps the original template sizing. Check the preview for wrapping with larger text.', 'news-photocard-pro'),
            ],
        ]);
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        if (!UDNPC_License::can_use_premium_features()) { UDNPC_License::page(); return; }
        $notice = '';
        $error = '';
        $tpls = $this->list_templates();
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('udnpcv36_save', 'udnpcv36_nonce');
            $active_template = isset($_POST['active_template']) ? sanitize_key(wp_unslash($_POST['active_template'])) : '';
            if (!isset($tpls[$active_template])) {
                $error = __('Please select an available template.', 'news-photocard-pro');
            }
            $repository = UDNPC_GitHub_Updater::repository();
            if (UDNPC_GitHub_Updater::can_configure() && isset($_POST['github_repository'])) {
                $repository = UDNPC_GitHub_Updater::normalize_repository(wp_unslash($_POST['github_repository']));
                if (is_wp_error($repository)) $error = $repository->get_error_message();
            }
            $saved = [];
            foreach ($tpls[$active_template]['fields'] ?? [] as $key => $field) {
                $raw = isset($_POST['tpl_' . $key]) ? wp_unslash($_POST['tpl_' . $key]) : '';
                $value = $this->sanitize_template_value($raw, $field['type'], $field);
                if ($value === null) {
                    $error = sprintf(__('Please enter a valid value for %s.', 'news-photocard-pro'), $field['label']);
                    break;
                }
                $saved[$key] = $value;
            }
            if (!$error) $error = $this->save_font();
            if (!$error) {
                $position = isset($_POST['button_position']) ? sanitize_key(wp_unslash($_POST['button_position'])) : 'end';
                $text = isset($_POST['button_text']) ? sanitize_text_field(wp_unslash($_POST['button_text'])) : '';
                update_option(self::OPT_CORE, [
                    'active_template' => $active_template,
                    'show_button' => isset($_POST['show_button']) ? '1' : '0',
                    'button_position' => in_array($position, ['start', 'end'], true) ? $position : 'end',
                    'button_text' => $text ?: $this->core_defaults()['button_text'],
                ]);
                update_option(self::OPT_PREFIX . $active_template, $saved);
                if (UDNPC_GitHub_Updater::can_configure()) {
                    $old_cache = UDNPC_GitHub_Updater::cache_key();
                    if ($repository !== UDNPC_GitHub_Updater::repository()) {
                        update_site_option(UDNPC_GitHub_Updater::OPTION, $repository);
                        delete_site_transient($old_cache);
                        delete_site_transient('update_plugins');
                    }
                }
                $notice = __('Your design settings have been saved.', 'news-photocard-pro');
            }
        }
        $core = $this->get_core();
        $font = $this->get_font();
        $fonts = $this->list_fonts();
        if ($error && isset($_POST['font_family']) && is_string($_POST['font_family']) && isset($fonts[$_POST['font_family']])) {
            $font = $_POST['font_family'];
        }
        $active = $this->get_active_template_key();
        $all_options = [];
        foreach ($tpls as $key => $template) $all_options[$key] = $this->get_template_options($key);
        if ($error && isset($tpls[$active_template])) {
            // Keep the submitted design visible after a validation error so edits are not lost.
            $active = $active_template;
            $core['show_button'] = isset($_POST['show_button']) ? '1' : '0';
            $core['button_position'] = isset($_POST['button_position']) && $_POST['button_position'] === 'start' ? 'start' : 'end';
            if (isset($_POST['button_text']) && is_string($_POST['button_text'])) {
                $core['button_text'] = sanitize_text_field(wp_unslash($_POST['button_text']));
            }
            foreach ($tpls[$active]['fields'] as $key => $field) {
                if (isset($_POST['tpl_' . $key]) && is_string($_POST['tpl_' . $key])) {
                    $all_options[$active][$key] = sanitize_text_field(wp_unslash($_POST['tpl_' . $key]));
                }
            }
        }
        // Render after saving so the browser always receives the newly persisted values.
        include plugin_dir_path(__FILE__) . '../admin/settings-page.php';
    }

    public function fonts_page() {
        if (!current_user_can('manage_options')) return;
        if (!UDNPC_License::can_use_premium_features()) { UDNPC_License::page(); return; }
        $notice = '';
        $error = '';
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('udnpc_fonts_save', 'udnpc_fonts_nonce');
            $error = $this->save_font();
            if (!$error) $notice = __('Font settings saved. New downloads will use this font.', 'news-photocard-pro');
        }
        $font = $this->get_font();
        $fonts = $this->list_fonts();
        $active = $this->get_active_template_key();
        $all_options = [$active => $this->get_template_options($active)];
        include plugin_dir_path(__FILE__) . '../admin/fonts-page.php';
    }

    public function sanitize_template_value($raw, $type, $field = []) {
        if (!is_string($raw)) return null;
        if ($raw === '') return '';
        if ($type === 'font_size') {
            if (!preg_match('/^\d+$/', $raw)) return null;
            $size = (int) $raw;
            return $size >= ($field['min'] ?? 12) && $size <= ($field['max'] ?? 120) ? (string) $size : null;
        }
        if ($type === 'color') {
            $hex = sanitize_hex_color($raw);
            if ($hex) return $hex;
            if (preg_match('/^rgba?\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})(?:\\s*,\\s*(0(?:\\.\\d+)?|1(?:\\.0+)?|\\.\\d+))?\\s*\\)$/', $raw, $m) &&
                max((int) $m[1], (int) $m[2], (int) $m[3]) <= 255 &&
                (strpos($raw, 'rgba') === 0 ? isset($m[4]) : !isset($m[4]))) return $raw;
            return null;
        }
        if ($type === 'image') {
            $url = esc_url_raw($raw, ['http', 'https']);
            return $url && wp_parse_url($url, PHP_URL_HOST) ? $url : null;
        }
        return sanitize_text_field($raw);
    }

    public function post_columns($columns) {
        $columns['udnpc_photocard'] = __('PhotoCard', 'news-photocard-pro');
        return $columns;
    }

    public function post_column($column, $post_id) {
        if ($column !== 'udnpc_photocard') return;
        $reason = '';
        if (!current_user_can('edit_post', $post_id)) {
            $reason = __('You cannot edit this post.', 'news-photocard-pro');
        } elseif (!UDNPC_License::can_use_premium_features()) {
            $reason = __('An active subscription is required.', 'news-photocard-pro');
        } elseif (!has_post_thumbnail($post_id)) {
            $reason = __('Add a featured image first.', 'news-photocard-pro');
        }
        printf(
            '<div class="udnpc-wrap" title="%s"><button type="button" class="button udnpc-generate" data-post-id="%d"%s aria-label="%s"><span class="dashicons dashicons-download" aria-hidden="true"></span></button></div>',
            esc_attr($reason ?: __('Download PhotoCard', 'news-photocard-pro')),
            (int) $post_id,
            $reason ? ' disabled' : '',
            esc_attr(__('Download PhotoCard', 'news-photocard-pro') . ($reason ? ': ' . $reason : ''))
        );
    }

    /* ---------- Front ---------- */
    public function enqueue_front_assets() {
        if (!is_singular()) return;
        if (!UDNPC_License::can_use_premium_features()) return;

        $this->enqueue_download_assets();
    }

    private function enqueue_download_assets() {
        wp_enqueue_style('udnpc-fonts', plugin_dir_url(__FILE__) . '../assets/fonts.css', [], UDNPC_VERSION);
        wp_enqueue_style('udnpc-el-btn', plugin_dir_url(__FILE__) . '../assets/elementor-button.css', [], UDNPC_VERSION);
        wp_enqueue_script('html2canvas', plugin_dir_url(__FILE__) . '../assets/html2canvas.min.js', [], '1.4.1', true);
        wp_enqueue_script('udnpc-template', plugin_dir_url(__FILE__) . '../assets/template.js', [], UDNPC_VERSION, true);
        wp_enqueue_script('udnpc-front', plugin_dir_url(__FILE__) . '../assets/front.js', ['html2canvas', 'jquery', 'udnpc-template'], UDNPC_VERSION, true);

        $tpl_key = $this->get_active_template_key();
        $opts    = $this->get_template_options($tpl_key);
        $tpls    = $this->list_templates();
        $tpl_def = isset($tpls[$tpl_key]) ? [
            'key'  => $tpls[$tpl_key]['key'],
            'css'  => $tpls[$tpl_key]['css'],
            'html' => $tpls[$tpl_key]['html'],
        ] : null;

        $core = $this->get_core();
        wp_localize_script('udnpc-front', 'UDNPCF', [
            'ajax'        => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('udnpcv36f'),
            'tpl'         => $tpl_key,
            'opts'        => $opts,
            'tpl_def'     => $tpl_def,
            'button_text' => $core['button_text'],
            'i18n' => [
                'preparing' => __('Creating your photo card', 'news-photocard-pro'),
                'detail' => __('Preparing your image and typography…', 'news-photocard-pro'),
                'error' => __('The photo card could not be created. Please try again.', 'news-photocard-pro'),
                'network' => __('Connection failed. Refresh the page and try again.', 'news-photocard-pro'),
            ],
        ]);
    }

    public function inject_button($content) {
        if (!UDNPC_License::can_use_premium_features()) return $content;
        if (!is_single() || !in_the_loop() || !is_main_query() || !has_post_thumbnail()) {
            return $content;
        }

        $core = $this->get_core();
        if ($core['show_button'] !== '1') {
            return $content;
        }

        $post_id = get_the_ID();
        $btn_html = sprintf(
            '<div class="udnpc-wrap"><button type="button" data-post-id="%d" class="udnpc-generate udnpc-elementor-btn button button-primary">%s</button></div>',
            esc_attr($post_id),
            esc_html($core['button_text'])
        );

        return ($core['button_position'] === 'start') ? $btn_html . $content : $content . $btn_html;
    }

    public function shortcode_button($atts = [], $content = '') {
        if (!UDNPC_License::can_use_premium_features()) return '';
        $atts = shortcode_atts(['text' => ''], $atts, 'news_photocard_button');
        $text = $atts['text'] ?: $this->get_core()['button_text'];
        $post_id = get_the_ID();
        if (!$post_id || !has_post_thumbnail($post_id)) return '';
        return sprintf(
            '<div class="udnpc-wrap"><button type="button" data-post-id="%d" class="udnpc-generate udnpc-elementor-btn button button-primary">%s</button></div>',
            esc_attr($post_id),
            esc_html($text)
        );
    }

    /* ---------- AJAX ---------- */
    public function ajax_get_card_data() {
        $domain = (string) wp_parse_url(home_url(), PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $domain);

        check_ajax_referer('udnpcv36f', 'nonce');
        if (!UDNPC_License::can_use_premium_features()) wp_send_json_error(['message' => __('PhotoCard downloads require an active subscription. Please contact the site administrator.', 'news-photocard-pro')], 403);
        $post_id = isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
        if (!$post_id) wp_send_json_error(['message' => 'Invalid post id']);

        $post = get_post($post_id);
        if (!$post || (!current_user_can('edit_post', $post_id) && ($post->post_status !== 'publish' || !empty($post->post_password) || !is_post_type_viewable($post->post_type)))) {
            wp_send_json_error(['message' => __('This post is not publicly available.', 'news-photocard-pro')], 403);
        }
        $title = html_entity_decode(wp_strip_all_tags(get_the_title($post_id)), ENT_QUOTES, get_bloginfo('charset'));
        $date  = date_i18n('d F Y', get_post_time('U', true, $post_id));
        $image = get_the_post_thumbnail_url($post_id, 'full');
        $site  = get_bloginfo('name');
        if (!$image) wp_send_json_error(['message' => 'No featured image on this post.']);

        $tpl_key = $this->get_active_template_key();
        $opts    = $this->get_template_options($tpl_key);
        if (!is_array($opts)) $opts = [];

        $raw_title = trim(wp_strip_all_tags((string) $title));
        $words     = preg_split('/\s+/u', $raw_title, -1, PREG_SPLIT_NO_EMPTY);
        $opts['wc'] = is_array($words) ? count($words) : 0;
        $opts['domain'] = $domain;

        $tpls    = $this->list_templates();
        $tpl_def = isset($tpls[$tpl_key]) ? [
            'key'  => $tpls[$tpl_key]['key'],
            'css'  => $tpls[$tpl_key]['css'],
            'html' => $tpls[$tpl_key]['html'],
        ] : null;

        wp_send_json_success([
            'title'   => $title,
            'date'    => $date,
            'image'   => $image,
            'site'    => $site,
            'tpl'     => $tpl_key,
            'opts'    => $opts,
            'tpl_def' => $tpl_def,
        ]);
    }

    public function ajax_get_saved_options() {
        check_ajax_referer('udnpcv36_nonce', '_ajax_nonce', true);
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Access denied'], 403);
        if (!UDNPC_License::can_use_premium_features()) wp_send_json_error(['message' => 'An active PhotoCard subscription is required.'], 403);
        $key = isset($_POST['key']) ? sanitize_text_field(wp_unslash($_POST['key'])) : '';
        if (empty($key)) wp_send_json_error(['message' => 'Missing key']);
        $opt = get_option(self::OPT_PREFIX . $key, []);
        wp_send_json_success($opt);
    }
}
new UDNPC_Core();
