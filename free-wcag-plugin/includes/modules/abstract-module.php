<?php
/**
 * Abstract module base class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base class for accessibility modules.
 */
abstract class WPA11Y_Abstract_Module implements WPA11Y_Module_Interface {

    /**
     * Module settings.
     *
     * @var array
     */
    protected array $settings;

    /**
     * Module ID.
     *
     * @var string
     */
    protected string $id;

    /**
     * Constructor.
     *
     * @param array $settings Module settings from plugin options.
     */
    public function __construct( array $settings = [] ) {
        $this->settings = $settings;
    }

    /**
     * Get module ID.
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Check if module is enabled.
     *
     * @return bool
     */
    public function is_enabled(): bool {
        return ! empty( $this->settings['enabled'] );
    }

    /**
     * Check if a specific feature is enabled.
     *
     * @param string $feature Feature key.
     * @return bool
     */
    protected function is_feature_enabled( string $feature ): bool {
        if ( ! $this->is_enabled() ) {
            return false;
        }

        return ! empty( $this->settings['features'][ $feature ] );
    }

    /**
     * Get a setting value.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    protected function get_setting( string $key, $default = null ) {
        return $this->settings['settings'][ $key ] ?? $default;
    }

    /**
     * Get WCAG criteria covered by this module.
     *
     * @return array Array of WCAG criterion IDs.
     */
    public function get_wcag_criteria(): array {
        return [];
    }

    /**
     * Get module icon (dashicon name or SVG).
     *
     * @return string
     */
    public function get_icon(): string {
        return 'admin-generic';
    }

    /**
     * Check if module has admin settings.
     *
     * @return bool
     */
    public function has_settings(): bool {
        return ! empty( $this->settings['settings'] );
    }

    /**
     * Default init implementation.
     *
     * @return void
     */
    public function init(): void {
        if ( ! $this->is_enabled() ) {
            return;
        }

        // Enqueue assets on frontend
        if ( ! is_admin() ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        }
    }

    /**
     * Default asset enqueue (override in child classes).
     *
     * @return void
     */
    public function enqueue_assets(): void {
        // Override in child classes
    }
}

