<?php
/**
 * Dashboard renderer class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles dashboard rendering and data.
 */
class WPA11Y_Dashboard {

    /**
     * Get compliance score based on enabled features.
     *
     * @param array $settings Plugin settings.
     * @return int Score percentage (0-100).
     */
    public static function get_compliance_score( array $settings ): int {
        $enabled = 0;
        $total   = 0;

        // Count enabled features vs total
        $modules = [ 'module_visual', 'module_navigation', 'module_content', 'module_aria', 'module_interaction' ];

        foreach ( $modules as $module ) {
            if ( isset( $settings[ $module ]['features'] ) ) {
                foreach ( $settings[ $module ]['features'] as $feature => $feature_enabled ) {
                    $total++;
                    if ( $feature_enabled && ! empty( $settings[ $module ]['enabled'] ) ) {
                        $enabled++;
                    }
                }
            }
        }

        return $total > 0 ? (int) round( ( $enabled / $total ) * 100 ) : 0;
    }

    /**
     * Get compliance level label.
     *
     * @param int $score Compliance score.
     * @return string Level label.
     */
    public static function get_compliance_level( int $score ): string {
        if ( $score >= 90 ) {
            return 'high';
        }
        if ( $score >= 70 ) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Get module display data.
     *
     * @return array Module configuration for display.
     */
    public static function get_modules_display(): array {
        return [
            'module_visual' => [
                'title'       => __( 'Visual Adjustments', 'free-wcag' ),
                'description' => __( 'High contrast, fonts, colors, and display options for users', 'free-wcag' ),
                'icon'        => 'visibility',
                'wcag'        => '1.4.3, 1.4.4, 1.4.12',
                'features'    => [
                    'high_contrast'   => __( 'High Contrast Mode', 'free-wcag' ),
                    'grayscale'       => __( 'Grayscale Mode', 'free-wcag' ),
                    'invert_colors'   => __( 'Invert Colors', 'free-wcag' ),
                    'low_saturation'  => __( 'Low Saturation', 'free-wcag' ),
                    'text_resize'     => __( 'Text Resize', 'free-wcag' ),
                    'text_spacing'    => __( 'Text Spacing', 'free-wcag' ),
                    'readable_font'   => __( 'Readable Font (Atkinson)', 'free-wcag' ),
                    'dyslexia_font'   => __( 'Dyslexia Font (OpenDyslexic)', 'free-wcag' ),
                    'cursor_size'     => __( 'Large Cursor', 'free-wcag' ),
                    'reading_guide'   => __( 'Reading Guide', 'free-wcag' ),
                    'reading_mask'    => __( 'Reading Mask', 'free-wcag' ),
                ],
            ],
            'module_navigation' => [
                'title'       => __( 'Navigation & Focus', 'free-wcag' ),
                'description' => __( 'Keyboard navigation, focus indicators, and skip links', 'free-wcag' ),
                'icon'        => 'keyboard',
                'wcag'        => '2.1.1, 2.4.1, 2.4.7, 2.4.11',
                'features'    => [
                    'skip_links'         => __( 'Skip Links', 'free-wcag' ),
                    'focus_ring'         => __( 'Focus Ring Enforcement', 'free-wcag' ),
                    'focus_not_obscured' => __( 'Focus Not Obscured (WCAG 2.2)', 'free-wcag' ),
                    'keyboard_nav'       => __( 'Enhanced Keyboard Navigation', 'free-wcag' ),
                    'link_highlighting'  => __( 'Link Highlighting', 'free-wcag' ),
                ],
            ],
            'module_content' => [
                'title'       => __( 'Content & Reading', 'free-wcag' ),
                'description' => __( 'Reading aids and content presentation options', 'free-wcag' ),
                'icon'        => 'format-align-left',
                'wcag'        => '1.4.1, 2.2.2, 2.3.1',
                'features'    => [
                    'animation_pause'     => __( 'Pause Animations', 'free-wcag' ),
                    'hide_images'         => __( 'Hide Images', 'free-wcag' ),
                    'highlight_links'     => __( 'Highlight Links', 'free-wcag' ),
                    'highlight_headings'  => __( 'Highlight Headings', 'free-wcag' ),
                ],
            ],
            'module_aria' => [
                'title'       => __( 'ARIA & Semantics', 'free-wcag' ),
                'description' => __( 'Automatic ARIA attributes and semantic enhancements', 'free-wcag' ),
                'icon'        => 'editor-code',
                'wcag'        => '1.3.1, 2.4.4, 4.1.2, 4.1.3',
                'badge'       => 'advanced',
                'features'    => [
                    'landmark_roles' => __( 'Landmark Roles', 'free-wcag' ),
                    'form_labels'    => __( 'Form Label Automation', 'free-wcag' ),
                    'link_purpose'   => __( '"Read More" Enhancement', 'free-wcag' ),
                    'live_regions'   => __( 'Live Region Announcements', 'free-wcag' ),
                ],
            ],
            'module_interaction' => [
                'title'       => __( 'Interaction (WCAG 2.2)', 'free-wcag' ),
                'description' => __( 'Target size and drag alternatives for WCAG 2.2 compliance', 'free-wcag' ),
                'icon'        => 'move',
                'wcag'        => '2.5.7, 2.5.8',
                'badge'       => 'new',
                'features'    => [
                    'target_size'       => __( 'Target Size (24×24px min)', 'free-wcag' ),
                    'drag_alternatives' => __( 'Drag Alternatives', 'free-wcag' ),
                ],
            ],
        ];
    }

    /**
     * Get recent scan results summary.
     *
     * @return array Scan summary data.
     */
    public static function get_scan_summary(): array {
        global $wpdb;

        $table = $wpdb->prefix . 'a11y_scan_history';

        // Check if table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table
        ) );

        if ( ! $table_exists ) {
            return [
                'last_scan'     => null,
                'total_issues'  => 0,
                'errors'        => 0,
                'warnings'      => 0,
                'notices'       => 0,
                'posts_scanned' => 0,
            ];
        }

        $latest = $wpdb->get_row(
            "SELECT * FROM $table ORDER BY scanned_at DESC LIMIT 1",
            ARRAY_A
        );

        if ( ! $latest ) {
            return [
                'last_scan'     => null,
                'total_issues'  => 0,
                'errors'        => 0,
                'warnings'      => 0,
                'notices'       => 0,
                'posts_scanned' => 0,
            ];
        }

        return [
            'last_scan'     => $latest['scanned_at'],
            'total_issues'  => (int) $latest['total_issues'],
            'errors'        => (int) $latest['errors'],
            'warnings'      => (int) $latest['warnings'],
            'notices'       => (int) $latest['notices'],
            'posts_scanned' => (int) $latest['posts_scanned'],
        ];
    }

    /**
     * Get quick stats for dashboard.
     *
     * @return array Stats data.
     */
    public static function get_quick_stats(): array {
        global $wpdb;

        // Count posts/pages
        $post_count = wp_count_posts( 'post' );
        $page_count = wp_count_posts( 'page' );

        $total_content = ( $post_count->publish ?? 0 ) + ( $page_count->publish ?? 0 );

        // Count images without alt
        $images_without_alt = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND (pm.meta_value IS NULL OR pm.meta_value = '')"
        );

        return [
            'total_content'       => (int) $total_content,
            'images_without_alt'  => (int) $images_without_alt,
        ];
    }
}

