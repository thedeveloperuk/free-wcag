<?php
/**
 * Settings management class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles plugin settings storage and retrieval.
 */
class WPA11Y_Settings {

    /**
     * Option name in wp_options table.
     */
    const OPTION_NAME = 'wpa11y_settings';

    /**
     * Default settings structure.
     *
     * @return array
     */
    public static function get_defaults(): array {
        return [
            'version' => WPA11Y_VERSION,

            // Global settings
            'global' => [
                'toolbar_enabled'  => true,
                'toolbar_position' => 'left',    // left, right, bottom
                'toolbar_theme'    => 'auto',    // auto, light, dark
                'safe_mode'        => false,     // Admin-only testing
                'respect_prefers'  => true,      // Honor OS preferences
            ],

            // Module: Visual
            'module_visual' => [
                'enabled'  => true,
                'features' => [
                    'high_contrast'   => true,
                    'grayscale'       => true,
                    'invert_colors'   => true,
                    'low_saturation'  => true,
                    'text_resize'     => true,
                    'text_spacing'    => true,
                    'readable_font'   => true,
                    'dyslexia_font'   => true,
                    'cursor_size'     => true,
                    'reading_guide'   => true,
                    'reading_mask'    => true,
                ],
                'settings' => [
                    'max_font_scale' => 2.0,
                    'default_font'   => 'atkinson',
                ],
            ],

            // Module: Navigation
            'module_navigation' => [
                'enabled'  => true,
                'features' => [
                    'skip_links'         => true,
                    'focus_ring'         => true,
                    'focus_not_obscured' => true,
                    'keyboard_nav'       => true,
                    'link_highlighting'  => true,
                ],
                'settings' => [
                    'focus_ring_color'   => '#0066cc',
                    'focus_ring_width'   => 2,
                    'skip_link_targets'  => [ 'content', 'navigation', 'footer' ],
                ],
            ],

            // Module: Content
            'module_content' => [
                'enabled'  => true,
                'features' => [
                    'animation_pause'     => true,
                    'hide_images'         => true,
                    'highlight_links'     => true,
                    'highlight_headings'  => true,
                ],
            ],

            // Module: ARIA
            'module_aria' => [
                'enabled'  => false, // Disabled by default (advanced)
                'features' => [
                    'landmark_roles' => true,
                    'form_labels'    => true,
                    'link_purpose'   => true,
                    'live_regions'   => true,
                ],
                'settings' => [
                    'auto_inject' => false,
                ],
            ],

            // Module: Interaction (WCAG 2.2)
            'module_interaction' => [
                'enabled'  => true,
                'features' => [
                    'target_size'       => true,
                    'drag_alternatives' => false,
                ],
            ],

            // Scanner settings
            'scanner' => [
                'batch_size'       => 50,
                'auto_scan'        => false,
                'scan_on_publish'  => true,
                'max_pages'        => 0,     // 0 = unlimited
                'excluded_types'   => [],    // Post types to exclude from scan
            ],
        ];
    }

    /**
     * Get current settings, merged with defaults.
     *
     * @return array
     */
    public static function get_settings(): array {
        $stored   = get_option( self::OPTION_NAME, [] );
        $defaults = self::get_defaults();

        return self::merge_recursive( $defaults, $stored );
    }

    /**
     * Save settings.
     *
     * @param array $settings Settings to save.
     * @return array Sanitized settings.
     */
    public static function save_settings( array $settings ): array {
        $sanitized = self::sanitize( $settings );
        
        update_option( self::OPTION_NAME, $sanitized, true );
        
        // Clear any cached data - both custom and WordPress option cache
        wp_cache_delete( 'wpa11y_settings', 'wpa11y' );
        wp_cache_delete( self::OPTION_NAME, 'options' );
        wp_cache_delete( 'alloptions', 'options' );
        
        /**
         * Fires after settings are saved.
         *
         * @param array $sanitized Sanitized settings.
         */
        do_action( 'wpa11y_settings_saved', $sanitized );
        
        return $sanitized;
    }

    /**
     * Reset settings to defaults.
     *
     * @return array Default settings.
     */
    public static function reset_settings(): array {
        $defaults = self::get_defaults();
        
        update_option( self::OPTION_NAME, $defaults, true );
        
        wp_cache_delete( 'wpa11y_settings', 'wpa11y' );
        
        /**
         * Fires after settings are reset.
         */
        do_action( 'wpa11y_settings_reset' );
        
        return $defaults;
    }

