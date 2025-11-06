<?php
/**
 * Admin functionality - SECURITY ENHANCED v1.0.3
 * - Added POST request method verification
 * - Enhanced nonce verification
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
            'nonce' => wp_create_nonce(POST_PRODUCT_FILTER_ADMIN_NONCE)
        ));
    }
    
    /**
     * AJAX handler to get preset data - with enhanced security
     */
    public function get_preset_data() {
        // Verify nonce
        check_ajax_referer(POST_PRODUCT_FILTER_ADMIN_NONCE, 'nonce');
        
        // Verify capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        // Verify request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => 'Invalid request method'));
            return;
        }
        
        $preset_slug = isset($_POST['preset_slug']) ? sanitize_key($_POST['preset_slug']) : '';
        
        if (empty($preset_slug)) {
            wp_send_json_error(array('message' => 'Invalid preset slug'));
            return;
        }
        
        $presets = get_option('post_product_filter_presets', array());
        
        if (isset($presets[$preset_slug])) {
            wp_send_json_success($presets[$preset_slug]);
        } else {
            wp_send_json_error(array('message' => 'Preset not found'));
        }
    }
    
    /**
     * Render presets page with enhanced security checks
     */
    public function presets_page() {
        // Verify capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'post-product-filter'));
        }
        
        // SECURITY FIX: Verify POST request method for save action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_preset'])) {
            check_admin_referer(POST_PRODUCT_FILTER_SAVE_NONCE);
            
            $result = post_product_filter_save_preset();
            
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Preset saved successfully!', 'post-product-filter') . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Failed to save preset. Please try again.', 'post-product-filter') . '</p></div>';
            }
        }
        
        // Handle delete action with proper verification
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['preset_id'])) {
            $preset_id = sanitize_key($_GET['preset_id']);
            
            // Verify nonce for delete action
            check_admin_referer('delete_preset_' . $preset_id);
            
            $result = post_product_filter_delete_preset($preset_id);
            
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Preset deleted successfully!', 'post-product-filter') . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Failed to delete preset.', 'post-product-filter') . '</p></div>';
            }
        }
        
        $presets = get_option('post_product_filter_presets', array());
        post_product_filter_render_admin_page($presets);
    }
    
    /**
     * Output custom CSS with security filtering
     */
    public function output_custom_css() {
        post_product_filter_custom_css();
    }
}
