<?php
/**
 * Uninstall handler.
 *
 * Fired when the plugin is uninstalled (deleted) from WordPress.
 * Cleans up all plugin data from the database.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Exit if not called by WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check user permissions
if ( ! current_user_can( 'activate_plugins' ) ) {
    exit;
}

// Double check the plugin being uninstalled
if ( __FILE__ !== WP_UNINSTALL_PLUGIN ) {
    exit;
}

/**
 * Clean up plugin data.
 *
 * Removes all options and custom tables created by the plugin.
 */
function wpa11y_uninstall_cleanup() {
    global $wpdb;

    // Delete plugin options
    delete_option( 'wpa11y_settings' );
    delete_option( 'wpa11y_db_version' );

    // Delete transients
    delete_transient( 'wpa11y_activation_redirect' );
    delete_transient( 'wpa11y_scan_in_progress' );

    // Clean up any scan transients.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup on uninstall.
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpa11y_scan_%'"
    );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup on uninstall.
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpa11y_scan_%'"
    );

    // Drop custom tables.
    $tables = [
        $wpdb->prefix . 'a11y_scan_results',
        $wpdb->prefix . 'a11y_scan_history',
    ];

    foreach ( $tables as $table ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Dropping tables on uninstall.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safely constructed from wpdb prefix.
        $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
    }

    // Clear any scheduled cron events
    $cron_events = [
        'wpa11y_scheduled_scan',
        'wpa11y_cleanup_old_scans',
    ];

    foreach ( $cron_events as $event ) {
        $timestamp = wp_next_scheduled( $event );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, $event );
        }
    }

    // Clear object cache
    wp_cache_flush();
}

// Run cleanup
wpa11y_uninstall_cleanup();

