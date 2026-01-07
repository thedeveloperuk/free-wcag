<?php
/**
 * Frontend toolbar template.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="wpa11y-toolbar" 
     class="wpa11y-toolbar" 
     role="region" 
     aria-label="<?php esc_attr_e( 'Accessibility Options', 'free-wcag' ); ?>"
     data-position="<?php echo esc_attr( $position ); ?>">
  
    <!-- Trigger Button -->
    <button id="wpa11y-trigger"
            class="wpa11y-trigger"
            aria-expanded="false"
            aria-controls="wpa11y-panel"
            aria-label="<?php esc_attr_e( 'Open Accessibility Options', 'free-wcag' ); ?>">
        <?php echo WPA11Y_Toolbar::get_icon(); ?>
    </button>
  
    <!-- Main Panel -->
    <div id="wpa11y-panel" 
         class="wpa11y-panel" 
         role="dialog"
         aria-labelledby="wpa11y-title"
         aria-modal="true"
         hidden>
    
        <header class="wpa11y-header">
            <h2 id="wpa11y-title"><?php esc_html_e( 'Accessibility Options', 'free-wcag' ); ?></h2>
            <button class="wpa11y-close" aria-label="<?php esc_attr_e( 'Close', 'free-wcag' ); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </header>
    
        <div class="wpa11y-content">
            
            <?php if ( ! empty( $features['high_contrast'] ) || ! empty( $features['grayscale'] ) || ! empty( $features['invert_colors'] ) || ! empty( $features['text_resize'] ) ) : ?>
            <!-- Visual Adjustments Section -->
            <section class="wpa11y-section" aria-labelledby="wpa11y-visual-title">
                <h3 id="wpa11y-visual-title"><?php esc_html_e( 'Visual Adjustments', 'free-wcag' ); ?></h3>
                
                <?php if ( ! empty( $features['high_contrast'] ) ) : ?>
                <!-- Contrast Modes -->
                <fieldset class="wpa11y-option-group">
                    <legend><?php esc_html_e( 'Contrast', 'free-wcag' ); ?></legend>
                    <div class="wpa11y-button-group" role="radiogroup" aria-label="<?php esc_attr_e( 'Contrast options', 'free-wcag' ); ?>">
                        <button role="radio" 
                                aria-checked="true" 
                                data-action="contrast" 
                                data-value="default"
                                class="wpa11y-option-btn is-active">
                            <?php esc_html_e( 'Default', 'free-wcag' ); ?>
                        </button>
                        <button role="radio" 
                                aria-checked="false" 
                                data-action="contrast" 
                                data-value="high-dark"
                                class="wpa11y-option-btn">
                            <?php esc_html_e( 'Dark', 'free-wcag' ); ?>
                        </button>
                        <button role="radio" 
                                aria-checked="false" 
                                data-action="contrast" 
                                data-value="high-light"
                                class="wpa11y-option-btn">
                            <?php esc_html_e( 'Light', 'free-wcag' ); ?>
                        </button>
                        <button role="radio" 
                                aria-checked="false" 
                                data-action="contrast" 
                                data-value="yellow-black"
                                class="wpa11y-option-btn">
                            <?php esc_html_e( 'Yellow', 'free-wcag' ); ?>
                        </button>
                    </div>
                </fieldset>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['text_resize'] ) ) : ?>
                <!-- Text Size -->
                <div class="wpa11y-slider-option">
                    <label for="wpa11y-font-size"><?php esc_html_e( 'Text Size', 'free-wcag' ); ?></label>
                    <div class="wpa11y-slider-wrapper">
                        <button type="button" 
                                class="wpa11y-btn-decrease" 
                                aria-label="<?php esc_attr_e( 'Decrease text size', 'free-wcag' ); ?>" 
                                data-action="font-size" 
                                data-delta="-10">−</button>
                        <input type="range" 
                               id="wpa11y-font-size" 
                               min="100" 
                               max="200" 
                               value="100"
                               step="10"
                               data-action="font-size">
                        <button type="button" 
                                class="wpa11y-btn-increase" 
                                aria-label="<?php esc_attr_e( 'Increase text size', 'free-wcag' ); ?>" 
                                data-action="font-size" 
                                data-delta="10">+</button>
                        <output for="wpa11y-font-size" class="wpa11y-output">100%</output>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['grayscale'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="grayscale"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Grayscale', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['invert_colors'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="invert-colors"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Invert Colors', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['low_saturation'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="low-saturation"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Low Saturation', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ( ! empty( $features['dyslexia_font'] ) || ! empty( $features['readable_font'] ) || ! empty( $features['cursor_size'] ) ) : ?>
            <!-- Typography Section -->
            <section class="wpa11y-section" aria-labelledby="wpa11y-typography-title">
                <h3 id="wpa11y-typography-title"><?php esc_html_e( 'Typography', 'free-wcag' ); ?></h3>
                
                <?php if ( ! empty( $features['readable_font'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="readable-font"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Readable Font', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['dyslexia_font'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="dyslexia-font"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Dyslexia-Friendly Font', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['cursor_size'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="large-cursor"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Large Cursor', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ( ! empty( $features['reading_guide'] ) || ! empty( $features['reading_mask'] ) || ! empty( $features['highlight_links'] ) || ! empty( $features['animation_pause'] ) ) : ?>
            <!-- Reading Aids Section -->
            <section class="wpa11y-section" aria-labelledby="wpa11y-reading-title">
                <h3 id="wpa11y-reading-title"><?php esc_html_e( 'Reading Aids', 'free-wcag' ); ?></h3>
                
                <?php if ( ! empty( $features['reading_guide'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="reading-guide"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Reading Guide', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['reading_mask'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="reading-mask"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Reading Mask', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['highlight_links'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="highlight-links"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Highlight Links', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['animation_pause'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="pause-animations"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Pause Animations', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $features['hide_images'] ) ) : ?>
                <div class="wpa11y-toggle-option">
                    <button role="switch" 
                            aria-checked="false" 
                            data-action="hide-images"
                            class="wpa11y-switch-btn">
                        <span class="wpa11y-toggle-label"><?php esc_html_e( 'Hide Images', 'free-wcag' ); ?></span>
                        <span class="wpa11y-toggle-switch" aria-hidden="true"></span>
                    </button>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>
            
        </div>
    
        <footer class="wpa11y-footer">
            <button type="button" class="wpa11y-reset" data-action="reset">
                <?php esc_html_e( 'Reset All Settings', 'free-wcag' ); ?>
            </button>
            <a href="http://thedeveloper.co.uk/wcag/" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="wpa11y-credit">
                <?php esc_html_e( 'Free WP WCAG', 'free-wcag' ); ?>
            </a>
        </footer>
    </div>
</div>

