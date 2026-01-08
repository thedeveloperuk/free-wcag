<?php
/**
 * Admin dashboard template.
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
$wpa11y_modules      = WPA11Y_Dashboard::get_modules_display();
$wpa11y_score        = WPA11Y_Dashboard::get_compliance_score( $wpa11y_settings );
$wpa11y_level        = WPA11Y_Dashboard::get_compliance_level( $wpa11y_score );
$wpa11y_scan_summary = WPA11Y_Dashboard::get_scan_summary();
$wpa11y_quick_stats  = WPA11Y_Dashboard::get_quick_stats();
// phpcs:enable
?>

<div id="wpa11y-admin" class="wpa11y-admin wrap" x-data="wpa11yAdmin()">

    <!-- Header -->
    <header class="wpa11y-admin-header">
        <div class="wpa11y-header-content">
            <h1>
                <span class="dashicons dashicons-universal-access-alt"></span>
                <?php esc_html_e( 'Free WCAG Accessibility Suite', 'free-wcag' ); ?>
            </h1>
            <p class="wpa11y-version">v<?php echo esc_html( WPA11Y_VERSION ); ?></p>
        </div>
        
        <div class="wpa11y-compliance-badge wpa11y-compliance-<?php echo esc_attr( $wpa11y_level ); ?>">
            <span class="wpa11y-score"><?php echo esc_html( $wpa11y_score ); ?>%</span>
            <span class="wpa11y-label"><?php esc_html_e( 'WCAG 2.2 AA', 'free-wcag' ); ?></span>
        </div>
    </header>

    <!-- Quick Stats -->
    <div class="wpa11y-stats-grid">
        <div class="wpa11y-stat-card">
            <span class="dashicons dashicons-admin-page"></span>
            <div class="wpa11y-stat-content">
                <span class="wpa11y-stat-value"><?php echo esc_html( $wpa11y_quick_stats['total_content'] ); ?></span>
                <span class="wpa11y-stat-label"><?php esc_html_e( 'Pages & Posts', 'free-wcag' ); ?></span>
            </div>
        </div>
        
        <div class="wpa11y-stat-card <?php echo $wpa11y_quick_stats['images_without_alt'] > 0 ? 'wpa11y-stat-warning' : ''; ?>">
            <span class="dashicons dashicons-format-image"></span>
            <div class="wpa11y-stat-content">
                <span class="wpa11y-stat-value"><?php echo esc_html( $wpa11y_quick_stats['images_without_alt'] ); ?></span>
                <span class="wpa11y-stat-label"><?php esc_html_e( 'Images Missing Alt', 'free-wcag' ); ?></span>
            </div>
        </div>
        
        <div class="wpa11y-stat-card">
            <span class="dashicons dashicons-search"></span>
            <div class="wpa11y-stat-content">
                <span class="wpa11y-stat-value">
                    <?php 
                    if ( $wpa11y_scan_summary['last_scan'] ) {
                        echo esc_html( human_time_diff( strtotime( $wpa11y_scan_summary['last_scan'] ) ) );
                    } else {
                        esc_html_e( 'Never', 'free-wcag' );
                    }
                    ?>
                </span>
                <span class="wpa11y-stat-label"><?php esc_html_e( 'Last Scan', 'free-wcag' ); ?></span>
            </div>
        </div>
        
        <div class="wpa11y-stat-card <?php echo $wpa11y_scan_summary['errors'] > 0 ? 'wpa11y-stat-error' : ''; ?>">
            <span class="dashicons dashicons-warning"></span>
            <div class="wpa11y-stat-content">
                <span class="wpa11y-stat-value"><?php echo esc_html( $wpa11y_scan_summary['total_issues'] ); ?></span>
                <span class="wpa11y-stat-label"><?php esc_html_e( 'Issues Found', 'free-wcag' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <nav class="wpa11y-admin-nav" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'free-wcag' ); ?>">
        <button role="tab" 
                :aria-selected="activeTab === 'modules'"
                :class="{ 'is-active': activeTab === 'modules' }"
                @click="activeTab = 'modules'">
            <span class="dashicons dashicons-admin-plugins"></span>
            <?php esc_html_e( 'Modules', 'free-wcag' ); ?>
        </button>
        <button role="tab" 
                :aria-selected="activeTab === 'toolbar'"
                :class="{ 'is-active': activeTab === 'toolbar' }"
                @click="activeTab = 'toolbar'">
            <span class="dashicons dashicons-visibility"></span>
            <?php esc_html_e( 'Toolbar', 'free-wcag' ); ?>
        </button>
        <button role="tab" 
                :aria-selected="activeTab === 'advanced'"
                :class="{ 'is-active': activeTab === 'advanced' }"
                @click="activeTab = 'advanced'">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php esc_html_e( 'Advanced', 'free-wcag' ); ?>
        </button>
    </nav>

    <!-- Modules Tab -->
    <div x-show="activeTab === 'modules'" class="wpa11y-tab-content">
        <div class="wpa11y-modules-grid">
            <?php foreach ( $wpa11y_modules as $wpa11y_module_id => $wpa11y_module ) : ?>
            <article class="wpa11y-module-card" 
                     :class="{ 'is-disabled': !settings.<?php echo esc_attr( $wpa11y_module_id ); ?>?.enabled }">
                
                <header class="wpa11y-module-header">
                    <div class="wpa11y-module-icon">
                        <span class="dashicons dashicons-<?php echo esc_attr( $wpa11y_module['icon'] ); ?>"></span>
                    </div>
                    
                    <div class="wpa11y-module-info">
                        <h2>
                            <?php echo esc_html( $wpa11y_module['title'] ); ?>
                            <?php if ( ! empty( $wpa11y_module['badge'] ) ) : ?>
                            <span class="wpa11y-badge wpa11y-badge-<?php echo esc_attr( $wpa11y_module['badge'] ); ?>">
                                <?php echo esc_html( ucfirst( $wpa11y_module['badge'] ) ); ?>
                            </span>
                            <?php endif; ?>
                        </h2>
                        <p><?php echo esc_html( $wpa11y_module['description'] ); ?></p>
                    </div>
                    
                    <label class="wpa11y-master-switch">
                        <span class="screen-reader-text">
                            <?php 
                            /* translators: %s: Module name */
                            printf( esc_html__( 'Enable %s module', 'free-wcag' ), esc_html( $wpa11y_module['title'] ) ); 
                            ?>
                        </span>
                        <input type="checkbox" 
                               x-model="settings.<?php echo esc_attr( $wpa11y_module_id ); ?>.enabled"
                               @change="saveSettings()">
                        <span class="wpa11y-switch-slider"></span>
                    </label>
                </header>
                
                <div class="wpa11y-module-features" 
                     :class="{ 'is-disabled': !settings.<?php echo esc_attr( $wpa11y_module_id ); ?>?.enabled }">
                    <?php foreach ( $wpa11y_module['features'] as $wpa11y_feature_id => $wpa11y_feature_label ) : ?>
                    <label class="wpa11y-feature-toggle">
                        <input type="checkbox" 
                               x-model="settings.<?php echo esc_attr( $wpa11y_module_id ); ?>.features.<?php echo esc_attr( $wpa11y_feature_id ); ?>"
                               :disabled="!settings.<?php echo esc_attr( $wpa11y_module_id ); ?>?.enabled"
                               @change="saveSettings()">
                        <span class="wpa11y-switch-slider wpa11y-switch-small"></span>
                        <span class="wpa11y-feature-label"><?php echo esc_html( $wpa11y_feature_label ); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                
                <footer class="wpa11y-module-footer">
                    <span class="wpa11y-wcag-badge" title="<?php esc_attr_e( 'WCAG criteria addressed', 'free-wcag' ); ?>">
                        <?php echo esc_html( $wpa11y_module['wcag'] ); ?>
                    </span>
                </footer>
            </article>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Toolbar Tab -->
    <div x-show="activeTab === 'toolbar'" class="wpa11y-tab-content">
        <div class="wpa11y-settings-card">
            <h2><?php esc_html_e( 'Frontend Toolbar Settings', 'free-wcag' ); ?></h2>
            
            <div class="wpa11y-setting-row">
                <label class="wpa11y-feature-toggle">
                    <input type="checkbox" 
                           x-model="settings.global.toolbar_enabled"
                           @change="saveSettings()">
                    <span class="wpa11y-switch-slider"></span>
                    <span class="wpa11y-feature-label"><?php esc_html_e( 'Enable Frontend Toolbar', 'free-wcag' ); ?></span>
                </label>
                <p class="description"><?php esc_html_e( 'Show the accessibility toolbar to all site visitors.', 'free-wcag' ); ?></p>
            </div>
            
            <div class="wpa11y-setting-row" :class="{ 'is-disabled': !settings.global.toolbar_enabled }">
                <label for="wpa11y-toolbar-position"><?php esc_html_e( 'Toolbar Position', 'free-wcag' ); ?></label>
                <select id="wpa11y-toolbar-position" 
                        x-model="settings.global.toolbar_position"
                        :disabled="!settings.global.toolbar_enabled"
                        @change="saveSettings()">
                    <option value="left"><?php esc_html_e( 'Left', 'free-wcag' ); ?></option>
                    <option value="right"><?php esc_html_e( 'Right', 'free-wcag' ); ?></option>
                    <option value="bottom"><?php esc_html_e( 'Bottom', 'free-wcag' ); ?></option>
                </select>
            </div>
            
            <div class="wpa11y-setting-row" :class="{ 'is-disabled': !settings.global.toolbar_enabled }">
                <label for="wpa11y-toolbar-theme"><?php esc_html_e( 'Toolbar Theme', 'free-wcag' ); ?></label>
                <select id="wpa11y-toolbar-theme" 
                        x-model="settings.global.toolbar_theme"
                        :disabled="!settings.global.toolbar_enabled"
                        @change="saveSettings()">
                    <option value="auto"><?php esc_html_e( 'Auto (match system)', 'free-wcag' ); ?></option>
                    <option value="light"><?php esc_html_e( 'Light', 'free-wcag' ); ?></option>
                    <option value="dark"><?php esc_html_e( 'Dark', 'free-wcag' ); ?></option>
                </select>
            </div>
            
            <div class="wpa11y-setting-row">
                <label class="wpa11y-feature-toggle">
                    <input type="checkbox" 
                           x-model="settings.global.respect_prefers"
                           @change="saveSettings()">
                    <span class="wpa11y-switch-slider"></span>
                    <span class="wpa11y-feature-label"><?php esc_html_e( 'Respect User System Preferences', 'free-wcag' ); ?></span>
                </label>
                <p class="description"><?php esc_html_e( 'Honor prefers-reduced-motion, prefers-color-scheme, and other OS accessibility settings.', 'free-wcag' ); ?></p>
            </div>
        </div>
        
        <!-- Toolbar Preview -->
        <div class="wpa11y-toolbar-preview" x-show="settings.global.toolbar_enabled">
            <h3><?php esc_html_e( 'Toolbar Preview', 'free-wcag' ); ?></h3>
            <div class="wpa11y-preview-frame">
                <p class="description"><?php esc_html_e( 'Preview shows approximately how the toolbar will appear on your site.', 'free-wcag' ); ?></p>
                <!-- Preview iframe or mockup would go here -->
            </div>
        </div>
    </div>

    <!-- Advanced Tab -->
    <div x-show="activeTab === 'advanced'" class="wpa11y-tab-content">
        <div class="wpa11y-settings-card">
            <h2><?php esc_html_e( 'Advanced Settings', 'free-wcag' ); ?></h2>
            
            <div class="wpa11y-setting-row">
                <label class="wpa11y-feature-toggle">
                    <input type="checkbox" 
                           x-model="settings.global.safe_mode"
                           @change="saveSettings()">
                    <span class="wpa11y-switch-slider"></span>
                    <span class="wpa11y-feature-label"><?php esc_html_e( 'Safe Mode (Admin Only)', 'free-wcag' ); ?></span>
                </label>
                <p class="description"><?php esc_html_e( 'Only show accessibility features to logged-in administrators. Useful for testing before public deployment.', 'free-wcag' ); ?></p>
            </div>
            
            <h3><?php esc_html_e( 'Scanner Settings', 'free-wcag' ); ?></h3>
            
            <div class="wpa11y-setting-row">
                <label for="wpa11y-batch-size"><?php esc_html_e( 'Scan Batch Size', 'free-wcag' ); ?></label>
                <input type="number" 
                       id="wpa11y-batch-size" 
                       x-model.number="settings.scanner.batch_size"
                       min="10" 
                       max="100"
                       @change="saveSettings()">
                <p class="description"><?php esc_html_e( 'Number of posts to scan per batch. Lower values prevent server timeouts.', 'free-wcag' ); ?></p>
            </div>
            
            <div class="wpa11y-setting-row">
                <label class="wpa11y-feature-toggle">
                    <input type="checkbox" 
                           x-model="settings.scanner.scan_on_publish"
                           @change="saveSettings()">
                    <span class="wpa11y-switch-slider"></span>
                    <span class="wpa11y-feature-label"><?php esc_html_e( 'Auto-scan on Publish', 'free-wcag' ); ?></span>
                </label>
                <p class="description"><?php esc_html_e( 'Automatically scan new posts and pages when published.', 'free-wcag' ); ?></p>
            </div>
        </div>
        
        <div class="wpa11y-settings-card wpa11y-danger-zone">
            <h2><?php esc_html_e( 'Danger Zone', 'free-wcag' ); ?></h2>
            
            <div class="wpa11y-setting-row">
                <button type="button" 
                        class="button button-secondary"
                        @click="resetSettings()">
                    <?php esc_html_e( 'Reset All Settings to Defaults', 'free-wcag' ); ?>
                </button>
                <p class="description"><?php esc_html_e( 'This will reset all plugin settings to their default values. This action cannot be undone.', 'free-wcag' ); ?></p>
            </div>
        </div>
    </div>

    <!-- Footer Actions -->
    <footer class="wpa11y-admin-footer">
        <div class="wpa11y-footer-left">
            <p>
                <?php 
                printf( 
                    wp_kses( 
                        /* translators: %s: Plugin documentation URL */
                        __( 'Need help? Check the <a href="%s" target="_blank">documentation</a>.', 'free-wcag' ),
                        [ 'a' => [ 'href' => [], 'target' => [] ] ]
                    ),
                    esc_url( admin_url( 'admin.php?page=free-wcag-help' ) )
                ); 
                ?>
            </p>
        </div>
        
        <div class="wpa11y-footer-right">
            <span class="wpa11y-save-status" x-show="saving">
                <span class="spinner is-active"></span>
                <?php esc_html_e( 'Saving...', 'free-wcag' ); ?>
            </span>
            <span class="wpa11y-save-status wpa11y-saved" x-show="saved" x-transition>
                <span class="dashicons dashicons-yes"></span>
                <?php esc_html_e( 'Saved', 'free-wcag' ); ?>
            </span>
        </div>
    </footer>

</div>