    /**
     * Sanitize settings input.
     *
     * @param array $input Raw input.
     * @return array Sanitized settings.
     */
    public static function sanitize( array $input ): array {
        $defaults  = self::get_defaults();
        $sanitized = [];

        // Global settings
        if ( isset( $input['global'] ) ) {
            $sanitized['global'] = [
                'toolbar_enabled'  => self::sanitize_bool( $input['global']['toolbar_enabled'] ?? $defaults['global']['toolbar_enabled'] ),
                'toolbar_position' => self::sanitize_enum( 
                    $input['global']['toolbar_position'] ?? '', 
                    [ 'left', 'right', 'bottom' ], 
                    $defaults['global']['toolbar_position'] 
                ),
                'toolbar_theme'    => self::sanitize_enum( 
                    $input['global']['toolbar_theme'] ?? '', 
                    [ 'auto', 'light', 'dark' ], 
                    $defaults['global']['toolbar_theme'] 
                ),
                'safe_mode'        => self::sanitize_bool( $input['global']['safe_mode'] ?? $defaults['global']['safe_mode'] ),
                'respect_prefers'  => self::sanitize_bool( $input['global']['respect_prefers'] ?? $defaults['global']['respect_prefers'] ),
            ];
        }

        // Module settings
        $modules = [ 'module_visual', 'module_navigation', 'module_content', 'module_aria', 'module_interaction' ];

        foreach ( $modules as $module ) {
            if ( isset( $input[ $module ] ) ) {
                // Get the enabled value - use explicit check instead of ?? false to handle boolean properly
                $enabled_value = false;
                if ( array_key_exists( 'enabled', $input[ $module ] ) ) {
                    $enabled_value = $input[ $module ]['enabled'];
                } elseif ( isset( $defaults[ $module ]['enabled'] ) ) {
                    $enabled_value = $defaults[ $module ]['enabled'];
                }

                $sanitized[ $module ] = [
                    'enabled'  => self::sanitize_bool( $enabled_value ),
                    'features' => [],
                ];

                // Sanitize features
                if ( isset( $input[ $module ]['features'] ) && is_array( $input[ $module ]['features'] ) ) {
                    $allowed_features = array_keys( $defaults[ $module ]['features'] ?? [] );

                    foreach ( $input[ $module ]['features'] as $feature => $enabled ) {
                        if ( in_array( $feature, $allowed_features, true ) ) {
                            $sanitized[ $module ]['features'][ $feature ] = self::sanitize_bool( $enabled );
                        }
                    }
                }

                // Sanitize module-specific settings
                if ( isset( $input[ $module ]['settings'] ) && isset( $defaults[ $module ]['settings'] ) ) {
                    $sanitized[ $module ]['settings'] = self::sanitize_module_settings( 
                        $module, 
                        $input[ $module ]['settings'], 
                        $defaults[ $module ]['settings'] 
                    );
                }
            }
        }

        // Scanner settings
        if ( isset( $input['scanner'] ) ) {
            $sanitized['scanner'] = [
                'batch_size'       => absint( $input['scanner']['batch_size'] ?? $defaults['scanner']['batch_size'] ),
                'auto_scan'        => self::sanitize_bool( $input['scanner']['auto_scan'] ?? $defaults['scanner']['auto_scan'] ),
                'scan_on_publish'  => self::sanitize_bool( $input['scanner']['scan_on_publish'] ?? $defaults['scanner']['scan_on_publish'] ),
                'max_pages'        => absint( $input['scanner']['max_pages'] ?? $defaults['scanner']['max_pages'] ),
                'excluded_types'   => [],
            ];

            // Enforce batch size limits
            $sanitized['scanner']['batch_size'] = max( 10, min( 100, $sanitized['scanner']['batch_size'] ) );

            // Enforce max pages limit options (0, 10, 50, 100, 500, 1000)
            $allowed_limits = [ 0, 10, 50, 100, 500, 1000 ];
            if ( ! in_array( $sanitized['scanner']['max_pages'], $allowed_limits, true ) ) {
                $sanitized['scanner']['max_pages'] = 0;
            }

            // Sanitize excluded post types
            if ( isset( $input['scanner']['excluded_types'] ) && is_array( $input['scanner']['excluded_types'] ) ) {
                $public_types = get_post_types( [ 'public' => true ], 'names' );
                foreach ( $input['scanner']['excluded_types'] as $type ) {
                    $type = sanitize_key( $type );
                    if ( in_array( $type, $public_types, true ) ) {
                        $sanitized['scanner']['excluded_types'][] = $type;
                    }
                }
            }
        }

        // Merge with defaults to ensure complete structure
        return self::merge_recursive( $defaults, $sanitized );
    }

