<?php
/**
 * Toolbar renderer class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles the frontend accessibility toolbar.
 */
class WPA11Y_Toolbar {

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
     * Render the toolbar.
     *
     * @return void
     */
    public function render(): void {
        $position = $this->settings['global']['toolbar_position'] ?? 'left';
        $features = $this->get_enabled_features();

        require WPA11Y_PLUGIN_DIR . 'templates/frontend/toolbar.php';
    }

    /**
     * Get enabled features for the toolbar.
     *
     * @return array
     */
    private function get_enabled_features(): array {
        $features = [];

        // Visual features
        if ( ! empty( $this->settings['module_visual']['enabled'] ) ) {
            $visual = $this->settings['module_visual']['features'] ?? [];
            
            if ( ! empty( $visual['high_contrast'] ) ) {
                $features['high_contrast'] = true;
            }
            if ( ! empty( $visual['grayscale'] ) ) {
                $features['grayscale'] = true;
            }
            if ( ! empty( $visual['invert_colors'] ) ) {
                $features['invert_colors'] = true;
            }
            if ( ! empty( $visual['low_saturation'] ) ) {
                $features['low_saturation'] = true;
            }
            if ( ! empty( $visual['text_resize'] ) ) {
                $features['text_resize'] = true;
            }
            if ( ! empty( $visual['text_spacing'] ) ) {
                $features['text_spacing'] = true;
            }
            if ( ! empty( $visual['readable_font'] ) ) {
                $features['readable_font'] = true;
            }
            if ( ! empty( $visual['dyslexia_font'] ) ) {
                $features['dyslexia_font'] = true;
            }
            if ( ! empty( $visual['cursor_size'] ) ) {
                $features['cursor_size'] = true;
            }
            if ( ! empty( $visual['reading_guide'] ) ) {
                $features['reading_guide'] = true;
            }
            if ( ! empty( $visual['reading_mask'] ) ) {
                $features['reading_mask'] = true;
            }
        }

        // Content features
        if ( ! empty( $this->settings['module_content']['enabled'] ) ) {
            $content = $this->settings['module_content']['features'] ?? [];
            
            if ( ! empty( $content['animation_pause'] ) ) {
                $features['animation_pause'] = true;
            }
            if ( ! empty( $content['hide_images'] ) ) {
                $features['hide_images'] = true;
            }
            if ( ! empty( $content['highlight_links'] ) ) {
                $features['highlight_links'] = true;
            }
            if ( ! empty( $content['highlight_headings'] ) ) {
                $features['highlight_headings'] = true;
            }
        }

        return $features;
    }

    /**
     * Get accessibility icon SVG.
     *
     * @return string SVG markup.
     */
    public static function get_icon(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false" width="24" height="24"><path d="M12 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z"/></svg>';
    }
}

