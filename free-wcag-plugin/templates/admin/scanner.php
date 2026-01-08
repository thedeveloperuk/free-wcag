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

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables
$wpa11y_scan_summary   = WPA11Y_Dashboard::get_scan_summary();
$wpa11y_settings       = WPA11Y_Settings::get_settings();
$wpa11y_post_types     = WPA11Y_Settings::get_scannable_post_types();
$wpa11y_excluded_types = $wpa11y_settings['scanner']['excluded_types'] ?? [];
$wpa11y_max_pages      = $wpa11y_settings['scanner']['max_pages'] ?? 0;
// phpcs:enable
?>

<div class="wpa11y-admin wrap" x-data="wpa11yScanner()">

    <header class="wpa11y-admin-header">
        <h1>
            <span class="dashicons dashicons-search"></span>
            <?php esc_html_e( 'Accessibility Scanner', 'free-wcag' ); ?>
        </h1>
    </header>

    <!-- Scanner Settings -->
    <div class="wpa11y-scanner-settings">
        <div class="wpa11y-settings-row">
            <div class="wpa11y-setting-group">
                <h3><?php esc_html_e( 'Content to Scan', 'free-wcag' ); ?></h3>
                <p class="description"><?php esc_html_e( 'Select which post types to include in the scan.', 'free-wcag' ); ?></p>
                <div class="wpa11y-post-types-grid">
                    <?php foreach ( $wpa11y_post_types as $wpa11y_pt ) : ?>
                    <label class="wpa11y-post-type-checkbox">
                        <input type="checkbox" 
                               :checked="!scannerSettings.excludedTypes.includes('<?php echo esc_attr( $wpa11y_pt['name'] ); ?>')"
                               @change="togglePostType('<?php echo esc_attr( $wpa11y_pt['name'] ); ?>')">
                        <span class="wpa11y-post-type-label">
                            <?php echo esc_html( $wpa11y_pt['label'] ); ?>
                            <span class="wpa11y-post-type-count">(<?php echo esc_html( $wpa11y_pt['count'] ); ?>)</span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="wpa11y-setting-group">
                <h3><?php esc_html_e( 'Scan Limit', 'free-wcag' ); ?></h3>
                <p class="description"><?php esc_html_e( 'Maximum number of pages to scan. Useful for large sites.', 'free-wcag' ); ?></p>
                <select x-model="scannerSettings.maxPages" @change="saveSettings()" class="wpa11y-max-pages-select">
                    <option value="0"><?php esc_html_e( 'Unlimited', 'free-wcag' ); ?></option>
                    <option value="10">10 <?php esc_html_e( 'pages', 'free-wcag' ); ?></option>
                    <option value="50">50 <?php esc_html_e( 'pages', 'free-wcag' ); ?></option>
                    <option value="100">100 <?php esc_html_e( 'pages', 'free-wcag' ); ?></option>
                    <option value="500">500 <?php esc_html_e( 'pages', 'free-wcag' ); ?></option>
                    <option value="1000">1000 <?php esc_html_e( 'pages', 'free-wcag' ); ?></option>
                </select>
            </div>
        </div>
    </div>

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
        
        <?php if ( $wpa11y_scan_summary['last_scan'] ) : ?>
        <p class="wpa11y-last-scan">
            <?php 
            printf( 
                /* translators: %s: Time since last scan (e.g. "5 minutes") */
                esc_html__( 'Last scan: %s ago', 'free-wcag' ),
                esc_html( human_time_diff( strtotime( $wpa11y_scan_summary['last_scan'] ) ) )
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

