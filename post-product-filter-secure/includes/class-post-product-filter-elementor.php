<?php
/**
 * Elementor Widget - SECURITY FIX: Proper class existence checks
 */

if (!defined('ABSPATH')) exit;

// Only proceed if Elementor is loaded and base class exists
if (!did_action('elementor/loaded') || !class_exists('\Elementor\Widget_Base')) {
    return;
}

class Post_Product_Filter_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'post-product-filter';
    }

    public function get_title() {
        return __('Post/Product Filter', 'post-product-filter');
    }

    public function get_icon() {
        return 'eicon-posts-grid';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        // Basic controls
        $this->start_controls_section('content_section', [
            'label' => __('Settings', 'post-product-filter'),
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        if (class_exists('Post_Product_Filter_Public')) {
            $public = new Post_Product_Filter_Public();
            echo $public->shortcode_handler(array());
        }
    }
}

if (did_action('elementor/loaded')) {
    add_action('elementor/widgets/register', function($widgets_manager) {
        if (class_exists('Post_Product_Filter_Elementor_Widget')) {
            $widgets_manager->register(new \Post_Product_Filter_Elementor_Widget());
        }
    });
}
