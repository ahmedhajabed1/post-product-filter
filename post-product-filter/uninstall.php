<?php
/**
 * Fired when the plugin is uninstalled.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!defined('ABSPATH')) {
    exit;
}

$delete_data = get_option('post_product_filter_delete_on_uninstall', false);

if ($delete_data) {
    delete_option('post_product_filter_presets');
    delete_option('post_product_filter_delete_on_uninstall');
}
