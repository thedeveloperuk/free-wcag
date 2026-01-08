<?php
/**
 * Plugin deactivation handler.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles plugin deactivation tasks.
 */
class WPA11Y_Deactivator {

    /**
     * Run deactivation tasks.
     *
     * @return void
     */
    public static function deactivate(): void {
        // Clear scheduled events
        self::clear_scheduled_events();

        // Clear transients
        self::clear_transients();

        // Clear rewrite rules
        flush_rewrite_rules();

        /**
         * Fires after plugin deactivation.
         */
        do_action( 'wpa11y_deactivated' );
    }

    /**
     * Clear scheduled cron events.
     *
     * @return void
     */
    private static function clear_scheduled_events(): void {
        $events = [
            'wpa11y_scheduled_scan',
            'wpa11y_cleanup_old_scans',
        ];

        foreach ( $events as $event ) {
            $timestamp = wp_next_scheduled( $event );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $event );
            }
        }
    }

    /**
     * Clear plugin transients.
     *
     * @return void
     */
    private static function clear_transients(): void {
        delete_transient( 'wpa11y_activation_redirect' );
        delete_transient( 'wpa11y_scan_in_progress' );
        
        // Clear object cache
        wp_cache_delete( 'wpa11y_settings', 'wpa11y' );
    }
}

