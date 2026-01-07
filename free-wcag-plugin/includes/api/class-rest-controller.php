<?php
/**
 * REST API controller class.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles REST API endpoints.
 */
class WPA11Y_REST_Controller {

    /**
     * Namespace for REST routes.
     */
    const NAMESPACE = 'wpa11y/v1';

    /**
     * Register REST routes.
     *
     * @return void
     */
    public function register_routes(): void {
        // Settings endpoints
        register_rest_route(
            self::NAMESPACE,
            '/settings',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_settings' ],
                    'permission_callback' => [ $this, 'admin_permissions' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'update_settings' ],
                    'permission_callback' => [ $this, 'admin_permissions' ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'reset_settings' ],
                    'permission_callback' => [ $this, 'admin_permissions' ],
                ],
            ]
        );

        // Module settings endpoint
        register_rest_route(
            self::NAMESPACE,
            '/settings/module/(?P<module_id>[a-z_]+)',
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_module_settings' ],
                'permission_callback' => [ $this, 'admin_permissions' ],
                'args'                => [
                    'module_id' => [
                        'required'          => true,
                        'validate_callback' => [ $this, 'validate_module_id' ],
                    ],
                ],
            ]
        );

        // Scanner endpoints
        register_rest_route(
            self::NAMESPACE,
            '/scan/start',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'start_scan' ],
                'permission_callback' => [ $this, 'editor_permissions' ],
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/scan/batch',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'process_scan_batch' ],
                'permission_callback' => [ $this, 'editor_permissions' ],
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/scan/results',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_scan_results' ],
                'permission_callback' => [ $this, 'editor_permissions' ],
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/scan/resolve/(?P<issue_id>\d+)',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'resolve_issue' ],
                'permission_callback' => [ $this, 'editor_permissions' ],
                'args'                => [
                    'issue_id' => [
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ],
                ],
            ]
        );

        // Reports endpoints
        register_rest_route(
            self::NAMESPACE,
            '/reports/summary',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_report_summary' ],
                'permission_callback' => [ $this, 'admin_permissions' ],
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/reports/export/(?P<format>[a-z]+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'export_report' ],
                'permission_callback' => [ $this, 'admin_permissions' ],
                'args'                => [
                    'format' => [
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return in_array( $param, [ 'pdf', 'csv', 'json' ], true );
                        },
                    ],
                ],
            ]
        );
    }

    /**
     * Check admin permissions.
     *
     * @return bool
     */
    public function admin_permissions(): bool {
        return current_user_can( 'manage_options' );
    }

    /**
     * Check editor permissions.
     *
     * @return bool
     */
    public function editor_permissions(): bool {
        return current_user_can( 'edit_posts' );
    }

    /**
     * Validate module ID.
     *
     * @param string $module_id Module ID to validate.
     * @return bool
     */
    public function validate_module_id( string $module_id ): bool {
        $valid_modules = [
            'module_visual',
            'module_navigation',
            'module_content',
            'module_aria',
            'module_interaction',
        ];

        return in_array( $module_id, $valid_modules, true );
    }

    /**
     * Get settings.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_settings( WP_REST_Request $request ): WP_REST_Response {
        $settings = WPA11Y_Settings::get_settings();

        return new WP_REST_Response( $settings, 200 );
    }

    /**
     * Update settings.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function update_settings( WP_REST_Request $request ): WP_REST_Response {
        $body = $request->get_json_params();

        if ( empty( $body ) ) {
            return new WP_REST_Response(
                [ 'message' => __( 'No settings provided.', 'free-wcag' ) ],
                400
            );
        }

        $saved = WPA11Y_Settings::save_settings( $body );

        return new WP_REST_Response( [
            'message'  => __( 'Settings saved successfully.', 'free-wcag' ),
            'settings' => $saved,
        ], 200 );
    }

    /**
     * Reset settings to defaults.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function reset_settings( WP_REST_Request $request ): WP_REST_Response {
        $defaults = WPA11Y_Settings::reset_settings();

        return new WP_REST_Response( $defaults, 200 );
    }

    /**
     * Update module settings.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function update_module_settings( WP_REST_Request $request ): WP_REST_Response {
        $module_id = $request->get_param( 'module_id' );
        $body      = $request->get_json_params();

        $settings                 = WPA11Y_Settings::get_settings();
        $settings[ $module_id ]   = array_merge(
            $settings[ $module_id ] ?? [],
            $body
        );

        $saved = WPA11Y_Settings::save_settings( $settings );

        return new WP_REST_Response( [
            'message'  => __( 'Module settings saved.', 'free-wcag' ),
            'module'   => $saved[ $module_id ] ?? [],
        ], 200 );
    }

    /**
     * Start a scan.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function start_scan( WP_REST_Request $request ): WP_REST_Response {
        $body      = $request->get_json_params();
        $scan_type = isset( $body['type'] ) ? sanitize_text_field( $body['type'] ) : 'full';

        // Validate scan type
        $allowed_types = [ 'full', 'images', 'headings', 'links' ];
        if ( ! in_array( $scan_type, $allowed_types, true ) ) {
            $scan_type = 'full';
        }

        // Get excluded types and max pages from request or settings
        $settings       = WPA11Y_Settings::get_settings();
        $excluded_types = isset( $body['excluded_types'] ) && is_array( $body['excluded_types'] )
            ? array_map( 'sanitize_key', $body['excluded_types'] )
            : ( $settings['scanner']['excluded_types'] ?? [] );
        
        $max_pages = isset( $body['max_pages'] ) 
            ? absint( $body['max_pages'] ) 
            : ( $settings['scanner']['max_pages'] ?? 0 );

        // Clear previous results using safe delete
        global $wpdb;
        $table = $wpdb->prefix . 'a11y_scan_results';
        
        // Delete all unresolved issues from previous scans
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "DELETE FROM `{$table}` WHERE resolved_at IS NULL"
        );

        // Calculate total batches based on selected post types
        $batch_size = $settings['scanner']['batch_size'] ?? 50;

        // Get all public post types except excluded ones
        $all_types   = get_post_types( [ 'public' => true ], 'names' );
        $scan_types  = array_diff( $all_types, $excluded_types, [ 'attachment' ] );
        
        // Count posts across all scannable types
        $total_posts = 0;
        foreach ( $scan_types as $type ) {
            $counts = wp_count_posts( $type );
            $total_posts += $counts->publish ?? 0;
        }

        // Apply max pages limit
        if ( $max_pages > 0 && $total_posts > $max_pages ) {
            $total_posts = $max_pages;
        }

        $total_batches = max( 1, ceil( $total_posts / $batch_size ) );

        // Create scan ID
        $scan_id = wp_generate_uuid4();

        set_transient( 'wpa11y_scan_' . $scan_id, [
            'type'           => $scan_type,
            'total_batches'  => $total_batches,
            'current_batch'  => 0,
            'started_at'     => current_time( 'mysql' ),
            'excluded_types' => $excluded_types,
            'max_pages'      => $max_pages,
            'scan_types'     => array_values( $scan_types ),
        ], HOUR_IN_SECONDS );

        return new WP_REST_Response( [
            'scan_id'       => $scan_id,
            'total_batches' => $total_batches,
            'total_posts'   => $total_posts,
            'post_types'    => array_values( $scan_types ),
        ], 200 );
    }

    /**
     * Process a scan batch.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function process_scan_batch( WP_REST_Request $request ): WP_REST_Response {
        $body    = $request->get_json_params();
        $scan_id = $body['scan_id'] ?? '';
        $batch   = $body['batch'] ?? 0;

        $scan_data = get_transient( 'wpa11y_scan_' . $scan_id );

        if ( ! $scan_data ) {
            return new WP_REST_Response(
                [ 'message' => __( 'Scan not found or expired.', 'free-wcag' ) ],
                404
            );
        }

        // Process batch using AJAX handler logic
        $settings   = WPA11Y_Settings::get_settings();
        $batch_size = $settings['scanner']['batch_size'] ?? 50;
        $max_pages  = $scan_data['max_pages'] ?? 0;

        // Use post types from scan data, or default to post and page
        $post_types = ! empty( $scan_data['scan_types'] ) 
            ? $scan_data['scan_types'] 
            : [ 'post', 'page' ];

        $offset = $batch * $batch_size;

        // If max_pages is set, limit the number of posts
        $posts_per_page = $batch_size;
        if ( $max_pages > 0 ) {
            $remaining = $max_pages - $offset;
            if ( $remaining <= 0 ) {
                return new WP_REST_Response( [
                    'batch'        => $batch,
                    'processed'    => 0,
                    'issues_found' => 0,
                    'complete'     => true,
                ], 200 );
            }
            $posts_per_page = min( $batch_size, $remaining );
        }

        $args = [
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'offset'         => $offset,
            'fields'         => 'ids',
        ];

        $posts = get_posts( $args );

        // Process each post (simplified - actual scanning logic in AJAX handler)
        $issues_found = 0;

        // Update scan progress
        $scan_data['current_batch'] = $batch + 1;
        set_transient( 'wpa11y_scan_' . $scan_id, $scan_data, HOUR_IN_SECONDS );

        return new WP_REST_Response( [
            'batch'        => $batch,
            'processed'    => count( $posts ),
            'issues_found' => $issues_found,
        ], 200 );
    }

    /**
     * Get scan results.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_scan_results( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $table = $wpdb->prefix . 'a11y_scan_results';

        // Check if table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table
        ) );

        if ( ! $table_exists ) {
            return new WP_REST_Response( [], 200 );
        }

        $page     = absint( $request->get_param( 'page' ) ) ?: 1;
        $per_page = absint( $request->get_param( 'per_page' ) ) ?: 50;
        $severity = sanitize_text_field( $request->get_param( 'severity' ) ) ?: '';
        $offset   = ( $page - 1 ) * $per_page;

        $where = '1=1';
        if ( $severity && in_array( $severity, [ 'error', 'warning', 'notice' ], true ) ) {
            $where .= $wpdb->prepare( ' AND severity = %s', $severity );
        }

        $results = $wpdb->get_results(
            "SELECT r.*, p.post_title 
            FROM $table r
            LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID
            WHERE $where
            ORDER BY r.severity DESC, r.scanned_at DESC
            LIMIT $per_page OFFSET $offset",
            ARRAY_A
        );

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where" );

        // Decode issue_data JSON
        foreach ( $results as &$result ) {
            $result['issue_data'] = json_decode( $result['issue_data'], true );
        }

        return new WP_REST_Response( [
            'results'    => $results,
            'total'      => (int) $total,
            'page'       => $page,
            'per_page'   => $per_page,
            'total_pages' => ceil( $total / $per_page ),
        ], 200 );
    }

    /**
     * Resolve an issue.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function resolve_issue( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $issue_id = absint( $request->get_param( 'issue_id' ) );
        $table    = $wpdb->prefix . 'a11y_scan_results';

        $updated = $wpdb->update(
            $table,
            [ 'resolved_at' => current_time( 'mysql' ) ],
            [ 'id' => $issue_id ],
            [ '%s' ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            return new WP_REST_Response(
                [ 'message' => __( 'Failed to resolve issue.', 'free-wcag' ) ],
                500
            );
        }

        return new WP_REST_Response( [
            'message'  => __( 'Issue marked as resolved.', 'free-wcag' ),
            'issue_id' => $issue_id,
        ], 200 );
    }

    /**
     * Get report summary.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_report_summary( WP_REST_Request $request ): WP_REST_Response {
        $settings   = WPA11Y_Settings::get_settings();
        $scan_data  = WPA11Y_Dashboard::get_scan_summary();
        $quick_stats = WPA11Y_Dashboard::get_quick_stats();

        $score = WPA11Y_Dashboard::get_compliance_score( $settings );
        $level = WPA11Y_Dashboard::get_compliance_level( $score );

        return new WP_REST_Response( [
            'compliance' => [
                'score' => $score,
                'level' => $level,
            ],
            'scan'       => $scan_data,
            'stats'      => $quick_stats,
            'settings'   => [
                'modules_enabled' => array_filter( [
                    'visual'      => $settings['module_visual']['enabled'] ?? false,
                    'navigation'  => $settings['module_navigation']['enabled'] ?? false,
                    'content'     => $settings['module_content']['enabled'] ?? false,
                    'aria'        => $settings['module_aria']['enabled'] ?? false,
                    'interaction' => $settings['module_interaction']['enabled'] ?? false,
                ] ),
            ],
        ], 200 );
    }

    /**
     * Export report.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function export_report( WP_REST_Request $request ) {
        $format = $request->get_param( 'format' );

        switch ( $format ) {
            case 'json':
                return $this->export_json();
            case 'csv':
                return $this->export_csv();
            case 'pdf':
                return new WP_REST_Response(
                    [ 'message' => __( 'PDF export not yet implemented.', 'free-wcag' ) ],
                    501
                );
            default:
                return new WP_REST_Response(
                    [ 'message' => __( 'Invalid export format.', 'free-wcag' ) ],
                    400
                );
        }
    }

    /**
     * Export as JSON.
     *
     * @return WP_REST_Response
     */
    private function export_json(): WP_REST_Response {
        global $wpdb;

        $table   = $wpdb->prefix . 'a11y_scan_results';
        $results = $wpdb->get_results( "SELECT * FROM $table ORDER BY scanned_at DESC", ARRAY_A );

        foreach ( $results as &$result ) {
            $result['issue_data'] = json_decode( $result['issue_data'], true );
        }

        $settings = WPA11Y_Settings::get_settings();

        return new WP_REST_Response( [
            'generated_at' => current_time( 'c' ),
            'site_url'     => home_url(),
            'plugin_version' => WPA11Y_VERSION,
            'settings'     => $settings,
            'issues'       => $results,
        ], 200 );
    }

    /**
     * Export as CSV.
     *
     * @return WP_REST_Response
     */
    private function export_csv(): WP_REST_Response {
        global $wpdb;

        $table   = $wpdb->prefix . 'a11y_scan_results';
        $results = $wpdb->get_results(
            "SELECT r.*, p.post_title 
            FROM $table r
            LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID
            ORDER BY r.scanned_at DESC",
            ARRAY_A
        );

        $csv = "ID,Post ID,Post Title,Scan Type,Issue Code,Severity,Scanned At,Resolved At\n";

        foreach ( $results as $row ) {
            $csv .= sprintf(
                "%d,%d,\"%s\",%s,%s,%s,%s,%s\n",
                $row['id'],
                $row['post_id'],
                str_replace( '"', '""', $row['post_title'] ?? '' ),
                $row['scan_type'],
                $row['issue_code'],
                $row['severity'],
                $row['scanned_at'],
                $row['resolved_at'] ?? ''
            );
        }

        return new WP_REST_Response( [
            'content_type' => 'text/csv',
            'filename'     => 'accessibility-report-' . gmdate( 'Y-m-d' ) . '.csv',
            'data'         => $csv,
        ], 200 );
    }
}

