<?php
/**
 * Help page template.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wpa11y-admin wrap">

    <header class="wpa11y-admin-header">
        <h1>
            <span class="dashicons dashicons-sos"></span>
            <?php esc_html_e( 'Help & Documentation', 'free-wcag' ); ?>
        </h1>
    </header>

    <div class="wpa11y-help-grid">
        
        <!-- Getting Started -->
        <div class="wpa11y-help-card">
            <h2>
                <span class="dashicons dashicons-welcome-learn-more"></span>
                <?php esc_html_e( 'Getting Started', 'free-wcag' ); ?>
            </h2>
            <ol>
                <li><?php esc_html_e( 'Enable the modules you need from the Dashboard.', 'free-wcag' ); ?></li>
                <li><?php esc_html_e( 'Turn on the Frontend Toolbar to give visitors accessibility options.', 'free-wcag' ); ?></li>
                <li><?php esc_html_e( 'Run a site scan to identify existing accessibility issues.', 'free-wcag' ); ?></li>
                <li><?php esc_html_e( 'Review and fix any issues found by the scanner.', 'free-wcag' ); ?></li>
            </ol>
        </div>

        <!-- WCAG Overview -->
        <div class="wpa11y-help-card">
            <h2>
                <span class="dashicons dashicons-clipboard"></span>
                <?php esc_html_e( 'WCAG 2.2 Overview', 'free-wcag' ); ?>
            </h2>
            <p><?php esc_html_e( 'WCAG (Web Content Accessibility Guidelines) 2.2 is the latest standard for web accessibility. Level AA is typically required for legal compliance.', 'free-wcag' ); ?></p>
            <h3><?php esc_html_e( 'Four Principles', 'free-wcag' ); ?></h3>
            <ul>
                <li><strong><?php esc_html_e( 'Perceivable', 'free-wcag' ); ?></strong> - <?php esc_html_e( 'Information must be presentable to users.', 'free-wcag' ); ?></li>
                <li><strong><?php esc_html_e( 'Operable', 'free-wcag' ); ?></strong> - <?php esc_html_e( 'Interface must be usable by all.', 'free-wcag' ); ?></li>
                <li><strong><?php esc_html_e( 'Understandable', 'free-wcag' ); ?></strong> - <?php esc_html_e( 'Information and operation must be understandable.', 'free-wcag' ); ?></li>
                <li><strong><?php esc_html_e( 'Robust', 'free-wcag' ); ?></strong> - <?php esc_html_e( 'Content must work with assistive technologies.', 'free-wcag' ); ?></li>
            </ul>
        </div>

        <!-- Module Guide -->
        <div class="wpa11y-help-card wpa11y-help-wide">
            <h2>
                <span class="dashicons dashicons-admin-plugins"></span>
                <?php esc_html_e( 'Module Guide', 'free-wcag' ); ?>
            </h2>
            
            <div class="wpa11y-module-guide">
                <div class="wpa11y-guide-item">
                    <h3><?php esc_html_e( 'Visual Adjustments', 'free-wcag' ); ?></h3>
                    <p><?php esc_html_e( 'Provides user-controlled display options including high contrast modes, text resizing, readable fonts, and color adjustments. These help users with visual impairments customize their experience.', 'free-wcag' ); ?></p>
                </div>
                
                <div class="wpa11y-guide-item">
                    <h3><?php esc_html_e( 'Navigation & Focus', 'free-wcag' ); ?></h3>
                    <p><?php esc_html_e( 'Ensures keyboard users can navigate your site effectively. Includes skip links, focus indicators, and prevents focus from being hidden behind sticky headers.', 'free-wcag' ); ?></p>
                </div>
                
                <div class="wpa11y-guide-item">
                    <h3><?php esc_html_e( 'Content & Reading', 'free-wcag' ); ?></h3>
                    <p><?php esc_html_e( 'Provides reading aids and content presentation options. Users can pause animations, hide images, and highlight links or headings for easier reading.', 'free-wcag' ); ?></p>
                </div>
                
                <div class="wpa11y-guide-item">
                    <h3><?php esc_html_e( 'ARIA & Semantics', 'free-wcag' ); ?></h3>
                    <p><?php esc_html_e( 'Automatically adds ARIA attributes to improve screen reader compatibility. Use with caution - only enable if your theme lacks proper semantic HTML.', 'free-wcag' ); ?></p>
                </div>
                
                <div class="wpa11y-guide-item">
                    <h3><?php esc_html_e( 'Interaction (WCAG 2.2)', 'free-wcag' ); ?></h3>
                    <p><?php esc_html_e( 'New in WCAG 2.2! Ensures interactive elements meet minimum size requirements (24×24px) and provides button alternatives for drag-and-drop operations.', 'free-wcag' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Testing Checklist -->
        <div class="wpa11y-help-card">
            <h2>
                <span class="dashicons dashicons-yes-alt"></span>
                <?php esc_html_e( 'Testing Checklist', 'free-wcag' ); ?>
            </h2>
            <ul class="wpa11y-checklist">
                <li>
                    <strong><?php esc_html_e( 'Keyboard Navigation', 'free-wcag' ); ?></strong>
                    <p><?php esc_html_e( 'Can you Tab through all interactive elements? Is focus visible?', 'free-wcag' ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Screen Reader', 'free-wcag' ); ?></strong>
                    <p><?php esc_html_e( 'Test with NVDA, VoiceOver, or JAWS. Are all elements announced correctly?', 'free-wcag' ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Zoom & Reflow', 'free-wcag' ); ?></strong>
                    <p><?php esc_html_e( 'Zoom to 200%. Does content remain usable without horizontal scrolling?', 'free-wcag' ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Color Contrast', 'free-wcag' ); ?></strong>
                    <p><?php esc_html_e( 'Use browser developer tools to check contrast ratios (4.5:1 minimum).', 'free-wcag' ); ?></p>
                </li>
            </ul>
        </div>

        <!-- Resources -->
        <div class="wpa11y-help-card">
            <h2>
                <span class="dashicons dashicons-admin-links"></span>
                <?php esc_html_e( 'Helpful Resources', 'free-wcag' ); ?>
            </h2>
            <ul>
                <li>
                    <a href="https://www.w3.org/WAI/WCAG22/quickref/" target="_blank" rel="noopener">
                        <?php esc_html_e( 'WCAG 2.2 Quick Reference', 'free-wcag' ); ?>
                    </a>
                </li>
                <li>
                    <a href="https://wave.webaim.org/" target="_blank" rel="noopener">
                        <?php esc_html_e( 'WAVE Accessibility Checker', 'free-wcag' ); ?>
                    </a>
                </li>
                <li>
                    <a href="https://www.deque.com/axe/" target="_blank" rel="noopener">
                        <?php esc_html_e( 'axe DevTools', 'free-wcag' ); ?>
                    </a>
                </li>
                <li>
                    <a href="https://www.nvaccess.org/" target="_blank" rel="noopener">
                        <?php esc_html_e( 'NVDA Screen Reader (Free)', 'free-wcag' ); ?>
                    </a>
                </li>
                <li>
                    <a href="https://webaim.org/resources/contrastchecker/" target="_blank" rel="noopener">
                        <?php esc_html_e( 'WebAIM Contrast Checker', 'free-wcag' ); ?>
                    </a>
                </li>
            </ul>
        </div>

    </div>

</div>

