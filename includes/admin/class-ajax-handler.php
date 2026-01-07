<?php
/**
 * AJAX handler class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles AJAX requests for admin functionality.
 */
class WPA11Y_Ajax_Handler {

    /**
     * Handle scan AJAX request.
     *
     * @return void
     */
    public function handle_scan(): void {
        // Verify nonce
        check_ajax_referer( 'wpa11y_admin_nonce', 'nonce' );

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to perform this action.', 'free-wcag' ),
            ], 403 );
        }

        $scan_type = sanitize_text_field( $_POST['scan_type'] ?? 'full' );
        $batch     = absint( $_POST['batch'] ?? 0 );

        // Get settings for batch size
        $settings   = WPA11Y_Settings::get_settings();
        $batch_size = $settings['scanner']['batch_size'] ?? 50;

        // Get posts to scan
        $args = [
            'post_type'      => [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => $batch_size,
            'offset'         => $batch * $batch_size,
            'fields'         => 'ids',
        ];

        $posts = get_posts( $args );

        if ( empty( $posts ) ) {
            // No more posts, scan complete
            $this->finalize_scan( $scan_type );
            wp_send_json_success( [
                'complete' => true,
                'message'  => __( 'Scan complete.', 'free-wcag' ),
            ] );
        }

        $issues = [];

        foreach ( $posts as $post_id ) {
            $post_issues = $this->scan_post( $post_id, $scan_type );
            $issues      = array_merge( $issues, $post_issues );
        }

        // Store issues in database
        $this->store_issues( $issues );

        // Calculate progress
        $total_posts = wp_count_posts( 'post' )->publish + wp_count_posts( 'page' )->publish;
        $scanned     = min( ( $batch + 1 ) * $batch_size, $total_posts );
        $progress    = (int) round( ( $scanned / $total_posts ) * 100 );

        wp_send_json_success( [
            'complete'     => false,
            'batch'        => $batch,
            'scanned'      => $scanned,
            'total'        => $total_posts,
            'progress'     => $progress,
            'issues_found' => count( $issues ),
        ] );
    }

    /**
     * Scan a single post for accessibility issues.
     *
     * @param int    $post_id   Post ID.
     * @param string $scan_type Type of scan.
     * @return array Found issues.
     */
    private function scan_post( int $post_id, string $scan_type ): array {
        $issues  = [];
        $post    = get_post( $post_id );
        $content = $post->post_content;

        // Scan for images without alt text
        if ( in_array( $scan_type, [ 'full', 'images' ], true ) ) {
            $issues = array_merge( $issues, $this->check_images( $post_id, $content ) );
        }

        // Scan for heading hierarchy
        if ( in_array( $scan_type, [ 'full', 'headings' ], true ) ) {
            $issues = array_merge( $issues, $this->check_headings( $post_id, $content ) );
        }

        // Scan for links
        if ( in_array( $scan_type, [ 'full', 'links' ], true ) ) {
            $issues = array_merge( $issues, $this->check_links( $post_id, $content ) );
        }

        return $issues;
    }

    /**
     * Check images for alt text.
     *
     * @param int    $post_id Post ID.
     * @param string $content Post content.
     * @return array Issues found.
     */
    private function check_images( int $post_id, string $content ): array {
        $issues = [];

        // Match img tags
        preg_match_all( '/<img[^>]+>/i', $content, $matches );

        foreach ( $matches[0] as $img ) {
            // Check for alt attribute
            if ( ! preg_match( '/alt\s*=\s*["\'][^"\']+["\']/', $img ) ) {
                // Get src for identification
                preg_match( '/src\s*=\s*["\']([^"\']+)["\']/', $img, $src_match );
                
                $issues[] = [
                    'post_id'          => $post_id,
                    'scan_type'        => 'images',
                    'issue_code'       => 'img_no_alt',
                    'severity'         => 'error',
                    'element_selector' => $img,
                    'issue_data'       => wp_json_encode( [
                        'src'     => $src_match[1] ?? '',
                        'wcag'    => '1.1.1',
                        'message' => __( 'Image missing alt attribute', 'free-wcag' ),
                    ] ),
                ];
            } elseif ( preg_match( '/alt\s*=\s*["\']["\']/', $img ) ) {
                // Empty alt - might be decorative, but flag as warning
                preg_match( '/src\s*=\s*["\']([^"\']+)["\']/', $img, $src_match );
                
                $issues[] = [
                    'post_id'          => $post_id,
                    'scan_type'        => 'images',
                    'issue_code'       => 'img_empty_alt',
                    'severity'         => 'warning',
                    'element_selector' => $img,
                    'issue_data'       => wp_json_encode( [
                        'src'     => $src_match[1] ?? '',
                        'wcag'    => '1.1.1',
                        'message' => __( 'Image has empty alt (verify if decorative)', 'free-wcag' ),
                    ] ),
                ];
            }
        }

        return $issues;
    }

    /**
     * Check heading hierarchy.
     *
     * @param int    $post_id Post ID.
     * @param string $content Post content.
     * @return array Issues found.
     */
    private function check_headings( int $post_id, string $content ): array {
        $issues = [];

        // Match all headings
        preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_OFFSET_CAPTURE );

        if ( empty( $matches[1] ) ) {
            return $issues;
        }

        $previous_level = 0;

        foreach ( $matches[1] as $index => $match ) {
            $level = (int) $match[0];
            $text  = strip_tags( $matches[2][ $index ][0] );

            // Check for skipped levels (e.g., h2 to h4)
            if ( $previous_level > 0 && $level > $previous_level + 1 ) {
                $issues[] = [
                    'post_id'          => $post_id,
                    'scan_type'        => 'headings',
                    'issue_code'       => 'heading_skip',
                    'severity'         => 'warning',
                    'element_selector' => "h{$level}",
                    'issue_data'       => wp_json_encode( [
                        'text'          => $text,
                        'level'         => $level,
                        'previous'      => $previous_level,
                        'wcag'          => '1.3.1',
                        'message'       => sprintf(
                            /* translators: 1: Current heading level, 2: Previous heading level */
                            __( 'Heading level skipped: H%1$d follows H%2$d', 'free-wcag' ),
                            $level,
                            $previous_level
                        ),
                    ] ),
                ];
            }

            // Check for empty headings
            if ( empty( trim( $text ) ) ) {
                $issues[] = [
                    'post_id'          => $post_id,
                    'scan_type'        => 'headings',
                    'issue_code'       => 'heading_empty',
                    'severity'         => 'error',
                    'element_selector' => "h{$level}",
                    'issue_data'       => wp_json_encode( [
                        'level'   => $level,
                        'wcag'    => '1.3.1',
                        'message' => __( 'Empty heading found', 'free-wcag' ),
                    ] ),
                ];
            }

            $previous_level = $level;
        }

        return $issues;
    }

    /**
     * Check links for accessibility issues.
     *
     * @param int    $post_id Post ID.
     * @param string $content Post content.
     * @return array Issues found.
     */
    private function check_links( int $post_id, string $content ): array {
        $issues = [];

        // Match all links
        preg_match_all( '/<a[^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER );

        $generic_texts = [
            'click here',
            'here',
            'read more',
            'more',
            'learn more',
            'link',
            'this link',
        ];

        foreach ( $matches as $match ) {
            $full_tag   = $match[0];
            $link_text  = strip_tags( $match[1] );
            $clean_text = strtolower( trim( $link_text ) );

            // Check for generic link text
            if ( in_array( $clean_text, $generic_texts, true ) ) {
                // Check if aria-label provides context
                if ( ! preg_match( '/aria-label\s*=\s*["\'][^"\']+["\']/', $full_tag ) ) {
                    $issues[] = [
                        'post_id'          => $post_id,
                        'scan_type'        => 'links',
                        'issue_code'       => 'link_generic_text',
                        'severity'         => 'warning',
                        'element_selector' => $full_tag,
                        'issue_data'       => wp_json_encode( [
                            'text'    => $link_text,
                            'wcag'    => '2.4.4',
                            'message' => __( 'Link has generic text without context', 'free-wcag' ),
                        ] ),
                    ];
                }
            }

            // Check for empty links
            if ( empty( trim( $link_text ) ) ) {
                // Check for aria-label or title
                if ( ! preg_match( '/(aria-label|title)\s*=\s*["\'][^"\']+["\']/', $full_tag ) ) {
                    $issues[] = [
                        'post_id'          => $post_id,
                        'scan_type'        => 'links',
                        'issue_code'       => 'link_empty',
                        'severity'         => 'error',
                        'element_selector' => $full_tag,
                        'issue_data'       => wp_json_encode( [
                            'wcag'    => '2.4.4',
                            'message' => __( 'Link has no accessible name', 'free-wcag' ),
                        ] ),
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Store issues in database.
     *
     * @param array $issues Issues to store.
     * @return void
     */
    private function store_issues( array $issues ): void {
        global $wpdb;

        $table = $wpdb->prefix . 'a11y_scan_results';

        foreach ( $issues as $issue ) {
            $wpdb->insert(
                $table,
                [
                    'post_id'          => $issue['post_id'],
                    'scan_type'        => $issue['scan_type'],
                    'issue_code'       => $issue['issue_code'],
                    'issue_data'       => $issue['issue_data'],
                    'severity'         => $issue['severity'],
                    'element_selector' => $issue['element_selector'],
                    'scanned_at'       => current_time( 'mysql' ),
                ],
                [ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
            );
        }
    }

    /**
     * Finalize scan and record history.
     *
     * @param string $scan_type Type of scan.
     * @return void
     */
    private function finalize_scan( string $scan_type ): void {
        global $wpdb;

        $results_table = $wpdb->prefix . 'a11y_scan_results';
        $history_table = $wpdb->prefix . 'a11y_scan_history';

        // Get summary
        $summary = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN severity = 'error' THEN 1 ELSE 0 END) as errors,
                    SUM(CASE WHEN severity = 'warning' THEN 1 ELSE 0 END) as warnings,
                    SUM(CASE WHEN severity = 'notice' THEN 1 ELSE 0 END) as notices
                FROM $results_table 
                WHERE DATE(scanned_at) = %s",
                current_time( 'Y-m-d' )
            ),
            ARRAY_A
        );

        // Count posts scanned
        $posts_scanned = wp_count_posts( 'post' )->publish + wp_count_posts( 'page' )->publish;

        // Insert history record
        $wpdb->insert(
            $history_table,
            [
                'scan_type'     => $scan_type,
                'total_issues'  => $summary['total'] ?? 0,
                'errors'        => $summary['errors'] ?? 0,
                'warnings'      => $summary['warnings'] ?? 0,
                'notices'       => $summary['notices'] ?? 0,
                'posts_scanned' => $posts_scanned,
                'scanned_at'    => current_time( 'mysql' ),
            ],
            [ '%s', '%d', '%d', '%d', '%d', '%d', '%s' ]
        );
    }

    /**
     * Handle save settings AJAX request.
     *
     * @return void
     */
    public function handle_save_settings(): void {
        check_ajax_referer( 'wpa11y_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to perform this action.', 'free-wcag' ),
            ], 403 );
        }

        $settings = json_decode( stripslashes( $_POST['settings'] ?? '' ), true );

        if ( ! is_array( $settings ) ) {
            wp_send_json_error( [
                'message' => __( 'Invalid settings data.', 'free-wcag' ),
            ], 400 );
        }

        $saved = WPA11Y_Settings::save_settings( $settings );

        wp_send_json_success( [
            'message'  => __( 'Settings saved successfully.', 'free-wcag' ),
            'settings' => $saved,
        ] );
    }
}

