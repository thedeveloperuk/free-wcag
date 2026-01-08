<?php
/**
 * Plugin Name:       Free WCAG
 * Plugin URI:        https://wordpress.org/plugins/free-wcag/
 * Description:       A comprehensive accessibility toolkit for WCAG 2.2 Level AA compliance. Features a user-facing toolbar, automated remediation, and content auditing.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      8.0
 * Author:            Developer
 * Author URI:        https://thedeveloper.co.uk/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       free-wcag
 * Domain Path:       /languages
 *
 * @package Free_WCAG
 * @author  Developer
 * @license GPL-2.0-or-later
 * @link    https://wordpress.org/plugins/free-wcag/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'WPA11Y_VERSION', '1.0.0' );
define( 'WPA11Y_PLUGIN_FILE', __FILE__ );
define( 'WPA11Y_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPA11Y_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPA11Y_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class WP_Accessibility_Suite {

    /**
     * Plugin instance.
     *
     * @var WP_Accessibility_Suite|null
     */
    private static ?WP_Accessibility_Suite $instance = null;

    /**
     * Plugin settings.
     *
     * @var array
     */
    private array $settings = [];

    /**
     * Loaded modules.
     *
     * @var array
     */
    private array $modules = [];

    /**
     * Get singleton instance.
     *
     * @return WP_Accessibility_Suite
     */
    public static function instance(): WP_Accessibility_Suite {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_frontend_hooks();
        $this->define_rest_hooks();
    }

    /**
     * Load required dependencies.
     *
     * @return void
     */
    private function load_dependencies(): void {
        // Core classes
        require_once WPA11Y_PLUGIN_DIR . 'includes/class-activator.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/class-deactivator.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/class-settings.php';

        // Admin classes
        require_once WPA11Y_PLUGIN_DIR . 'includes/admin/class-admin.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/admin/class-dashboard.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/admin/class-ajax-handler.php';

        // Frontend classes
        require_once WPA11Y_PLUGIN_DIR . 'includes/frontend/class-frontend.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/frontend/class-toolbar.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/frontend/class-asset-loader.php';

        // Module interface and base
        require_once WPA11Y_PLUGIN_DIR . 'includes/modules/interface-module.php';
        require_once WPA11Y_PLUGIN_DIR . 'includes/modules/abstract-module.php';

        // Load settings
        $this->settings = WPA11Y_Settings::get_settings();
    }

    /**
     * Set plugin locale for translations.
     *
     * Note: Since WordPress 4.6, translations are automatically loaded
     * from wordpress.org when the plugin is hosted there.
     *
     * @return void
     */
    private function set_locale(): void {
        // Translations are now auto-loaded by WordPress 4.6+ when hosted on wordpress.org.
        // Manual load_plugin_textdomain() is no longer necessary.
    }

    /**
     * Register admin hooks.
     *
     * @return void
     */
    private function define_admin_hooks(): void {
        if ( ! is_admin() ) {
            return;
        }

        $admin = new WPA11Y_Admin( $this->settings );
        
        add_action( 'admin_menu', [ $admin, 'add_menu_page' ] );
        add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_assets' ] );
        add_action( 'admin_init', [ $admin, 'register_settings' ] );

        // AJAX handlers
        $ajax = new WPA11Y_Ajax_Handler();
        add_action( 'wp_ajax_wpa11y_scan', [ $ajax, 'handle_scan' ] );
        add_action( 'wp_ajax_wpa11y_save_settings', [ $ajax, 'handle_save_settings' ] );
    }

    /**
     * Register frontend hooks.
     *
     * @return void
     */
    private function define_frontend_hooks(): void {
        if ( is_admin() ) {
            return;
        }

        // Check safe mode (admin only testing)
        if ( ! empty( $this->settings['global']['safe_mode'] ) && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $frontend = new WPA11Y_Frontend( $this->settings );

        // Asset loading
        add_action( 'wp_enqueue_scripts', [ $frontend, 'enqueue_assets' ] );

        // Toolbar injection
        if ( ! empty( $this->settings['global']['toolbar_enabled'] ) ) {
            $toolbar = new WPA11Y_Toolbar( $this->settings );
            add_action( 'wp_footer', [ $toolbar, 'render' ] );
        }

        // Skip links (high priority to be first in DOM)
        if ( $this->is_feature_enabled( 'module_navigation', 'skip_links' ) ) {
            add_action( 'wp_body_open', [ $frontend, 'render_skip_links' ], 0 );
        }

        // ARIA enhancements
        if ( ! empty( $this->settings['module_aria']['enabled'] ) ) {
            add_action( 'wp_footer', [ $frontend, 'render_live_region' ] );
            
            if ( $this->is_feature_enabled( 'module_aria', 'link_purpose' ) ) {
                add_filter( 'the_content_more_link', [ $frontend, 'enhance_read_more_link' ], 10, 2 );
                add_filter( 'excerpt_more', [ $frontend, 'enhance_excerpt_more' ] );
            }
        }
    }

    /**
     * Register REST API endpoints.
     *
     * @return void
     */
    private function define_rest_hooks(): void {
        add_action( 'rest_api_init', function() {
            require_once WPA11Y_PLUGIN_DIR . 'includes/api/class-rest-controller.php';
            
            $controller = new WPA11Y_REST_Controller();
            $controller->register_routes();
        });
    }

    /**
     * Check if a specific feature is enabled.
     *
     * @param string $module  Module key.
     * @param string $feature Feature key.
     * @return bool
     */
    public function is_feature_enabled( string $module, string $feature ): bool {
        if ( empty( $this->settings[ $module ]['enabled'] ) ) {
            return false;
        }

        return ! empty( $this->settings[ $module ]['features'][ $feature ] );
    }

    /**
     * Get plugin settings.
     *
     * @return array
     */
    public function get_settings(): array {
        return $this->settings;
    }

    /**
     * Update plugin settings.
     *
     * @param array $settings New settings.
     * @return bool
     */
    public function update_settings( array $settings ): bool {
        $this->settings = WPA11Y_Settings::save_settings( $settings );
        return true;
    }

    /**
     * Prevent cloning.
     */
    private function __clone() {}

    /**
     * Prevent unserializing.
     */
    public function __wakeup() {
        throw new \Exception( 'Cannot unserialize singleton' );
    }
}

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, function() {
    require_once WPA11Y_PLUGIN_DIR . 'includes/class-activator.php';
    WPA11Y_Activator::activate();
});

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, function() {
    require_once WPA11Y_PLUGIN_DIR . 'includes/class-deactivator.php';
    WPA11Y_Deactivator::deactivate();
});

/**
 * Initialize the plugin.
 *
 * @return WP_Accessibility_Suite
 */
function wpa11y(): WP_Accessibility_Suite {
    return WP_Accessibility_Suite::instance();
}

// Start the plugin
wpa11y();

