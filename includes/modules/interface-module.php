<?php
/**
 * Module interface.
 *
 * @package WP_Accessibility_Suite
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Interface for accessibility modules.
 */
interface WPA11Y_Module_Interface {

    /**
     * Get module ID.
     *
     * @return string
     */
    public function get_id(): string;

    /**
     * Get module name.
     *
     * @return string
     */
    public function get_name(): string;

    /**
     * Get module description.
     *
     * @return string
     */
    public function get_description(): string;

    /**
     * Get available features.
     *
     * @return array
     */
    public function get_features(): array;

    /**
     * Check if module is enabled.
     *
     * @return bool
     */
    public function is_enabled(): bool;

    /**
     * Initialize module hooks.
     *
     * @return void
     */
    public function init(): void;

    /**
     * Enqueue module assets.
     *
     * @return void
     */
    public function enqueue_assets(): void;
}

