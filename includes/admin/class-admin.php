<?php
/**
 * Admin controller class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles admin functionality.
 */
class WPA11Y_Admin {

    /**
     * Plugin settings.
     *
     * @var array
     */
    private array $settings;

    /**
     * Admin page slug.
     */
    const MENU_SLUG = 'free-wcag';

    /**
     * Constructor.
     *
     * @param array $settings Plugin settings.
     */
    public function __construct( array $settings ) {
        $this->settings = $settings;

        // Redirect after activation
        add_action( 'admin_init', [ $this, 'activation_redirect' ] );

        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . WPA11Y_PLUGIN_BASENAME, [ $this, 'add_action_links' ] );
    }

    /**
     * Redirect to settings page after activation.
     *
     * @return void
     */
    public function activation_redirect(): void {
        if ( get_transient( 'wpa11y_activation_redirect' ) ) {
            delete_transient( 'wpa11y_activation_redirect' );

            if ( ! isset( $_GET['activate-multi'] ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=' . self::MENU_SLUG ) );
                exit;
            }
        }
    }

    /**
     * Add admin menu page.
     *
     * @return void
     */
    public function add_menu_page(): void {
        add_menu_page(
            __( 'Accessibility Suite', 'free-wcag' ),
            __( 'Accessibility', 'free-wcag' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_dashboard' ],
            'dashicons-universal-access-alt',
            80
        );

        // Submenu pages
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Dashboard', 'free-wcag' ),
            __( 'Dashboard', 'free-wcag' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_dashboard' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Content Scanner', 'free-wcag' ),
            __( 'Scanner', 'free-wcag' ),
            'edit_posts',
            self::MENU_SLUG . '-scanner',
            [ $this, 'render_scanner' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Reports', 'free-wcag' ),
            __( 'Reports', 'free-wcag' ),
            'manage_options',
            self::MENU_SLUG . '-reports',
            [ $this, 'render_reports' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Help & Documentation', 'free-wcag' ),
            __( 'Help', 'free-wcag' ),
            'manage_options',
            self::MENU_SLUG . '-help',
            [ $this, 'render_help' ]
        );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_assets( string $hook ): void {
        // Only load on our pages
        if ( strpos( $hook, self::MENU_SLUG ) === false ) {
            return;
        }

        // Admin styles
        $css_file = file_exists( WPA11Y_PLUGIN_DIR . 'assets/build/css/admin.min.css' )
            ? 'assets/build/css/admin.min.css'
            : 'assets/src/css/admin.css';

        wp_enqueue_style(
            'wpa11y-admin',
            WPA11Y_PLUGIN_URL . $css_file,
            [],
            WPA11Y_VERSION
        );

        // Admin scripts - load BEFORE Alpine so global functions are defined
        $js_file = file_exists( WPA11Y_PLUGIN_DIR . 'assets/build/js/admin.min.js' )
            ? 'assets/build/js/admin.min.js'
            : 'assets/src/js/admin/app.js';

        wp_enqueue_script(
            'wpa11y-admin',
            WPA11Y_PLUGIN_URL . $js_file,
            [],
            WPA11Y_VERSION,
            [ 'in_footer' => true ]
        );

        // Alpine.js - load AFTER our script defines the global functions
        wp_enqueue_script(
            'wpa11y-alpinejs',
            WPA11Y_PLUGIN_URL . 'assets/vendor/alpine.min.js',
            [ 'wpa11y-admin' ],
            '3.14.3',
            [ 'strategy' => 'defer', 'in_footer' => true ]
        );

        // Localize script data
        wp_localize_script( 'wpa11y-admin', 'wpa11ySettings', $this->settings );
        wp_localize_script( 'wpa11y-admin', 'wpa11yRest', [
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
        wp_localize_script( 'wpa11y-admin', 'wpa11yAdminData', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wpa11y_admin_nonce' ),
            'strings' => [
                'saved'           => __( 'Settings saved successfully.', 'free-wcag' ),
                'saveFailed'      => __( 'Failed to save settings.', 'free-wcag' ),
                'reset'           => __( 'Settings reset to defaults.', 'free-wcag' ),
                'resetConfirm'    => __( 'Reset all settings to defaults? This cannot be undone.', 'free-wcag' ),
                'scanStarted'     => __( 'Scan started...', 'free-wcag' ),
                'scanComplete'    => __( 'Scan complete.', 'free-wcag' ),
                'scanFailed'      => __( 'Scan failed. Please try again.', 'free-wcag' ),
                'noIssues'        => __( 'No accessibility issues found!', 'free-wcag' ),
            ],
        ] );
    }

    /**
     * Register settings.
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting(
            'wpa11y_settings_group',
            WPA11Y_Settings::OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [ 'WPA11Y_Settings', 'sanitize' ],
                'default'           => WPA11Y_Settings::get_defaults(),
            ]
        );
    }

    /**
     * Render dashboard page.
     *
     * @return void
     */
    public function render_dashboard(): void {
        require_once WPA11Y_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Render scanner page.
     *
     * @return void
     */
    public function render_scanner(): void {
        require_once WPA11Y_PLUGIN_DIR . 'templates/admin/scanner.php';
    }

    /**
     * Render reports page.
     *
     * @return void
     */
    public function render_reports(): void {
        require_once WPA11Y_PLUGIN_DIR . 'templates/admin/reports.php';
    }

    /**
     * Render help page.
     *
     * @return void
     */
    public function render_help(): void {
        require_once WPA11Y_PLUGIN_DIR . 'templates/admin/help.php';
    }

    /**
     * Add action links to plugins page.
     *
     * @param array $links Existing links.
     * @return array Modified links.
     */
    public function add_action_links( array $links ): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=' . self::MENU_SLUG ),
            __( 'Settings', 'free-wcag' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }
}

