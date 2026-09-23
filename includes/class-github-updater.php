<?php
/** Public GitHub releases through the WordPress 5.8+ Update URI API. */
if (!defined('ABSPATH')) exit;

class UDNPC_GitHub_Updater {
    const OPTION = 'udnpc_github_repository';
    const DEFAULT_REPOSITORY = 'https://github.com/programmerashraful/news-photocard-pro';

    public function __construct() {
        add_filter('update_plugins_github.com', [$this, 'check_update'], 10, 3);
        add_filter('plugins_api', [$this, 'plugin_information'], 10, 3);
        add_filter('upgrader_source_selection', [$this, 'normalize_directory'], 10, 4);
        add_filter('upgrader_pre_download', [$this, 'authorize_download'], 10, 4);
    }

    public static function can_configure() {
        return current_user_can(is_multisite() ? 'manage_network_plugins' : 'update_plugins');
    }

    public static function normalize_repository($value) {
        if (!is_string($value)) return new WP_Error('invalid_repository', __('Enter a public GitHub repository URL.', 'news-photocard-pro'));
        if (trim($value) === '') return '';
        $value = preg_replace('/\.git$/i', '', rtrim(trim($value), '/'));
        $parts = wp_parse_url($value);
        $path = trim($parts['path'] ?? '', '/');
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || strtolower($parts['host'] ?? '') !== 'github.com' ||
            isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || isset($parts['port']) ||
            !preg_match('~^[a-zA-Z0-9][a-zA-Z0-9-]*/[a-zA-Z0-9_.-]+$~', $path) ||
            preg_match('~/(?:\.|\.\.)$~', $path)) {
            return new WP_Error('invalid_repository', __('Use a public HTTPS GitHub repository URL, for example https://github.com/your-account/your-plugin.', 'news-photocard-pro'));
        }
        return 'https://github.com/' . $path;
    }

    public static function repository() {
        // A new option deliberately replaces the previous GitLab default on upgrade.
        $url = self::normalize_repository(get_site_option(self::OPTION, self::DEFAULT_REPOSITORY));
        return is_wp_error($url) ? '' : $url;
    }

    public static function cache_key() {
        return 'udnpc_github_release_' . md5(self::repository());
    }

    private function api_base() {
        $repo = self::repository();
        return $repo ? 'https://api.github.com/repos/' . substr($repo, strlen('https://github.com/')) : '';
    }

    public static function icons() {
        $icon = plugin_dir_url(UDNPC_PLUGIN_FILE) . 'assets/plugin-icon.png';
        return ['1x' => $icon, '2x' => $icon];
    }

    public function release() {
        if (!UDNPC_License::can_use_premium_features() || !$this->api_base()) return false;
        $cached = get_site_transient(self::cache_key());
        if ($cached !== false) return is_array($cached) && !empty($cached['version']) ? $cached : false;
        $response = wp_safe_remote_get($this->api_base() . '/releases/latest', [
            'timeout' => 10, 'redirection' => 0, 'sslverify' => true, 'limit_response_size' => 262144,
            'headers' => ['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'],
            'user-agent' => 'News-PhotoCard-Pro/' . UDNPC_VERSION . '; ' . home_url('/'),
        ]);
        $release = false;
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            $tag = is_array($data) && isset($data['tag_name']) && is_string($data['tag_name']) ? $data['tag_name'] : '';
            $version = ltrim($tag, 'vV');
            $published = isset($data['published_at']) && is_string($data['published_at']) ? strtotime($data['published_at']) : false;
            if (preg_match('/^\d+\.\d+\.\d+(?:\.\d+)?$/', $version) &&
                ($data['draft'] ?? true) === false && ($data['prerelease'] ?? true) === false &&
                $published !== false && $published <= time()) {
                $package = self::repository() . '/archive/refs/tags/' . rawurlencode($tag) . '.zip';
                $asset_url = self::repository() . '/releases/download/' . rawurlencode($tag) . '/news-photocard-pro.zip';
                foreach (is_array($data['assets'] ?? null) ? $data['assets'] : [] as $asset) {
                    if (is_array($asset) && ($asset['name'] ?? '') === 'news-photocard-pro.zip' &&
                        ($asset['state'] ?? '') === 'uploaded' && ($asset['browser_download_url'] ?? '') === $asset_url) {
                        $package = $asset_url;
                        break;
                    }
                }
                $release = ['version' => $version, 'package' => $package,
                    'description' => isset($data['body']) && is_string($data['body']) ? $data['body'] : ''];
            }
        }
        set_site_transient(self::cache_key(), $release ?: ['unavailable' => true], $release ? HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS);
        return $release;
    }

    public function check_update($update, $plugin_data, $plugin_file) {
        if ($plugin_file !== plugin_basename(UDNPC_PLUGIN_FILE)) return $update;
        $release = $this->release();
        if (!$release) return false;
        return ['id' => self::repository(), 'slug' => 'news-photocard-pro', 'version' => $release['version'],
            'url' => self::repository() . '/releases', 'package' => $release['package'], 'requires' => '5.8',
            'requires_php' => '7.4', 'icons' => self::icons()];
    }

    public function plugin_information($result, $action, $args) {
        if ($action !== 'plugin_information' || ($args->slug ?? '') !== 'news-photocard-pro') return $result;
        $release = $this->release();
        return (object) ['name' => 'News PhotoCard Pro', 'slug' => 'news-photocard-pro',
            'version' => $release ? $release['version'] : UDNPC_VERSION,
            'author' => '<a href="https://nexolbd.com/">Nexol BD</a>', 'homepage' => 'https://nexolbd.com/',
            'requires' => '5.8', 'requires_php' => '7.4', 'icons' => self::icons(),
            'download_link' => $release ? $release['package'] : '',
            'sections' => ['description' => esc_html__('Branded news cards, twelve designs, live preview, and Elementor integration. Created by Nexol BD — nexolbd.com.', 'news-photocard-pro'),
                'changelog' => $release ? nl2br(esc_html($release['description'])) : esc_html__('No published GitHub release is currently available.', 'news-photocard-pro')]];
    }

    public function authorize_download($reply, $package, $upgrader, $hook_extra) {
        $repo = self::repository();
        $ours = ($hook_extra['plugin'] ?? '') === plugin_basename(UDNPC_PLUGIN_FILE) ||
            ($repo && is_string($package) && (strpos($package, $repo . '/archive/refs/tags/') === 0 || strpos($package, $repo . '/releases/download/') === 0));
        if ($ours && !UDNPC_License::can_use_premium_features()) return new WP_Error('udnpc_subscription_required', __('An active PhotoCard subscription is required for this update.', 'news-photocard-pro'));
        return $reply;
    }

    public function normalize_directory($source, $remote_source, $upgrader, $hook_extra) {
        if (is_wp_error($source) || ($hook_extra['plugin'] ?? '') !== plugin_basename(UDNPC_PLUGIN_FILE)) return $source;
        global $wp_filesystem;
        if (!$wp_filesystem || !$wp_filesystem->exists(trailingslashit($source) . 'news-photocard-pro.php')) return new WP_Error('udnpc_archive_layout', __('The release archive must contain news-photocard-pro.php at its plugin root.', 'news-photocard-pro'));
        $folder = basename(dirname(UDNPC_PLUGIN_FILE));
        if (basename(untrailingslashit($source)) === $folder) return $source;
        $target = trailingslashit($remote_source) . $folder . '/';
        if (!$wp_filesystem->move($source, $target)) return new WP_Error('udnpc_archive_move', __('The plugin update directory could not be prepared.', 'news-photocard-pro'));
        return $target;
    }
}
