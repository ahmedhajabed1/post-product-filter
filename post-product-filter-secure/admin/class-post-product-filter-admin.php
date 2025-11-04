<?php
/**
 * Admin functionality - SECURITY HARDENED
 */

if (!defined('ABSPATH')) exit;

class Post_Product_Filter_Admin {
    
    public function __construct() {
        add_action('wp_ajax_get_preset_data', array($this, 'get_preset_data'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Post/Product Filter',
            'Post/Product Filter',
            'manage_options',
            'post-product-filter',
            array($this, 'presets_page'),
            'dashicons-filter',
            30
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'post-product-filter') === false) {
            return;
        }
        
        wp_enqueue_style(
            'post-product-filter-admin',
            POST_PRODUCT_FILTER_URL . 'admin/css/post-product-filter-admin.css',
            array(),
            POST_PRODUCT_FILTER_VERSION
        );
        
        wp_enqueue_script(
            'post-product-filter-admin',
            POST_PRODUCT_FILTER_URL . 'admin/js/post-product-filter-admin.js',
            array('jquery'),
            POST_PRODUCT_FILTER_VERSION,
            true
        );
        
        wp_localize_script('post-product-filter-admin', 'postProductFilterAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('post_product_filter_admin_nonce')
        ));
    }
    
    public function get_preset_data() {
        check_ajax_referer('post_product_filter_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $preset_slug = isset($_POST['preset_slug']) ? sanitize_key($_POST['preset_slug']) : '';
        $presets = get_option('post_product_filter_presets', array());
        
        if (isset($presets[$preset_slug])) {
            wp_send_json_success($presets[$preset_slug]);
        } else {
            wp_send_json_error('Preset not found');
        }
    }
    
    public function presets_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions.');
        }
        
        if (isset($_POST['save_preset'])) {
            check_admin_referer('post_product_filter_save_preset');
            post_product_filter_save_preset();
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['preset_id'])) {
            check_admin_referer('delete_preset_' . sanitize_key($_GET['preset_id']));
            post_product_filter_delete_preset(sanitize_key($_GET['preset_id']));
        }
        
        $presets = get_option('post_product_filter_presets', array());
        post_product_filter_render_admin_page($presets);
    }
    
    public function output_custom_css() {
        post_product_filter_custom_css();
    }
}