    /**
     * Sanitize module-specific settings.
     *
     * @param string $module   Module name.
     * @param array  $input    Input settings.
     * @param array  $defaults Default settings.
     * @return array
     */
    private static function sanitize_module_settings( string $module, array $input, array $defaults ): array {
        $sanitized = [];

        switch ( $module ) {
            case 'module_visual':
                $sanitized['max_font_scale'] = floatval( $input['max_font_scale'] ?? $defaults['max_font_scale'] );
                $sanitized['max_font_scale'] = max( 1.5, min( 3.0, $sanitized['max_font_scale'] ) );
                
                $sanitized['default_font'] = self::sanitize_enum(
                    $input['default_font'] ?? '',
                    [ 'atkinson', 'opendyslexic', 'inherit' ],
                    $defaults['default_font']
                );
                break;

            case 'module_navigation':
                $sanitized['focus_ring_color'] = sanitize_hex_color( $input['focus_ring_color'] ?? '' ) ?: $defaults['focus_ring_color'];
                $sanitized['focus_ring_width'] = absint( $input['focus_ring_width'] ?? $defaults['focus_ring_width'] );
                $sanitized['focus_ring_width'] = max( 1, min( 5, $sanitized['focus_ring_width'] ) );
                
                $allowed_targets = [ 'content', 'navigation', 'footer', 'search' ];
                $sanitized['skip_link_targets'] = array_intersect(
                    (array) ( $input['skip_link_targets'] ?? [] ),
                    $allowed_targets
                );
                
                if ( empty( $sanitized['skip_link_targets'] ) ) {
                    $sanitized['skip_link_targets'] = $defaults['skip_link_targets'];
                }
                break;

            case 'module_aria':
                $sanitized['auto_inject'] = self::sanitize_bool( $input['auto_inject'] ?? $defaults['auto_inject'] );
                break;

            default:
                $sanitized = $defaults;
        }

        return $sanitized;
    }

    /**
     * Sanitize boolean value.
     *
     * @param mixed $value Value to sanitize.
     * @return bool
     */
    private static function sanitize_bool( $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Sanitize enum value.
     *
     * @param string $value   Value to check.
     * @param array  $allowed Allowed values.
     * @param string $default Default if not in allowed.
     * @return string
     */
    private static function sanitize_enum( string $value, array $allowed, string $default ): string {
        return in_array( $value, $allowed, true ) ? $value : $default;
    }

    /**
     * Recursively merge arrays.
     *
     * @param array $defaults Default values.
     * @param array $values   Values to merge.
     * @return array
     */
    private static function merge_recursive( array $defaults, array $values ): array {
        $merged = $defaults;

        foreach ( $values as $key => $value ) {
            if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
                $merged[ $key ] = self::merge_recursive( $merged[ $key ], $value );
            } else {
                $merged[ $key ] = $value;
            }
        }

        return $merged;
    }

    /**
     * Get a specific setting value.
     *
     * @param string $path    Dot-notation path (e.g., 'global.toolbar_enabled').
     * @param mixed  $default Default value if not found.
     * @return mixed
     */
    public static function get( string $path, $default = null ) {
        $settings = self::get_settings();
        $keys     = explode( '.', $path );
        $value    = $settings;

        foreach ( $keys as $key ) {
            if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
                return $default;
            }
            $value = $value[ $key ];
        }

        return $value;
    }

    /**
     * Get available post types for scanner.
     *
     * @return array Array of post type objects with name and label.
     */
    public static function get_scannable_post_types(): array {
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $result     = [];

        foreach ( $post_types as $post_type ) {
            // Skip attachments
            if ( 'attachment' === $post_type->name ) {
                continue;
            }

            $result[] = [
                'name'  => $post_type->name,
                'label' => $post_type->labels->name,
                'count' => wp_count_posts( $post_type->name )->publish,
            ];
        }

        return $result;
    }
}

