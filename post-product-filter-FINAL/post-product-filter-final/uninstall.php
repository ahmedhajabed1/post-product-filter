<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Post_Product_Filter
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check if user wants to delete data
$delete_data = get_option('post_product_filter_delete_on_uninstall', false);

if ($delete_data) {
    // Delete all plugin options
    delete_option('post_product_filter_presets');
    delete_option('post_product_filter_delete_on_uninstall');
    
    // Note: We keep the option names the same for backwards compatibility
    // even though the plugin is renamed
}

// If user chose to keep data, options remain in database for future use
