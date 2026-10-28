<?php
/** Nexol subscription entitlement. License credentials never leave server-side HTTP. */
if (!defined('ABSPATH')) exit;

class UDNPC_License {
    const ORIGIN = 'https://nexolbd.com';
    const PRODUCT = 'photocard-pro';
    const KEY = 'udnpc_license_key';
    const INSTANCE = 'udnpc_license_instance';
    const STATE = 'udnpc_license_state';
    const RETRY = 'udnpc_license_retry';
    const LOCK = 'udnpc_license_lock';
    const CRON = 'udnpc_license_check';
    const CACHE_SECONDS = 21600;

    public function __construct() {
        add_action('admin_menu', [$this, 'menu'], 20);
        add_action('admin_notices', [$this, 'notice']);
        add_action('init', [__CLASS__, 'schedule']);
        add_action(self::CRON, [__CLASS__, 'refresh']);
        register_deactivation_hook(UDNPC_PLUGIN_FILE, [__CLASS__, 'cleanup']);
    }

    public static function schedule() {
        if (get_option(self::KEY) && !wp_next_scheduled(self::CRON)) {
            wp_schedule_event(time() + 60, 'hourly', self::CRON);
        }
    }

    public static function cleanup($network_wide = false) {
        if ($network_wide && is_multisite()) {
            $offset = 0;
            do {
                $ids = get_sites(['fields' => 'ids', 'number' => 100, 'offset' => $offset]);
                foreach ($ids as $id) {
                    switch_to_blog($id);
                    self::cleanup();
                    restore_current_blog();
                }
                $offset += count($ids);
            } while (count($ids) === 100);
            return;
        }
        wp_clear_scheduled_hook(self::CRON);
        delete_option(self::LOCK);
    }

    private static function site() {
        return untrailingslashit(home_url());
    }

    private static function state() {
        $state = get_option(self::STATE, []);
        return is_array($state) ? $state : [];
    }

    private static function matches($state) {
        $key = get_option(self::KEY, '');
        $instance = get_option(self::INSTANCE, []);
        return is_string($key) && $key !== '' && is_array($instance) &&
            ($instance['site_url'] ?? '') === self::site() &&
            ($state['site_url'] ?? '') === self::site() &&
            !empty($instance['instance_id']) &&
            ($state['instance_id'] ?? '') === $instance['instance_id'] &&
            ($state['fingerprint'] ?? '') === hash('sha256', $key);
    }

    private static function valid($state) {
        $expires = self::expiry($state);
        return self::matches($state) && ($state['active'] ?? false) === true &&
            $expires !== false && time() < $expires &&
            time() < ($state['valid_until'] ?? 0) &&
            time() < ($state['last_verified_at'] ?? 0) + self::CACHE_SECONDS;
    }

    private static function expiry($data) {
        if (!array_key_exists('expires_at', $data)) return false;
        if ($data['expires_at'] === null) return PHP_INT_MAX;
        if (!is_string($data['expires_at']) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $data['expires_at'])) return false;
        try {
            $date = new DateTimeImmutable($data['expires_at']);
            $errors = DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) return false;
            return $date->getTimestamp();
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function can_use_premium_features() {
        self::refresh();
        return self::valid(self::state());
    }

    public static function refresh() {
        $instance = get_option(self::INSTANCE, []);
        if (!get_option(self::KEY) || !is_array($instance) || ($instance['site_url'] ?? '') !== self::site()) return;
        if (time() >= (int) get_option(self::RETRY, 0)) self::perform('check');
    }

    private static function error($code, $message) {
        return new WP_Error($code, __($message, 'news-photocard-pro'));
    }

