/**
 * Free WCAG Accessibility Suite - Admin Dashboard
 * 
 * Uses global functions for Alpine.js components for maximum compatibility.
 * 
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

/**
 * Main admin dashboard controller
 * Called via x-data="wpa11yAdmin()"
 */
window.wpa11yAdmin = function() {
    return {
        activeTab: 'modules',
        settings: JSON.parse(JSON.stringify(window.wpa11ySettings || {})),
        originalSettings: JSON.parse(JSON.stringify(window.wpa11ySettings || {})),
        saving: false,
        saved: false,
        saveTimeout: null,
        
        /**
         * Initialize the admin panel
         */
        init() {
            // Watch for changes to auto-save
            this.$watch('settings', () => {
                if (this.hasChanges) {
                    this.debouncedSave();
                }
            }, { deep: true });
        },
        
        /**
         * Check if settings have changed
         */
        get hasChanges() {
            return JSON.stringify(this.settings) !== JSON.stringify(this.originalSettings);
        },
        
        /**
         * Debounced save function
         */
        debouncedSave() {
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            this.saveTimeout = setTimeout(() => {
                this.saveSettings();
            }, 1000);
        },
        
        /**
         * Save settings via REST API
         */
        async saveSettings() {
            if (this.saving) return;
            
            this.saving = true;
            this.saved = false;
            
            try {
                const response = await fetch(`${window.wpa11yRest.root}wpa11y/v1/settings`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.wpa11yRest.nonce,
                    },
                    body: JSON.stringify(this.settings),
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.originalSettings = JSON.parse(JSON.stringify(data.settings));
                    this.saved = true;
                    
                    // Hide saved message after 3 seconds
                    setTimeout(() => {
                        this.saved = false;
                    }, 3000);
                } else {
                    throw new Error('Save failed');
                }
            } catch (error) {
                console.error('Failed to save settings:', error);
                this.showNotice(window.wpa11yAdminData?.strings?.saveFailed || 'Failed to save settings.', 'error');
            } finally {
                this.saving = false;
            }
        },
        
        /**
         * Reset settings to defaults
         */
        async resetSettings() {
            const confirmMessage = window.wpa11yAdminData?.strings?.resetConfirm || 
                'Reset all settings to defaults? This cannot be undone.';
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            try {
                const response = await fetch(`${window.wpa11yRest.root}wpa11y/v1/settings`, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': window.wpa11yRest.nonce,
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.settings = JSON.parse(JSON.stringify(data));
                    this.originalSettings = JSON.parse(JSON.stringify(data));
                    this.showNotice(window.wpa11yAdminData?.strings?.reset || 'Settings reset to defaults.', 'success');
                }
            } catch (error) {
                console.error('Failed to reset settings:', error);
                this.showNotice('Failed to reset settings.', 'error');
            }
        },
        
        /**
         * Format feature name for display
         */
        formatFeatureName(feature) {
            return feature
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
        },
        
        /**
         * Show WordPress admin notice
         */
        showNotice(message, type = 'info') {
            const notice = document.createElement('div');
            notice.className = `notice notice-${type} is-dismissible`;
            notice.innerHTML = `<p>${message}</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>`;
            
            const header = document.querySelector('.wpa11y-admin-header');
            if (header) {
                header.after(notice);
            }
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.3s';
                setTimeout(() => notice.remove(), 300);
            }, 5000);
            
            // Handle manual dismiss
            notice.querySelector('.notice-dismiss').addEventListener('click', () => {
                notice.remove();
            });
        }
    };
};

/**
 * Scanner controller
 * Called via x-data="wpa11yScanner()"
 */
window.wpa11yScanner = function() {
    return {
        scanning: false,
        progress: 0,
        progressText: '',
        results: [],
        severityFilter: '',
        currentBatch: 0,
        totalBatches: 0,
        
        /**
         * Initialize scanner
         */
        init() {
            this.loadResults();
        },
        
        /**
         * Get filtered results
         */
        get filteredResults() {
            if (!this.severityFilter) {
                return this.results;
            }
            return this.results.filter(r => r.severity === this.severityFilter);
        },
        
        /**
         * Count results by severity
         */
        get errorCount() {
            return this.results.filter(r => r.severity === 'error').length;
        },
        
        get warningCount() {
            return this.results.filter(r => r.severity === 'warning').length;
        },
        
        get noticeCount() {
            return this.results.filter(r => r.severity === 'notice').length;
        },
        
        /**
         * Start a scan
         */
        async startScan(type = 'full') {
            this.scanning = true;
            this.progress = 0;
            this.progressText = window.wpa11yAdminData?.strings?.scanStarted || 'Starting scan...';
            this.results = [];
            
            try {
                // Start scan via REST API
                const startResponse = await fetch(`${window.wpa11yRest.root}wpa11y/v1/scan/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.wpa11yRest.nonce,
                    },
                    body: JSON.stringify({ type }),
                });
                
                if (!startResponse.ok) {
                    throw new Error('Failed to start scan');
                }
                
                const { scan_id, total_batches, total_posts } = await startResponse.json();
                this.totalBatches = total_batches;
                
                // Process batches via AJAX (more reliable for long operations)
                for (let batch = 0; batch < total_batches; batch++) {
                    this.currentBatch = batch;
                    this.progress = Math.round(((batch + 1) / total_batches) * 100);
                    this.progressText = `Scanning... ${batch + 1} of ${total_batches} batches (${this.progress}%)`;
                    
                    await this.processBatch(type, batch);
                }
                
                // Load final results
                await this.loadResults();
                
                this.progressText = window.wpa11yAdminData?.strings?.scanComplete || 'Scan complete!';
                
            } catch (error) {
                console.error('Scan failed:', error);
                this.progressText = window.wpa11yAdminData?.strings?.scanFailed || 'Scan failed. Please try again.';
            } finally {
                setTimeout(() => {
                    this.scanning = false;
                }, 2000);
            }
        },
        
        /**
         * Process a single batch via AJAX
         */
        async processBatch(type, batch) {
            const formData = new FormData();
            formData.append('action', 'wpa11y_scan');
            formData.append('nonce', window.wpa11yAdminData.nonce);
            formData.append('scan_type', type);
            formData.append('batch', batch);
            
            const response = await fetch(window.wpa11yAdminData.ajaxUrl, {
                method: 'POST',
                body: formData,
            });
            
            return response.json();
        },
        
        /**
         * Load scan results
         */
        async loadResults() {
            try {
                const response = await fetch(`${window.wpa11yRest.root}wpa11y/v1/scan/results`, {
                    headers: {
                        'X-WP-Nonce': window.wpa11yRest.nonce,
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.results = data.results || [];
                }
            } catch (error) {
                console.error('Failed to load results:', error);
            }
        },
        
        /**
         * Mark an issue as resolved
         */
        async resolveIssue(issueId) {
            try {
                const response = await fetch(`${window.wpa11yRest.root}wpa11y/v1/scan/resolve/${issueId}`, {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': window.wpa11yRest.nonce,
                    },
                });
                
                if (response.ok) {
                    // Update local state
                    const issue = this.results.find(r => r.id === issueId);
                    if (issue) {
                        issue.resolved_at = new Date().toISOString();
                    }
                }
            } catch (error) {
                console.error('Failed to resolve issue:', error);
            }
        }
    };
};
