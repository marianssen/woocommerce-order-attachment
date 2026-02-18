<?php
/**
 * Fired when the plugin is uninstalled.
 * Delete attachment meta from all orders using SQL to cover both CPT and HPOS if available
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Clear Classical Post Meta
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
        '_woa_attachment_id'
    )
);

// Clear HPOS Meta (if table exists)
$hpos_meta_table = $wpdb->prefix . 'wc_orders_meta';

if ($wpdb->get_var("SHOW TABLES LIKE '$hpos_meta_table'") === $hpos_meta_table) {
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$hpos_meta_table} WHERE meta_key = %s",
            '_woa_attachment_id'
        )
    );
}
