<?php
/**
 * Plugin Name: Post/Product Filter
 * Plugin URI: https://example.com/post-product-filter
 * Description: Advanced AJAX blog post and WooCommerce product filter with lazy loading, multiple pagination types, SEO optimization, and full customization options. Security hardened version.
 * Version: 1.0.3
 * Author: Ahmed haj abed
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: post-product-filter
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
define('POST_PRODUCT_FILTER_VERSION', '1.0.3');

// Plugin path
define('POST_PRODUCT_FILTER_PATH', plugin_dir_path(__FILE__));

// Plugin URL
define('POST_PRODUCT_FILTER_URL', plugin_dir_url(__FILE__));

// Security: Define nonce action constants
define('POST_PRODUCT_FILTER_AJAX_NONCE', 'post_filter_nonce');
define('POST_PRODUCT_FILTER_ADMIN_NONCE', 'post_product_filter_admin_nonce');
define('POST_PRODUCT_FILTER_SAVE_NONCE', 'post_product_filter_save_preset');

/**
 * The core plugin class
 */
require_once POST_PRODUCT_FILTER_PATH . 'includes/class-post-product-filter-core.php';

/**
 * Admin class
 */
require_once POST_PRODUCT_FILTER_PATH . 'admin/class-post-product-filter-admin.php';

/**
 * Public class
 */
require_once POST_PRODUCT_FILTER_PATH . 'public/class-post-product-filter-public.php';

/**
 * AJAX handler
 */
require_once POST_PRODUCT_FILTER_PATH . 'includes/class-post-product-filter-ajax-handler.php';

/**
 * Helper functions
 */
require_once POST_PRODUCT_FILTER_PATH . 'includes/helper-functions.php';

/**
 * Initialize the plugin
 */
function post_product_filter_init() {
    $plugin = new Post_Product_Filter_Core();
    $plugin->run();
}
add_action('plugins_loaded', 'post_product_filter_init');

/**
 * Activation hook
 */
function post_product_filter_activate() {
    // Create empty presets array
    add_option('post_product_filter_presets', array());
    
    // Add option to track if database should be deleted on uninstall
    add_option('post_product_filter_delete_on_uninstall', false);
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'post_product_filter_activate');

/**
 * Deactivation hook
 */
function post_product_filter_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'post_product_filter_deactivate');
