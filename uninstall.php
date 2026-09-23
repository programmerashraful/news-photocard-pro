<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
// Keep saved designs and installation credentials for reinstall/deactivation recovery.
if (is_multisite()) {
    $offset = 0;
    do {
        $ids = get_sites(['fields' => 'ids', 'number' => 100, 'offset' => $offset]);
        foreach ($ids as $id) {
            switch_to_blog($id);
            wp_clear_scheduled_hook('udnpc_license_check');
            delete_option('udnpc_license_lock');
            restore_current_blog();
        }
        $offset += count($ids);
    } while (count($ids) === 100);
} else {
    wp_clear_scheduled_hook('udnpc_license_check');
    delete_option('udnpc_license_lock');
}
