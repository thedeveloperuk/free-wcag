<?php
/**
 * Reports page template.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables
$wpa11y_settings     = WPA11Y_Settings::get_settings();
$wpa11y_score        = WPA11Y_Dashboard::get_compliance_score( $wpa11y_settings );
$wpa11y_level        = WPA11Y_Dashboard::get_compliance_level( $wpa11y_score );
$wpa11y_scan_summary = WPA11Y_Dashboard::get_scan_summary();
// phpcs:enable
?>

<div class="wpa11y-admin wrap">

    <header class="wpa11y-admin-header">
        <h1>
            <span class="dashicons dashicons-analytics"></span>
            <?php esc_html_e( 'Accessibility Reports', 'free-wcag' ); ?>
        </h1>
    </header>

    <!-- Compliance Overview -->
    <div class="wpa11y-report-section">
        <h2><?php esc_html_e( 'Compliance Overview', 'free-wcag' ); ?></h2>
        
        <div class="wpa11y-compliance-overview">
            <div class="wpa11y-compliance-meter">
                <div class="wpa11y-meter-circle wpa11y-compliance-<?php echo esc_attr( $wpa11y_level ); ?>">
                    <span class="wpa11y-meter-value"><?php echo esc_html( $wpa11y_score ); ?>%</span>
                </div>
                <div class="wpa11y-meter-label">
                    <strong><?php esc_html_e( 'WCAG 2.2 Level AA', 'free-wcag' ); ?></strong>
                    <p>
                        <?php
                        if ( $wpa11y_level === 'high' ) {
                            esc_html_e( 'Excellent! Your site has comprehensive accessibility coverage.', 'free-wcag' );
                        } elseif ( $wpa11y_level === 'medium' ) {
                            esc_html_e( 'Good progress. Enable more features to improve compliance.', 'free-wcag' );
                        } else {
                            esc_html_e( 'Consider enabling more accessibility modules.', 'free-wcag' );
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Status -->
    <div class="wpa11y-report-section">
        <h2><?php esc_html_e( 'Module Status', 'free-wcag' ); ?></h2>
        
        <table class="wpa11y-status-table widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Module', 'free-wcag' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'free-wcag' ); ?></th>
                    <th><?php esc_html_e( 'Features Enabled', 'free-wcag' ); ?></th>
                    <th><?php esc_html_e( 'WCAG Coverage', 'free-wcag' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in loop
                $wpa11y_modules = WPA11Y_Dashboard::get_modules_display();
                foreach ( $wpa11y_modules as $wpa11y_module_id => $wpa11y_module ) :
                    $wpa11y_module_settings = $wpa11y_settings[ $wpa11y_module_id ] ?? [];
                    $wpa11y_is_enabled      = ! empty( $wpa11y_module_settings['enabled'] );
                    $wpa11y_features        = $wpa11y_module_settings['features'] ?? [];
                    $wpa11y_enabled_count   = count( array_filter( $wpa11y_features ) );
                    $wpa11y_total_count     = count( $wpa11y_features );
                // phpcs:enable
                ?>
                <tr>
                    <td>
                        <span class="dashicons dashicons-<?php echo esc_attr( $wpa11y_module['icon'] ); ?>"></span>
                        <?php echo esc_html( $wpa11y_module['title'] ); ?>
                    </td>
                    <td>
                        <?php if ( $wpa11y_is_enabled ) : ?>
                        <span class="wpa11y-status-enabled"><?php esc_html_e( 'Enabled', 'free-wcag' ); ?></span>
                        <?php else : ?>
                        <span class="wpa11y-status-disabled"><?php esc_html_e( 'Disabled', 'free-wcag' ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        printf( 
                            /* translators: 1: Enabled features count, 2: Total features count */
                            esc_html__( '%1$d of %2$d', 'free-wcag' ), 
                            (int) $wpa11y_enabled_count, 
                            (int) $wpa11y_total_count 
                        ); 
                        ?>
                    </td>
                    <td>
                        <span class="wpa11y-wcag-badge"><?php echo esc_html( $wpa11y_module['wcag'] ); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Scan History -->
    <div class="wpa11y-report-section">
        <h2><?php esc_html_e( 'Recent Scan Results', 'free-wcag' ); ?></h2>
        
        <?php if ( $wpa11y_scan_summary['last_scan'] ) : ?>
        <div class="wpa11y-scan-results-summary">
            <p>
                <?php 
                printf( 
                    /* translators: %s: Date and time of last scan (e.g. "January 1, 2024 3:00pm") */
                    esc_html__( 'Last scanned: %s', 'free-wcag' ),
                    esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $wpa11y_scan_summary['last_scan'] ) ) )
                ); 
                ?>
            </p>
            
            <div class="wpa11y-issue-breakdown">
                <div class="wpa11y-issue-stat">
                    <span class="wpa11y-issue-count wpa11y-error"><?php echo esc_html( $wpa11y_scan_summary['errors'] ); ?></span>
                    <span class="wpa11y-issue-label"><?php esc_html_e( 'Errors', 'free-wcag' ); ?></span>
                </div>
                <div class="wpa11y-issue-stat">
                    <span class="wpa11y-issue-count wpa11y-warning"><?php echo esc_html( $wpa11y_scan_summary['warnings'] ); ?></span>
                    <span class="wpa11y-issue-label"><?php esc_html_e( 'Warnings', 'free-wcag' ); ?></span>
                </div>
                <div class="wpa11y-issue-stat">
                    <span class="wpa11y-issue-count wpa11y-notice"><?php echo esc_html( $wpa11y_scan_summary['notices'] ); ?></span>
                    <span class="wpa11y-issue-label"><?php esc_html_e( 'Notices', 'free-wcag' ); ?></span>
                </div>
            </div>
        </div>
        <?php else : ?>
        <p class="wpa11y-no-scans"><?php esc_html_e( 'No scans have been performed yet. Run a scan from the Scanner page.', 'free-wcag' ); ?></p>
        <?php endif; ?>
    </div>

    <!-- Export Options -->
    <div class="wpa11y-report-section">
        <h2><?php esc_html_e( 'Export Report', 'free-wcag' ); ?></h2>
        
        <div class="wpa11y-export-options">
            <a href="<?php echo esc_url( rest_url( 'wpa11y/v1/reports/export/json' ) ); ?>" 
               class="button button-secondary"
               download="accessibility-report.json">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export JSON', 'free-wcag' ); ?>
            </a>
            
            <a href="<?php echo esc_url( rest_url( 'wpa11y/v1/reports/export/csv' ) ); ?>" 
               class="button button-secondary"
               download="accessibility-report.csv">
                <span class="dashicons dashicons-media-spreadsheet"></span>
                <?php esc_html_e( 'Export CSV', 'free-wcag' ); ?>
            </a>
            
            <button type="button" class="button button-secondary" disabled title="<?php esc_attr_e( 'Coming soon', 'free-wcag' ); ?>">
                <span class="dashicons dashicons-pdf"></span>
                <?php esc_html_e( 'Export PDF', 'free-wcag' ); ?>
            </button>
        </div>
    </div>

</div>

