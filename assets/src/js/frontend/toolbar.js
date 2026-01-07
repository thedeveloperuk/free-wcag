/**
 * WP Accessibility Suite - Frontend Toolbar
 * 
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    /**
     * Accessibility Preferences Manager
     */
    class A11yPreferences {
        constructor() {
            this.storageKey = 'wpa11y_preferences';
            this.defaults = {
                contrast: 'default',
                fontSize: 100,
                fontFamily: 'default',
                grayscale: false,
                invertColors: false,
                lowSaturation: false,
                largeCursor: false,
                readingGuide: false,
                readingMask: false,
                highlightLinks: false,
                highlightHeadings: false,
                pauseAnimations: false,
                hideImages: false,
            };
            
            this.preferences = this.load();
        }
        
        /**
         * Load preferences from localStorage
         */
        load() {
            try {
                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    return { ...this.defaults, ...JSON.parse(stored) };
                }
            } catch (e) {
                console.warn('A11y: Could not load preferences', e);
            }
            return { ...this.defaults };
        }
        
        /**
         * Save preferences to localStorage
         */
        save() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.preferences));
            } catch (e) {
                console.warn('A11y: Could not save preferences', e);
            }
        }
        
        /**
         * Get a preference value
         */
        get(key) {
            return this.preferences[key];
        }
        
        /**
         * Set a preference value
         */
        set(key, value) {
            if (key in this.defaults) {
                this.preferences[key] = value;
                this.save();
                return true;
            }
            return false;
        }
        
        /**
         * Toggle a boolean preference
         */
        toggle(key) {
            if (key in this.defaults && typeof this.defaults[key] === 'boolean') {
                this.preferences[key] = !this.preferences[key];
                this.save();
                return this.preferences[key];
            }
            return null;
        }
        
        /**
         * Reset all preferences to defaults
         */
        reset() {
            this.preferences = { ...this.defaults };
            localStorage.removeItem(this.storageKey);
        }
    }
    
    /**
     * Accessibility Toolbar Controller
     */
    class A11yToolbar {
        constructor() {
            this.prefs = new A11yPreferences();
            this.toolbar = document.getElementById('wpa11y-toolbar');
            this.trigger = document.getElementById('wpa11y-trigger');
            this.panel = document.getElementById('wpa11y-panel');
            this.announcer = document.getElementById('wpa11y-announcer');
            
            this.readingGuideEl = null;
            this.readingMaskEl = null;
            
            if (!this.toolbar) return;
            
            this.init();
        }
        
        /**
         * Initialize toolbar
         */
        init() {
            // Apply saved preferences
            this.applyAllPreferences();
            
            // Set up event listeners
            this.bindEvents();
            
            // Update UI to match saved state
            this.updateUI();
        }
        
        /**
         * Bind event listeners
         */
        bindEvents() {
            // Trigger button
            if (this.trigger) {
                this.trigger.addEventListener('click', () => this.togglePanel());
            }
            
            // Close button
            const closeBtn = this.panel?.querySelector('.wpa11y-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.closePanel());
            }
            
            // Escape key to close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isPanelOpen()) {
                    this.closePanel();
                }
            });
            
            // Click outside to close
            document.addEventListener('click', (e) => {
                if (this.isPanelOpen() && 
                    !this.panel.contains(e.target) && 
                    !this.trigger.contains(e.target)) {
                    this.closePanel();
                }
            });
            
            // Option buttons (contrast modes, etc.)
            this.panel?.querySelectorAll('.wpa11y-option-btn').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleOptionClick(e));
            });
            
            // Switch buttons (toggles)
            this.panel?.querySelectorAll('.wpa11y-switch-btn').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleSwitchClick(e));
            });
            
            // Slider inputs
            this.panel?.querySelectorAll('input[type="range"]').forEach(input => {
                input.addEventListener('input', (e) => this.handleSliderInput(e));
            });
            
            // Increment/decrement buttons
            this.panel?.querySelectorAll('.wpa11y-btn-decrease, .wpa11y-btn-increase').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleIncrementClick(e));
            });
            
            // Reset button
            const resetBtn = this.panel?.querySelector('.wpa11y-reset');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => this.resetAll());
            }
            
            // Reading guide mouse tracking
            document.addEventListener('mousemove', (e) => this.updateReadingGuide(e));
        }
        
        /**
         * Check if panel is open
         */
        isPanelOpen() {
            return this.trigger?.getAttribute('aria-expanded') === 'true';
        }
        
        /**
         * Toggle panel visibility
         */
        togglePanel() {
            if (this.isPanelOpen()) {
                this.closePanel();
            } else {
                this.openPanel();
            }
        }
        
        /**
         * Open panel
         */
        openPanel() {
            this.trigger.setAttribute('aria-expanded', 'true');
            this.panel.hidden = false;
            
            // Focus first focusable element
            const firstFocusable = this.panel.querySelector('button, input, select, [tabindex]');
            if (firstFocusable) {
                firstFocusable.focus();
            }
            
            // Trap focus within panel
            this.trapFocus();
        }
        
        /**
         * Close panel
         */
        closePanel() {
            this.trigger.setAttribute('aria-expanded', 'false');
            this.panel.hidden = true;
            this.trigger.focus();
        }
        
        /**
         * Trap focus within panel
         */
        trapFocus() {
            const focusableElements = this.panel.querySelectorAll(
                'button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            const firstFocusable = focusableElements[0];
            const lastFocusable = focusableElements[focusableElements.length - 1];
            
            this.panel.addEventListener('keydown', (e) => {
                if (e.key !== 'Tab') return;
                
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            });
        }
        
        /**
         * Handle option button click (radio-style)
         */
        handleOptionClick(event) {
            const btn = event.currentTarget;
            const action = btn.dataset.action;
            const value = btn.dataset.value;
            
            // Update button states
            const group = btn.closest('.wpa11y-button-group');
            group?.querySelectorAll('.wpa11y-option-btn').forEach(b => {
                b.classList.remove('is-active');
                b.setAttribute('aria-checked', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-checked', 'true');
            
            // Apply the change
            this.applyAction(action, value);
        }
        
        /**
         * Handle switch button click (toggle)
         */
        handleSwitchClick(event) {
            const btn = event.currentTarget;
            const action = btn.dataset.action;
            const isChecked = btn.getAttribute('aria-checked') === 'true';
            
            btn.setAttribute('aria-checked', !isChecked);
            this.applyAction(action, !isChecked);
        }
        
        /**
         * Handle slider input
         */
        handleSliderInput(event) {
            const input = event.currentTarget;
            const action = input.dataset.action;
            const value = parseInt(input.value, 10);
            
            // Update output display
            const output = input.parentElement.querySelector('output');
            if (output) {
                output.textContent = `${value}%`;
            }
            
            this.applyAction(action, value);
        }
        
        /**
         * Handle increment/decrement button click
         */
        handleIncrementClick(event) {
            const btn = event.currentTarget;
            const action = btn.dataset.action;
            const delta = parseInt(btn.dataset.delta, 10);
            
            const slider = btn.parentElement.querySelector('input[type="range"]');
            if (slider) {
                const newValue = Math.max(
                    parseInt(slider.min, 10),
                    Math.min(parseInt(slider.max, 10), parseInt(slider.value, 10) + delta)
                );
                slider.value = newValue;
                slider.dispatchEvent(new Event('input'));
            }
        }
        
        /**
         * Apply an action
         */
        applyAction(action, value) {
            const root = document.documentElement;
            let announcementKey = action;
            
            switch (action) {
                case 'contrast':
                    root.dataset.a11yContrast = value;
                    this.prefs.set('contrast', value);
                    break;
                    
                case 'font-size':
                    root.style.setProperty('--wpa11y-font-scale', value / 100);
                    this.prefs.set('fontSize', value);
                    break;
                    
                case 'grayscale':
                    root.dataset.a11yGrayscale = value;
                    this.prefs.set('grayscale', value);
                    break;
                    
                case 'invert-colors':
                    root.dataset.a11yInvertColors = value;
                    this.prefs.set('invertColors', value);
                    break;
                    
                case 'low-saturation':
                    root.dataset.a11yLowSaturation = value;
                    this.prefs.set('lowSaturation', value);
                    break;
                    
                case 'readable-font':
                    root.dataset.a11yFont = value ? 'readable' : 'default';
                    this.prefs.set('fontFamily', value ? 'readable' : 'default');
                    break;
                    
                case 'dyslexia-font':
                    root.dataset.a11yFont = value ? 'dyslexia' : 'default';
                    this.prefs.set('fontFamily', value ? 'dyslexia' : 'default');
                    break;
                    
                case 'large-cursor':
                    root.dataset.a11yCursor = value ? 'large' : 'default';
                    this.prefs.set('largeCursor', value);
                    break;
                    
                case 'reading-guide':
                    this.toggleReadingGuide(value);
                    this.prefs.set('readingGuide', value);
                    break;
                    
                case 'reading-mask':
                    this.toggleReadingMask(value);
                    this.prefs.set('readingMask', value);
                    break;
                    
                case 'highlight-links':
                    root.dataset.a11yHighlightLinks = value;
                    this.prefs.set('highlightLinks', value);
                    break;
                    
                case 'highlight-headings':
                    root.dataset.a11yHighlightHeadings = value;
                    this.prefs.set('highlightHeadings', value);
                    break;
                    
                case 'pause-animations':
                    root.dataset.a11yMotion = value ? 'reduce' : 'allow';
                    this.prefs.set('pauseAnimations', value);
                    break;
                    
                case 'hide-images':
                    root.dataset.a11yHideImages = value;
                    this.prefs.set('hideImages', value);
                    break;
            }
            
            // Announce change
            this.announce(action, value);
        }
        
        /**
         * Apply all saved preferences
         */
        applyAllPreferences() {
            const root = document.documentElement;
            const prefs = this.prefs.preferences;
            
            // Contrast
            if (prefs.contrast !== 'default') {
                root.dataset.a11yContrast = prefs.contrast;
            }
            
            // Font size
            if (prefs.fontSize !== 100) {
                root.style.setProperty('--wpa11y-font-scale', prefs.fontSize / 100);
            }
            
            // Font family
            if (prefs.fontFamily !== 'default') {
                root.dataset.a11yFont = prefs.fontFamily;
            }
            
            // Boolean toggles
            if (prefs.grayscale) root.dataset.a11yGrayscale = 'true';
            if (prefs.invertColors) root.dataset.a11yInvertColors = 'true';
            if (prefs.lowSaturation) root.dataset.a11yLowSaturation = 'true';
            if (prefs.largeCursor) root.dataset.a11yCursor = 'large';
            if (prefs.highlightLinks) root.dataset.a11yHighlightLinks = 'true';
            if (prefs.highlightHeadings) root.dataset.a11yHighlightHeadings = 'true';
            if (prefs.pauseAnimations) root.dataset.a11yMotion = 'reduce';
            if (prefs.hideImages) root.dataset.a11yHideImages = 'true';
            
            // Reading aids
            if (prefs.readingGuide) this.toggleReadingGuide(true);
            if (prefs.readingMask) this.toggleReadingMask(true);
        }
        
        /**
         * Update UI to match saved preferences
         */
        updateUI() {
            const prefs = this.prefs.preferences;
            
            // Contrast buttons
            const contrastBtns = this.panel?.querySelectorAll('[data-action="contrast"]');
            contrastBtns?.forEach(btn => {
                const isActive = btn.dataset.value === prefs.contrast;
                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('aria-checked', isActive);
            });
            
            // Font size slider
            const fontSizeSlider = this.panel?.querySelector('#wpa11y-font-size');
            if (fontSizeSlider) {
                fontSizeSlider.value = prefs.fontSize;
                const output = fontSizeSlider.parentElement.querySelector('output');
                if (output) output.textContent = `${prefs.fontSize}%`;
            }
            
            // Toggle switches
            const toggleMappings = {
                'grayscale': 'grayscale',
                'invert-colors': 'invertColors',
                'low-saturation': 'lowSaturation',
                'readable-font': 'fontFamily',
                'dyslexia-font': 'fontFamily',
                'large-cursor': 'largeCursor',
                'reading-guide': 'readingGuide',
                'reading-mask': 'readingMask',
                'highlight-links': 'highlightLinks',
                'highlight-headings': 'highlightHeadings',
                'pause-animations': 'pauseAnimations',
                'hide-images': 'hideImages',
            };
            
            Object.entries(toggleMappings).forEach(([action, prefKey]) => {
                const btn = this.panel?.querySelector(`[data-action="${action}"]`);
                if (btn) {
                    let isChecked = false;
                    if (prefKey === 'fontFamily') {
                        isChecked = prefs.fontFamily === action.replace('-font', '');
                    } else {
                        isChecked = prefs[prefKey];
                    }
                    btn.setAttribute('aria-checked', isChecked);
                }
            });
        }
        
        /**
         * Toggle reading guide overlay
         */
        toggleReadingGuide(enabled) {
            if (enabled) {
                if (!this.readingGuideEl) {
                    this.readingGuideEl = document.createElement('div');
                    this.readingGuideEl.className = 'wpa11y-reading-guide';
                    document.body.appendChild(this.readingGuideEl);
                }
                this.readingGuideEl.style.display = 'block';
            } else if (this.readingGuideEl) {
                this.readingGuideEl.style.display = 'none';
            }
        }
        
        /**
         * Toggle reading mask overlay
         */
        toggleReadingMask(enabled) {
            if (enabled) {
                if (!this.readingMaskEl) {
                    this.readingMaskEl = document.createElement('div');
                    this.readingMaskEl.className = 'wpa11y-reading-mask';
                    document.body.appendChild(this.readingMaskEl);
                }
                this.readingMaskEl.style.display = 'block';
            } else if (this.readingMaskEl) {
                this.readingMaskEl.style.display = 'none';
            }
        }
        
        /**
         * Update reading guide position
         */
        updateReadingGuide(event) {
            if (this.readingGuideEl && this.readingGuideEl.style.display !== 'none') {
                this.readingGuideEl.style.top = `${event.clientY - 20}px`;
            }
            
            if (this.readingMaskEl && this.readingMaskEl.style.display !== 'none') {
                const maskHeight = 60; // Height of visible area
                const viewportHeight = window.innerHeight;
                const topMask = Math.max(0, event.clientY - maskHeight / 2);
                const bottomMask = Math.max(0, viewportHeight - event.clientY - maskHeight / 2);
                
                this.readingMaskEl.style.setProperty('--mask-top', `${topMask}px`);
                this.readingMaskEl.style.setProperty('--mask-bottom', `${bottomMask}px`);
            }
        }
        
        /**
         * Reset all preferences
         */
        resetAll() {
            const root = document.documentElement;
            
            // Remove all data attributes
            delete root.dataset.a11yContrast;
            delete root.dataset.a11yGrayscale;
            delete root.dataset.a11yInvertColors;
            delete root.dataset.a11yLowSaturation;
            delete root.dataset.a11yFont;
            delete root.dataset.a11yCursor;
            delete root.dataset.a11yHighlightLinks;
            delete root.dataset.a11yHighlightHeadings;
            delete root.dataset.a11yMotion;
            delete root.dataset.a11yHideImages;
            
            // Reset CSS variable
            root.style.removeProperty('--wpa11y-font-scale');
            
            // Hide overlays
            this.toggleReadingGuide(false);
            this.toggleReadingMask(false);
            
            // Reset preferences
            this.prefs.reset();
            
            // Update UI
            this.updateUI();
            
            // Announce
            this.announce('reset', true);
        }
        
        /**
         * Announce change to screen readers
         */
        announce(action, value) {
            if (!this.announcer) return;
            
            const strings = window.wpa11yConfig?.strings || {};
            let message = '';
            
            switch (action) {
                case 'contrast':
                    message = `Contrast mode: ${value}`;
                    break;
                case 'font-size':
                    message = `Text size: ${value}%`;
                    break;
                case 'reset':
                    message = 'All accessibility settings reset';
                    break;
                default:
                    const actionName = action.replace(/-/g, ' ');
                    message = `${actionName} ${value ? strings.enabled || 'enabled' : strings.disabled || 'disabled'}`;
            }
            
            this.announcer.textContent = message;
        }
    }
    
    /**
     * Initialize toolbar when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new A11yToolbar());
    } else {
        new A11yToolbar();
    }
    
})();