    /** All operations share a lock and retry deadline, including manual retries. */
    public static function perform($action, $new_key = '') {
        if (!in_array($action, ['activate', 'check', 'deactivate'], true)) return self::error('action', 'Unsupported license action.');
        $key = get_option(self::KEY, '');
        if ($action === 'activate') {
            if (!is_string($new_key) || !preg_match('/^NX-[A-Za-z0-9-]{1,97}$/', $new_key)) return self::error('key', 'Enter a valid Nexol activation key beginning with NX-.');
            if ($key && $key !== $new_key) return self::error('key', 'Deactivate the saved license before replacing it.');
            $key = $new_key;
        }
        if (!is_string($key) || !$key) return self::error('key', 'Enter your Nexol activation key first.');
        // Successful check deadlines do not prevent an explicit check or deactivation.
        $state = self::state();
        if (time() < ($state['backoff_until'] ?? 0)) return self::error('retry', 'Please wait a few minutes before trying the license service again.');
        $locked_at = (int) get_option(self::LOCK, 0);
        if ($locked_at && $locked_at < time() - 60) delete_option(self::LOCK);
        if (!add_option(self::LOCK, time(), '', false)) return self::error('busy', 'A license request is already running. Please try again shortly.');
        try {
            $instance = get_option(self::INSTANCE, []);
            if (!is_array($instance) || empty($instance['instance_id']) || empty($instance['site_url'])) {
                $instance = ['instance_id' => wp_generate_uuid4(), 'site_url' => self::site()];
                update_option(self::INSTANCE, $instance, false);
            }
            if ($action !== 'deactivate' && $instance['site_url'] !== self::site()) return self::error('migration', 'The site URL has changed. Deactivate the old installation, then activate again for this URL.');
            if ($action === 'activate') update_option(self::KEY, $key, false);
            $response = wp_remote_post(self::ORIGIN . '/api/v1/licenses/' . $action, [
                'timeout' => 15, 'redirection' => 0, 'sslverify' => true, 'limit_response_size' => 16384,
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
                'body' => wp_json_encode(array_merge($instance, ['license_key' => $key, 'product_code' => self::PRODUCT])),
            ]);
            $status = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
            $body = is_wp_error($response) ? null : json_decode(wp_remote_retrieve_body($response), true);
            if ($status === 200 && is_array($body)) {
                if ($action === 'deactivate' && ($body['active'] ?? null) === false) {
                    foreach ([self::KEY, self::INSTANCE, self::STATE, self::RETRY] as $option) delete_option($option);
                    self::cleanup();
                    return true;
                }
                $expires = self::expiry($body);
                if ($action !== 'deactivate' && ($body['active'] ?? null) === true &&
                    ($body['product_code'] ?? '') === self::PRODUCT &&
                    ($body['instance_id'] ?? '') === $instance['instance_id'] &&
                    ($body['site_url'] ?? '') === $instance['site_url'] && $expires !== false && $expires > time() &&
                    isset($body['check_after_seconds']) && is_int($body['check_after_seconds']) && $body['check_after_seconds'] > 0) {
                    $until = min(time() + min(self::CACHE_SECONDS, $body['check_after_seconds']), $expires);
                    update_option(self::STATE, array_merge($instance, [
                        'active' => true, 'fingerprint' => hash('sha256', $key), 'last_verified_at' => time(),
                        'valid_until' => $until, 'expires_at' => $body['expires_at'], 'retry_error' => false,
                    ]), false);
                    update_option(self::RETRY, $until, false);
                    self::schedule();
                    return true;
                }
            }
            $temporary = $status === 0 || $status === 429 || $status >= 500;
            $messages = [
                403 => 'The license is invalid, expired, revoked, unpaid, or not activated. Renew or check your order at nexolbd.com.',
                409 => 'The installation limit was reached or this installation belongs to another URL. Deactivate the old installation first.',
                422 => 'The license request was rejected. Check the key and site URL, or contact Nexol support.',
                429 => 'The license service is busy. Please wait a few minutes before trying again.',
            ];
            $message = $messages[$status] ?? ($temporary ? 'The license service is temporarily unavailable. Please try again later.' : 'The license service returned an unexpected response. Contact Nexol support.');
            // Never extend entitlement after an outage. Explicit denials and malformed responses fail closed.
            if (!$temporary) $state['active'] = false;
            $state['retry_error'] = true;
            $state['message'] = $message;
            $delay = $temporary ? 300 : self::CACHE_SECONDS;
            if ($status === 429) {
                $retry = wp_remote_retrieve_header($response, 'retry-after');
                $seconds = is_numeric($retry) ? (int) $retry : (is_string($retry) ? strtotime($retry) - time() : 0);
                $delay = max(300, min(86400, $seconds));
            }
            $state['backoff_until'] = time() + ($temporary ? $delay : 30);
            update_option(self::STATE, $state, false);
            update_option(self::RETRY, time() + $delay, false);
            self::schedule();
            return self::error('license_request', $message);
        } finally {
            delete_option(self::LOCK);
        }
    }

    public function menu() {
        add_submenu_page('news-photocard-pro', __('Subscription license', 'news-photocard-pro'), __('License', 'news-photocard-pro'), 'manage_options', 'news-photocard-license', [$this, 'page']);
    }

    public function notice() {
        if (!current_user_can('manage_options') || self::can_use_premium_features()) return;
        echo '<div class="notice notice-warning"><p>' . esc_html__('PhotoCard Pro requires an active Nexol subscription.', 'news-photocard-pro') . ' <a href="' . esc_url(admin_url('admin.php?page=news-photocard-license')) . '">' . esc_html__('Manage license', 'news-photocard-pro') . '</a></p></div>';
    }

    public static function page() {
        if (!current_user_can('manage_options')) return;
        $result = null;
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            check_admin_referer('udnpc_license_save');
            $action = isset($_POST['license_action']) && is_string($_POST['license_action']) ? sanitize_key(wp_unslash($_POST['license_action'])) : '';
            $key = isset($_POST['license_key']) && is_string($_POST['license_key']) ? trim(wp_unslash($_POST['license_key'])) : '';
            if ($action === 'forget') {
                foreach ([self::KEY, self::INSTANCE, self::STATE, self::RETRY] as $option) delete_option($option);
                self::cleanup();
                $result = self::error('forgotten', 'Local license removed. This does not release a server installation slot; contact Nexol support if the old slot is still occupied.');
            } else {
                if ($action === 'activate' && $key === '') $key = get_option(self::KEY, '');
                $result = self::perform($action, $key);
            }
        }
        $active = self::can_use_premium_features();
        $state = self::state();
        $instance = get_option(self::INSTANCE, []);
        include plugin_dir_path(__FILE__) . '../admin/license-page.php';
    }
}
