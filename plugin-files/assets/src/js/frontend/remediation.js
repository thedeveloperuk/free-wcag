/**
 * Free WCAG - Remediation Scripts
 * 
 * Handles dynamic accessibility fixes that require JavaScript.
 * 
 * @package Free_WCAG
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * Initialize remediation features when DOM is ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initFocusNotObscured();
        initKeyboardNav();
        initFormLabels();
        initLiveRegions();
        initDragAlternatives();
    });

    /**
     * Focus Not Obscured (WCAG 2.4.11)
     * Ensures focused elements are not hidden by sticky headers/footers
     */
    function initFocusNotObscured() {
        document.addEventListener('focusin', function(e) {
            const target = e.target;
            if (!target) return;

            // Get all position:fixed or position:sticky elements
            const stickyElements = document.querySelectorAll('[style*="position: fixed"], [style*="position: sticky"], .sticky, .fixed');
            
            stickyElements.forEach(function(sticky) {
                const stickyRect = sticky.getBoundingClientRect();
                const targetRect = target.getBoundingClientRect();
                
                // Check if focused element is obscured
                if (isObscured(targetRect, stickyRect)) {
                    // Scroll to make element visible
                    target.scrollIntoView({ block: 'center', behavior: 'smooth' });
                }
            });
        });
    }

    /**
     * Check if an element is obscured by another
     */
    function isObscured(targetRect, obscurerRect) {
        return !(targetRect.bottom < obscurerRect.top || 
                 targetRect.top > obscurerRect.bottom ||
                 targetRect.right < obscurerRect.left ||
                 targetRect.left > obscurerRect.right);
    }

    /**
     * Enhanced Keyboard Navigation
     */
    function initKeyboardNav() {
        // Add visible focus indicators
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('wpa11y-keyboard-nav');
            }
        });

        document.addEventListener('mousedown', function() {
            document.body.classList.remove('wpa11y-keyboard-nav');
        });
    }

    /**
     * Form Labels Enhancement
     * Adds aria-label to inputs without visible labels
     */
    function initFormLabels() {
        const inputs = document.querySelectorAll('input, select, textarea');
        
        inputs.forEach(function(input) {
            // Skip if already has accessible name
            if (input.getAttribute('aria-label') || 
                input.getAttribute('aria-labelledby') ||
                input.id && document.querySelector('label[for="' + input.id + '"]')) {
                return;
            }

            // Try to find associated text
            const placeholder = input.getAttribute('placeholder');
            const title = input.getAttribute('title');
            const name = input.getAttribute('name');

            if (placeholder) {
                input.setAttribute('aria-label', placeholder);
            } else if (title) {
                input.setAttribute('aria-label', title);
            } else if (name) {
                // Convert name to readable label
                const label = name.replace(/[_-]/g, ' ').replace(/\b\w/g, function(l) {
                    return l.toUpperCase();
                });
                input.setAttribute('aria-label', label);
            }
        });
    }

    /**
     * Live Regions for Dynamic Content
     */
    function initLiveRegions() {
        // Create announcer if not exists
        if (!document.getElementById('wpa11y-announcer')) {
            const announcer = document.createElement('div');
            announcer.id = 'wpa11y-announcer';
            announcer.setAttribute('role', 'status');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            announcer.className = 'wpa11y-screen-reader-text';
            document.body.appendChild(announcer);
        }

        // Expose announce function globally
        window.wpa11yAnnounce = function(message, priority) {
            const announcer = document.getElementById('wpa11y-announcer');
            if (announcer) {
                announcer.setAttribute('aria-live', priority === 'assertive' ? 'assertive' : 'polite');
                announcer.textContent = '';
                // Small delay to ensure screen readers pick up the change
                setTimeout(function() {
                    announcer.textContent = message;
                }, 100);
            }
        };
    }

    /**
     * Drag Alternatives (WCAG 2.5.7)
     * Provides keyboard alternatives for drag operations
     */
    function initDragAlternatives() {
        const draggables = document.querySelectorAll('[draggable="true"]');
        
        draggables.forEach(function(el) {
            // Make draggable elements keyboard accessible
            if (!el.getAttribute('tabindex')) {
                el.setAttribute('tabindex', '0');
            }
            
            // Add keyboard instructions
            if (!el.getAttribute('aria-describedby')) {
                el.setAttribute('aria-keyshortcuts', 'Space Enter');
            }
        });
    }

})();

