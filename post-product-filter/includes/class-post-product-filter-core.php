<?php
/**
 * The core plugin class
 *
 * @package Post_Product_Filter
 * @author  Ahmed haj abed
 */

if (!defined('ABSPATH')) {
    exit;
}

class Post_Product_Filter_Core {
    
    protected $admin;
    protected $public;
    protected $ajax_handler;
    
    public function __construct() {
        $this->load_dependencies();
    }
    
    private function load_dependencies() {
        $this->admin = new Post_Product_Filter_Admin();
        $this->public = new Post_Product_Filter_Public();
        $this->ajax_handler = new Post_Product_Filter_Ajax_Handler();
    }
    
    public function run() {
        // Admin hooks
        add_action('admin_menu', array($this->admin, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
        
        // Public hooks
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_public_assets'));
        add_shortcode('post_product_filter', array($this->public, 'shortcode_handler'));
        
        // AJAX hooks
        add_action('wp_ajax_filter_posts', array($this->ajax_handler, 'filter_posts'));
        add_action('wp_ajax_nopriv_filter_posts', array($this->ajax_handler, 'filter_posts'));
        
        // SEO hooks
        add_action('wp_head', array($this->public, 'add_seo_meta'));
        
        // Custom CSS
        add_action('wp_head', array($this->admin, 'output_custom_css'));
        
        // Elementor integration - only load if Elementor is active
        add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
    }
    
    /**
     * Register Elementor widget - with proper class checks (SECURITY FIX)
     */
    public function register_elementor_widget($widgets_manager) {
        // Only proceed if Elementor is loaded and the widget manager is valid
        if (!did_action('elementor/loaded') || !$widgets_manager) {
            return;
        }
        
        // Load the Elementor widget file
        $elementor_file = POST_PRODUCT_FILTER_PATH . 'includes/class-post-product-filter-elementor.php';
        if (file_exists($elementor_file)) {
            require_once $elementor_file;
            
            // Register widget only if the class was successfully loaded
            if (class_exists('Post_Product_Filter_Elementor_Widget')) {
                $widgets_manager->register(new Post_Product_Filter_Elementor_Widget());
            }
        }
    }
}
