<?php
/**
 * Plugin activation handler.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles plugin activation tasks.
 */
class WPA11Y_Activator {

    /**
     * Minimum PHP version.
     */
    const MIN_PHP_VERSION = '8.0';

    /**
     * Minimum WordPress version.
     */
    const MIN_WP_VERSION = '6.0';

    /**
     * Run activation tasks.
     *
     * @return void
     */
    public static function activate(): void {
        // Check requirements
        self::check_requirements();

        // Create default settings
        self::create_default_settings();

        // Create database tables
        self::create_tables();

        // Set activation flag for welcome notice
        set_transient( 'wpa11y_activation_redirect', true, 30 );

        // Clear rewrite rules
        flush_rewrite_rules();

        /**
         * Fires after plugin activation.
         */
        do_action( 'wpa11y_activated' );
    }

    /**
     * Check plugin requirements.
     *
     * @return void
     */
    private static function check_requirements(): void {
        // Check PHP version
        if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
            deactivate_plugins( WPA11Y_PLUGIN_BASENAME );
            wp_die(
                sprintf(
                    /* translators: 1: Required PHP version, 2: Current PHP version */
                    esc_html__( 'Free WCAG Accessibility Suite requires PHP %1$s or higher. Your server is running PHP %2$s.', 'free-wcag' ),
                    self::MIN_PHP_VERSION,
                    PHP_VERSION
                ),
                esc_html__( 'Plugin Activation Error', 'free-wcag' ),
                [ 'back_link' => true ]
            );
        }

        // Check WordPress version
        global $wp_version;
        if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
            deactivate_plugins( WPA11Y_PLUGIN_BASENAME );
            wp_die(
                sprintf(
                    /* translators: 1: Required WP version, 2: Current WP version */
                    esc_html__( 'Free WCAG Accessibility Suite requires WordPress %1$s or higher. You are running WordPress %2$s.', 'free-wcag' ),
                    self::MIN_WP_VERSION,
                    $wp_version
                ),
                esc_html__( 'Plugin Activation Error', 'free-wcag' ),
                [ 'back_link' => true ]
            );
        }
    }

    /**
     * Create default settings if not exists.
     *
     * @return void
     */
    private static function create_default_settings(): void {
        if ( false === get_option( WPA11Y_Settings::OPTION_NAME ) ) {
            require_once WPA11Y_PLUGIN_DIR . 'includes/class-settings.php';
            add_option( WPA11Y_Settings::OPTION_NAME, WPA11Y_Settings::get_defaults(), '', true );
        }
    }

    /**
     * Create custom database tables.
     *
     * @return void
     */
    private static function create_tables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Scan results table
        $table_results = $wpdb->prefix . 'a11y_scan_results';
        $sql_results   = "CREATE TABLE $table_results (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            scan_type VARCHAR(50) NOT NULL,
            issue_code VARCHAR(50) NOT NULL,
            issue_data LONGTEXT,
            severity ENUM('error', 'warning', 'notice') DEFAULT 'warning',
            element_selector TEXT,
            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            resolved_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY scan_type (scan_type),
            KEY severity (severity),
            KEY scanned_at (scanned_at)
        ) $charset_collate;";

        // Scan history table
        $table_history = $wpdb->prefix . 'a11y_scan_history';
        $sql_history   = "CREATE TABLE $table_history (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            scan_type VARCHAR(50) NOT NULL,
            total_issues INT(11) DEFAULT 0,
            errors INT(11) DEFAULT 0,
            warnings INT(11) DEFAULT 0,
            notices INT(11) DEFAULT 0,
            posts_scanned INT(11) DEFAULT 0,
            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY scan_type (scan_type),
            KEY scanned_at (scanned_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_results );
        dbDelta( $sql_history );

        // Store database version
        update_option( 'wpa11y_db_version', WPA11Y_VERSION );
    }
}

