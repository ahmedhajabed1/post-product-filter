<?php
/**
 * AJAX Post Filter - Elementor Widget
 * 
 * Author: Ahmed haj abed
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

// Don't load if Elementor is not active
if (!did_action('elementor/loaded')) {
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

    public function get_keywords() {
        return ['post', 'filter', 'ajax', 'category', 'blog'];
    }

    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('General Settings', 'post-product-filter'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 50,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '2' => __('2 Columns', 'post-product-filter'),
                    '3' => __('3 Columns', 'post-product-filter'),
                    '4' => __('4 Columns', 'post-product-filter'),
                ],
            ]
        );

        $this->add_control(
            'pagination_type',
            [
                'label' => __('Pagination Type', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'pagination',
                'options' => [
                    'pagination' => __('Standard Pagination', 'post-product-filter'),
                    'load_more' => __('Load More Button', 'post-product-filter'),
                    'infinite' => __('Infinite Scroll', 'post-product-filter'),
                ],
            ]
        );

        $this->end_controls_section();

        // Display Options
        $this->start_controls_section(
            'display_section',
            [
                'label' => __('Display Options', 'post-product-filter'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_search',
            [
                'label' => __('Show Category Search', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'post-product-filter'),
                'label_off' => __('Hide', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_count',
            [
                'label' => __('Show Post Count', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'post-product-filter'),
                'label_off' => __('Hide', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Post Excerpt', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'post-product-filter'),
                'label_off' => __('Hide', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'show_read_more',
            [
                'label' => __('Show Read More Button', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'post-product-filter'),
                'label_off' => __('Hide', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'show_meta',
            [
                'label' => __('Show Post Meta (Date, Author)', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'post-product-filter'),
                'label_off' => __('Hide', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'show_categories',
            [
                'label' => __('Show Category Badges', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'post-product-filter'),
                'label_off' => __('Hide', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'lazy_load',
            [
                'label' => __('Enable Lazy Load', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'post-product-filter'),
                'label_off' => __('No', 'post-product-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Load images only when visible', 'post-product-filter'),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Typography & Colors', 'post-product-filter'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_font_size',
            [
                'label' => __('Title Font Size', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 60,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Title Hover Color', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2271b1',
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2271b1',
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => __('Button Background', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2271b1',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Button Text Color', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => __('Button Hover Background', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#135e96',
            ]
        );

        $this->end_controls_section();

        // Button Text Section
        $this->start_controls_section(
            'button_text_section',
            [
                'label' => __('Button Text', 'post-product-filter'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'load_more_text',
            [
                'label' => __('Load More Text', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Load More', 'post-product-filter'),
                'condition' => [
                    'pagination_type' => 'load_more',
                ],
            ]
        );

        $this->add_control(
            'loading_text',
            [
                'label' => __('Loading Text', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Loading...', 'post-product-filter'),
            ]
        );

        $this->add_control(
            'read_more_text',
            [
                'label' => __('Read More Text', 'post-product-filter'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Read More', 'post-product-filter'),
                'condition' => [
                    'show_read_more' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Create a preset-like structure
        $preset = array(
            'name' => 'Elementor Widget',
            'slug' => 'elementor-' . $this->get_id(),
            'settings' => array(
                'posts_per_page' => $settings['posts_per_page'],
                'show_search' => $settings['show_search'] === 'yes',
                'show_count' => $settings['show_count'] === 'yes',
                'show_excerpt' => $settings['show_excerpt'] === 'yes',
                'show_read_more' => $settings['show_read_more'] === 'yes',
                'show_meta' => $settings['show_meta'] === 'yes',
                'show_categories' => $settings['show_categories'] === 'yes',
                'lazy_load' => $settings['lazy_load'] === 'yes',
                'pagination_type' => $settings['pagination_type'],
                'columns' => $settings['columns'],
                'title_font_size' => $settings['title_font_size']['size'],
                'title_color' => $settings['title_color'],
                'title_hover_color' => $settings['title_hover_color'],
                'primary_color' => $settings['primary_color'],
                'button_color' => $settings['button_color'],
                'button_text_color' => $settings['button_text_color'],
                'button_hover_color' => $settings['button_hover_color'],
                'load_more_text' => $settings['load_more_text'],
                'loading_text' => $settings['loading_text'],
                'read_more_text' => $settings['read_more_text']
            )
        );
        
        // Add inline styles
        ?>
        <style>
            .elementor-element-<?php echo $this->get_id(); ?> .read-more,
            .elementor-element-<?php echo $this->get_id(); ?> .load-more-btn,
            .elementor-element-<?php echo $this->get_id(); ?> .active-filter-tag {
                background-color: <?php echo esc_attr($settings['button_color']); ?> !important;
                color: <?php echo esc_attr($settings['button_text_color']); ?> !important;
            }
            .elementor-element-<?php echo $this->get_id(); ?> .read-more:hover,
            .elementor-element-<?php echo $this->get_id(); ?> .load-more-btn:hover {
                background-color: <?php echo esc_attr($settings['button_hover_color']); ?> !important;
            }
            .elementor-element-<?php echo $this->get_id(); ?> .posts-grid {
                grid-template-columns: repeat(<?php echo esc_attr($settings['columns']); ?>, 1fr) !important;
            }
            .elementor-element-<?php echo $this->get_id(); ?> .post-title {
                font-size: <?php echo esc_attr($settings['title_font_size']['size']); ?>px !important;
            }
            .elementor-element-<?php echo $this->get_id(); ?> .post-title a {
                color: <?php echo esc_attr($settings['title_color']); ?> !important;
            }
            .elementor-element-<?php echo $this->get_id(); ?> .post-title a:hover {
                color: <?php echo esc_attr($settings['title_hover_color']); ?> !important;
            }
        </style>
        <?php
        
        // Render the filter
        if (class_exists('Post_Product_Filter_Public')) {
            $public = new Post_Product_Filter_Public();
            $public->render_filter($preset, $preset['slug']);
        }
    }
}

// Register Elementor widget
function register_post_product_filter_elementor_widget($widgets_manager) {
    $widgets_manager->register(new \Post_Product_Filter_Elementor_Widget());
}
add_action('elementor/widgets/register', 'register_post_product_filter_elementor_widget');
