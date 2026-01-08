<?php
/**
 * Skip links template.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<nav id="wpa11y-skip-links" 
     class="wpa11y-skip-links" 
     aria-label="<?php esc_attr_e( 'Skip links', 'free-wcag' ); ?>">
    
    <?php if ( in_array( 'content', $targets, true ) ) : ?>
    <a href="#main-content" class="wpa11y-skip-link">
        <?php esc_html_e( 'Skip to main content', 'free-wcag' ); ?>
    </a>
    <?php endif; ?>
    
    <?php if ( in_array( 'navigation', $targets, true ) ) : ?>
    <a href="#main-navigation" class="wpa11y-skip-link">
        <?php esc_html_e( 'Skip to navigation', 'free-wcag' ); ?>
    </a>
    <?php endif; ?>
    
    <?php if ( in_array( 'search', $targets, true ) ) : ?>
    <a href="#search" class="wpa11y-skip-link">
        <?php esc_html_e( 'Skip to search', 'free-wcag' ); ?>
    </a>
    <?php endif; ?>
    
    <?php if ( in_array( 'footer', $targets, true ) ) : ?>
    <a href="#site-footer" class="wpa11y-skip-link">
        <?php esc_html_e( 'Skip to footer', 'free-wcag' ); ?>
    </a>
    <?php endif; ?>
    
</nav>

