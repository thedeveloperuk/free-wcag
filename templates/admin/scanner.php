<?php
/**
 * Scanner page template.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$scan_summary = WPA11Y_Dashboard::get_scan_summary();
?>

<div class="wpa11y-admin wrap" x-data="wpa11yScanner()">

    <header class="wpa11y-admin-header">
        <h1>
            <span class="dashicons dashicons-search"></span>
            <?php esc_html_e( 'Accessibility Scanner', 'free-wcag' ); ?>
        </h1>
    </header>

    <!-- Scan Controls -->
    <div class="wpa11y-scanner-controls">
        <div class="wpa11y-scan-types">
            <button type="button" 
                    class="button button-primary button-hero"
                    :disabled="scanning"
                    @click="startScan('full')">
                <span class="dashicons dashicons-admin-site-alt3"></span>
                <?php esc_html_e( 'Full Site Scan', 'free-wcag' ); ?>
            </button>
            
            <button type="button" 
                    class="button button-secondary"
                    :disabled="scanning"
                    @click="startScan('images')">
                <span class="dashicons dashicons-format-image"></span>
                <?php esc_html_e( 'Images Only', 'free-wcag' ); ?>
            </button>
            
            <button type="button" 
                    class="button button-secondary"
                    :disabled="scanning"
                    @click="startScan('headings')">
                <span class="dashicons dashicons-editor-textcolor"></span>
                <?php esc_html_e( 'Headings Only', 'free-wcag' ); ?>
            </button>
            
            <button type="button" 
                    class="button button-secondary"
                    :disabled="scanning"
                    @click="startScan('links')">
                <span class="dashicons dashicons-admin-links"></span>
                <?php esc_html_e( 'Links Only', 'free-wcag' ); ?>
            </button>
        </div>
        
        <?php if ( $scan_summary['last_scan'] ) : ?>
        <p class="wpa11y-last-scan">
            <?php 
            /* translators: %s: Time since last scan */
            printf( 
                esc_html__( 'Last scan: %s ago', 'free-wcag' ),
                human_time_diff( strtotime( $scan_summary['last_scan'] ) )
            ); 
            ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- Progress Bar -->
    <div class="wpa11y-scan-progress" x-show="scanning" x-transition>
        <div class="wpa11y-progress-bar">
            <div class="wpa11y-progress-fill" :style="{ width: progress + '%' }"></div>
        </div>
        <p class="wpa11y-progress-text" x-text="progressText"></p>
    </div>

    <!-- Results Summary -->
    <div class="wpa11y-scan-summary" x-show="results.length > 0">
        <div class="wpa11y-summary-cards">
            <div class="wpa11y-summary-card wpa11y-summary-error">
                <span class="wpa11y-summary-count" x-text="errorCount"></span>
                <span class="wpa11y-summary-label"><?php esc_html_e( 'Errors', 'free-wcag' ); ?></span>
            </div>
            <div class="wpa11y-summary-card wpa11y-summary-warning">
                <span class="wpa11y-summary-count" x-text="warningCount"></span>
                <span class="wpa11y-summary-label"><?php esc_html_e( 'Warnings', 'free-wcag' ); ?></span>
            </div>
            <div class="wpa11y-summary-card wpa11y-summary-notice">
                <span class="wpa11y-summary-count" x-text="noticeCount"></span>
                <span class="wpa11y-summary-label"><?php esc_html_e( 'Notices', 'free-wcag' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="wpa11y-filter-controls" x-show="results.length > 0">
        <label for="wpa11y-severity-filter"><?php esc_html_e( 'Filter by severity:', 'free-wcag' ); ?></label>
        <select id="wpa11y-severity-filter" x-model="severityFilter">
            <option value=""><?php esc_html_e( 'All', 'free-wcag' ); ?></option>
            <option value="error"><?php esc_html_e( 'Errors only', 'free-wcag' ); ?></option>
            <option value="warning"><?php esc_html_e( 'Warnings only', 'free-wcag' ); ?></option>
            <option value="notice"><?php esc_html_e( 'Notices only', 'free-wcag' ); ?></option>
        </select>
    </div>

    <!-- Results Table -->
    <table class="wpa11y-results-table widefat striped" x-show="filteredResults.length > 0">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Severity', 'free-wcag' ); ?></th>
                <th><?php esc_html_e( 'Page/Post', 'free-wcag' ); ?></th>
                <th><?php esc_html_e( 'Issue', 'free-wcag' ); ?></th>
                <th><?php esc_html_e( 'WCAG', 'free-wcag' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'free-wcag' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="result in filteredResults" :key="result.id">
                <tr :class="'wpa11y-severity-' + result.severity">
                    <td>
                        <span class="wpa11y-severity-badge" :class="'wpa11y-severity-' + result.severity" x-text="result.severity"></span>
                    </td>
                    <td>
                        <a :href="'<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>' + result.post_id" target="_blank" x-text="result.post_title || 'Post #' + result.post_id"></a>
                    </td>
                    <td>
                        <strong x-text="result.issue_data?.message || result.issue_code"></strong>
                        <code class="wpa11y-element-preview" x-text="result.element_selector?.substring(0, 100)" x-show="result.element_selector"></code>
                    </td>
                    <td>
                        <span x-text="result.issue_data?.wcag || '-'"></span>
                    </td>
                    <td>
                        <button type="button" 
                                class="button button-small"
                                @click="resolveIssue(result.id)"
                                :disabled="result.resolved_at">
                            <span x-text="result.resolved_at ? '<?php esc_attr_e( 'Resolved', 'free-wcag' ); ?>' : '<?php esc_attr_e( 'Mark Resolved', 'free-wcag' ); ?>'"></span>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <!-- No Results -->
    <div class="wpa11y-no-results" x-show="!scanning && results.length === 0">
        <span class="dashicons dashicons-yes-alt"></span>
        <p><?php esc_html_e( 'No accessibility issues found! Run a scan to check your content.', 'free-wcag' ); ?></p>
    </div>

</div>

