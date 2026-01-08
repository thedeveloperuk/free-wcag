<?php
/**
 * Asset loader class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles conditional asset loading for performance.
 */
class WPA11Y_Asset_Loader {

    /**
     * Plugin settings.
     *
     * @var array
     */
    private array $settings;

    /**
     * Constructor.
     *
     * @param array $settings Plugin settings.
     */
    public function __construct( array $settings ) {
        $this->settings = $settings;
    }

    /**
     * Get CSS custom properties for enabled features.
     *
     * @return string CSS custom properties.
     */
    public function get_css_variables(): string {
        $nav_settings = $this->settings['module_navigation']['settings'] ?? [];

        $vars = [
            '--wpa11y-focus-color' => $nav_settings['focus_ring_color'] ?? '#0066cc',
            '--wpa11y-focus-width' => ( $nav_settings['focus_ring_width'] ?? 2 ) . 'px',
        ];

        $css = ':root {' . PHP_EOL;
        foreach ( $vars as $prop => $value ) {
            $css .= "  {$prop}: {$value};" . PHP_EOL;
        }
        $css .= '}';

        return $css;
    }

    /**
     * Get critical CSS to inline in head.
     *
     * @return string Critical CSS.
     */
    public function get_critical_css(): string {
        $css = '';

        // Skip links critical CSS
        if ( $this->is_feature_enabled( 'module_navigation', 'skip_links' ) ) {
            $css .= '
.wpa11y-skip-links {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 100000;
}
.wpa11y-skip-link {
  position: absolute;
  top: -100px;
  left: 10px;
  padding: 12px 24px;
  background: #000;
  color: #fff;
  font-weight: 600;
  text-decoration: none;
  border-radius: 0 0 4px 4px;
  transition: top 0.2s;
}
.wpa11y-skip-link:focus {
  top: 0;
  outline: 3px solid var(--wpa11y-focus-color, #0066cc);
  outline-offset: 2px;
}';
        }

        // Screen reader text
        $css .= '
.wpa11y-screen-reader-text {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}';

        return $css;
    }

    /**
     * Check if a specific feature is enabled.
     *
     * @param string $module  Module key.
     * @param string $feature Feature key.
     * @return bool
     */
    private function is_feature_enabled( string $module, string $feature ): bool {
        if ( empty( $this->settings[ $module ]['enabled'] ) ) {
            return false;
        }

        return ! empty( $this->settings[ $module ]['features'][ $feature ] );
    }

    /**
     * Should load toolbar assets.
     *
     * @return bool
     */
    public function should_load_toolbar(): bool {
        return ! empty( $this->settings['global']['toolbar_enabled'] );
    }

    /**
     * Should load remediation assets.
     *
     * @return bool
     */
    public function should_load_remediation(): bool {
        return ! empty( $this->settings['module_navigation']['enabled'] ) ||
               ! empty( $this->settings['module_aria']['enabled'] ) ||
               ! empty( $this->settings['module_interaction']['enabled'] );
    }
}

