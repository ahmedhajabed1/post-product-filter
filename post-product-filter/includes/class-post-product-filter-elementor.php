<?php
/**
 * Elementor Widget - FIXED: Preset selector added
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
        // Get all available presets
        $presets = get_option('post_product_filter_presets', array());
        $preset_options = array();
        
        if (!empty($presets)) {
            foreach ($presets as $slug => $preset) {
                $preset_options[$slug] = $preset['name'];
            }
        }
        
        if (empty($preset_options)) {
            $preset_options[''] = __('No presets available - Create one first', 'post-product-filter');
        }
        
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Settings', 'post-product-filter'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'preset_slug',
            [
                'label' => __('Select Preset', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $preset_options,
                'default' => !empty($preset_options) ? array_key_first($preset_options) : '',
                'description' => __('Choose which filter preset to display', 'post-product-filter'),
            ]
        );
        
        // Info message
        $this->add_control(
            'preset_info',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('Go to <strong>WP Admin → Post/Product Filter</strong> to create or manage presets.', 'post-product-filter'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $preset_slug = isset($settings['preset_slug']) ? sanitize_key($settings['preset_slug']) : '';
        
        if (empty($preset_slug)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div style="padding: 20px; background: #f0f0f0; border: 2px dashed #ccc; text-align: center;">';
                echo '<p style="margin: 0; color: #666;"><strong>Post/Product Filter</strong></p>';
                echo '<p style="margin: 10px 0 0 0; color: #999;">Please select a preset in the widget settings.</p>';
                echo '</div>';
            }
            return;
        }
        
        // Render the filter using the shortcode handler
        if (class_exists('Post_Product_Filter_Public')) {
            $public = new Post_Product_Filter_Public();
            echo $public->shortcode_handler(array('slug' => $preset_slug));
        }
    }
    
    protected function content_template() {
        ?>
        <#
        if (settings.preset_slug) {
            #>
            <div style="padding: 20px; background: #f0f0f0; border: 2px solid #2271b1; text-align: center;">
                <p style="margin: 0; color: #2271b1;"><strong>Post/Product Filter</strong></p>
                <p style="margin: 10px 0 0 0; color: #666;">Preset: {{ settings.preset_slug }}</p>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">Preview available on frontend</p>
            </div>
            <#
        } else {
            #>
            <div style="padding: 20px; background: #fff3cd; border: 2px dashed #ffc107; text-align: center;">
                <p style="margin: 0; color: #856404;"><strong>No Preset Selected</strong></p>
                <p style="margin: 10px 0 0 0; color: #856404;">Please select a preset from the settings.</p>
            </div>
            <#
        }
        #>
        <?php
    }
}

// Register the widget
if (did_action('elementor/loaded')) {
    add_action('elementor/widgets/register', function($widgets_manager) {
        if (class_exists('Post_Product_Filter_Elementor_Widget')) {
            $widgets_manager->register(new \Post_Product_Filter_Elementor_Widget());
        }
    });
}
