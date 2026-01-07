<?php
/**
 * Frontend controller class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles frontend functionality.
 */
class WPA11Y_Frontend {

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
     * Enqueue frontend assets.
     *
     * @return void
     */
    public function enqueue_assets(): void {
        // Check if ANY frontend module is enabled
        $has_frontend = $this->has_frontend_features();

        if ( ! $has_frontend && empty( $this->settings['global']['toolbar_enabled'] ) ) {
            return; // Don't load anything
        }

        // Core toolbar CSS (if enabled)
        if ( ! empty( $this->settings['global']['toolbar_enabled'] ) ) {
            $this->enqueue_toolbar_assets();
        }

        // Remediation CSS (always-on fixes like focus rings, skip links)
        if ( ! empty( $this->settings['module_navigation']['enabled'] ) ) {
            $this->enqueue_remediation_assets();
        }
    }

    /**
     * Check if any frontend features are enabled.
     *
     * @return bool
     */
    private function has_frontend_features(): bool {
        $frontend_modules = [ 'module_visual', 'module_navigation', 'module_content' ];

        foreach ( $frontend_modules as $module ) {
            if ( ! empty( $this->settings[ $module ]['enabled'] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enqueue toolbar assets.
     *
     * @return void
     */
    private function enqueue_toolbar_assets(): void {
        // Toolbar CSS
        wp_enqueue_style(
            'wpa11y-toolbar',
            WPA11Y_PLUGIN_URL . 'assets/build/css/toolbar.min.css',
            [],
            WPA11Y_VERSION
        );

        // Fallback
        if ( ! file_exists( WPA11Y_PLUGIN_DIR . 'assets/build/css/toolbar.min.css' ) ) {
            wp_enqueue_style(
                'wpa11y-toolbar',
                WPA11Y_PLUGIN_URL . 'assets/src/css/toolbar.css',
                [],
                WPA11Y_VERSION
            );
        }

        // Toolbar JS
        wp_enqueue_script(
            'wpa11y-toolbar',
            WPA11Y_PLUGIN_URL . 'assets/build/js/toolbar.min.js',
            [],
            WPA11Y_VERSION,
            true
        );

        // Fallback
        if ( ! file_exists( WPA11Y_PLUGIN_DIR . 'assets/build/js/toolbar.min.js' ) ) {
            wp_enqueue_script(
                'wpa11y-toolbar',
                WPA11Y_PLUGIN_URL . 'assets/src/js/frontend/toolbar.js',
                [],
                WPA11Y_VERSION,
                true
            );
        }

        // Pass configuration to JS
        wp_localize_script( 'wpa11y-toolbar', 'wpa11yConfig', [
            'features'      => $this->get_enabled_features(),
            'position'      => $this->settings['global']['toolbar_position'] ?? 'left',
            'theme'         => $this->settings['global']['toolbar_theme'] ?? 'auto',
            'respectPrefers' => $this->settings['global']['respect_prefers'] ?? true,
            'strings'       => [
                'openMenu'        => __( 'Open Accessibility Options', 'free-wcag' ),
                'closeMenu'       => __( 'Close', 'free-wcag' ),
                'title'           => __( 'Accessibility Options', 'free-wcag' ),
                'resetAll'        => __( 'Reset All Settings', 'free-wcag' ),
                'visualSection'   => __( 'Visual Adjustments', 'free-wcag' ),
                'navSection'      => __( 'Navigation', 'free-wcag' ),
                'readingSection'  => __( 'Reading Aids', 'free-wcag' ),
                'contrast'        => __( 'Contrast', 'free-wcag' ),
                'textSize'        => __( 'Text Size', 'free-wcag' ),
                'default'         => __( 'Default', 'free-wcag' ),
                'dark'            => __( 'Dark', 'free-wcag' ),
                'light'           => __( 'Light', 'free-wcag' ),
                'yellowBlack'     => __( 'Yellow/Black', 'free-wcag' ),
                'grayscale'       => __( 'Grayscale', 'free-wcag' ),
                'invertColors'    => __( 'Invert Colors', 'free-wcag' ),
                'lowSaturation'   => __( 'Low Saturation', 'free-wcag' ),
                'dyslexiaFont'    => __( 'Dyslexia-Friendly Font', 'free-wcag' ),
                'readableFont'    => __( 'Readable Font', 'free-wcag' ),
                'largeCursor'     => __( 'Large Cursor', 'free-wcag' ),
                'readingGuide'    => __( 'Reading Guide', 'free-wcag' ),
                'readingMask'     => __( 'Reading Mask', 'free-wcag' ),
                'highlightLinks'  => __( 'Highlight Links', 'free-wcag' ),
                'pauseAnimations' => __( 'Pause Animations', 'free-wcag' ),
                'hideImages'      => __( 'Hide Images', 'free-wcag' ),
                'enabled'         => __( 'enabled', 'free-wcag' ),
                'disabled'        => __( 'disabled', 'free-wcag' ),
            ],
        ] );
    }

    /**
     * Enqueue remediation assets.
     *
     * @return void
     */
    private function enqueue_remediation_assets(): void {
        // Remediation CSS
        wp_enqueue_style(
            'wpa11y-remediation',
            WPA11Y_PLUGIN_URL . 'assets/build/css/remediation.min.css',
            [],
            WPA11Y_VERSION
        );

        // Fallback
        if ( ! file_exists( WPA11Y_PLUGIN_DIR . 'assets/build/css/remediation.min.css' ) ) {
            wp_enqueue_style(
                'wpa11y-remediation',
                WPA11Y_PLUGIN_URL . 'assets/src/css/remediation.css',
                [],
                WPA11Y_VERSION
            );
        }

        // Remediation JS (if needed)
        if ( $this->needs_remediation_js() ) {
            wp_enqueue_script(
                'wpa11y-remediation',
                WPA11Y_PLUGIN_URL . 'assets/build/js/remediation.min.js',
                [],
                WPA11Y_VERSION,
                true
            );

            // Fallback
            if ( ! file_exists( WPA11Y_PLUGIN_DIR . 'assets/build/js/remediation.min.js' ) ) {
                wp_enqueue_script(
                    'wpa11y-remediation',
                    WPA11Y_PLUGIN_URL . 'assets/src/js/remediation/index.js',
                    [],
                    WPA11Y_VERSION,
                    true
                );
            }
        }
    }

    /**
     * Get list of enabled features.
     *
     * @return array
     */
    private function get_enabled_features(): array {
        $features = [];
        $modules  = [ 'module_visual', 'module_navigation', 'module_content' ];

        foreach ( $modules as $module ) {
            if ( empty( $this->settings[ $module ]['enabled'] ) ) {
                continue;
            }

            if ( empty( $this->settings[ $module ]['features'] ) ) {
                continue;
            }

            foreach ( $this->settings[ $module ]['features'] as $feature => $enabled ) {
                if ( $enabled ) {
                    $features[] = $feature;
                }
            }
        }

        return $features;
    }

    /**
     * Check if remediation JS is needed.
     *
     * @return bool
     */
    private function needs_remediation_js(): bool {
        $js_features = [
            'module_navigation' => [ 'focus_not_obscured', 'keyboard_nav' ],
            'module_aria'       => [ 'form_labels', 'live_regions' ],
            'module_interaction' => [ 'drag_alternatives' ],
        ];

        foreach ( $js_features as $module => $features ) {
            if ( empty( $this->settings[ $module ]['enabled'] ) ) {
                continue;
            }

            foreach ( $features as $feature ) {
                if ( ! empty( $this->settings[ $module ]['features'][ $feature ] ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Render skip links.
     *
     * @return void
     */
    public function render_skip_links(): void {
        $targets = $this->settings['module_navigation']['settings']['skip_link_targets'] ?? [ 'content' ];
        
        require WPA11Y_PLUGIN_DIR . 'templates/frontend/skip-links.php';
    }

    /**
     * Render live region for screen reader announcements.
     *
     * @return void
     */
    public function render_live_region(): void {
        ?>
        <div id="wpa11y-announcer" 
             role="status" 
             aria-live="polite" 
             aria-atomic="true"
             class="wpa11y-screen-reader-text">
        </div>
        <?php
    }

    /**
     * Enhance "Read more" link with post title context.
     *
     * @param string $link           The Read More link HTML.
     * @param string $more_link_text The Read More text.
     * @return string Modified link HTML.
     */
    public function enhance_read_more_link( string $link, string $more_link_text ): string {
        global $post;

        if ( ! $post ) {
            return $link;
        }

        $title = esc_attr( $post->post_title );

        // Add screen reader text
        $sr_text = sprintf(
            '<span class="wpa11y-screen-reader-text"> %s</span>',
            sprintf(
                /* translators: %s: Post title */
                esc_html__( 'about %s', 'free-wcag' ),
                $title
            )
        );

        // Inject before closing </a>
        return str_replace( '</a>', $sr_text . '</a>', $link );
    }

    /**
     * Enhance excerpt "Read more" text.
     *
     * @param string $more The excerpt more text.
     * @return string Modified more text.
     */
    public function enhance_excerpt_more( string $more ): string {
        global $post;

        if ( ! $post ) {
            return $more;
        }

        return sprintf(
            ' <a href="%s" class="wpa11y-read-more" aria-label="%s">%s</a>',
            esc_url( get_permalink( $post ) ),
            sprintf(
                /* translators: %s: Post title */
                esc_attr__( 'Read more about %s', 'free-wcag' ),
                esc_attr( $post->post_title )
            ),
            esc_html__( 'Read more', 'free-wcag' )
        );
    }
}

