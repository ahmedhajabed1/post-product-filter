<?php
/**
 * Public functionality - FIXED DATA ATTRIBUTES
 */

if (!defined('ABSPATH')) {
    exit;
}

class Post_Product_Filter_Public {
    
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'post-product-filter-public',
            POST_PRODUCT_FILTER_URL . 'public/css/post-product-filter-public.css',
            array(),
            POST_PRODUCT_FILTER_VERSION
        );
        
        wp_enqueue_script(
            'post-product-filter-public',
            POST_PRODUCT_FILTER_URL . 'public/js/post-product-filter-public.js',
            array('jquery'),
            POST_PRODUCT_FILTER_VERSION,
            true
        );
        
        wp_localize_script('post-product-filter-public', 'postProductFilter', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('post_filter_nonce')
        ));
    }
    
    public function shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'slug' => 'default-preset',
        ), $atts);
        
        $presets = get_option('post_product_filter_presets', array());
        $preset = isset($presets[$atts['slug']]) ? $presets[$atts['slug']] : null;
        
        if (!$preset) {
            return '<div class="post-product-filter-error">
                <p><strong>Error:</strong> Preset "' . esc_html($atts['slug']) . '" not found.</p>
                <p>Available presets: ' . implode(', ', array_keys($presets)) . '</p>
            </div>';
        }
        
        ob_start();
        $this->render_filter($preset, $atts['slug']);
        return ob_get_clean();
    }
    
    public function render_filter($preset = null, $preset_slug = 'default') {
        if (!$preset || !isset($preset['settings'])) {
            echo '<div class="post-product-filter-error">',
                 '<p>Invalid preset configuration.</p>',
                 '</div>';
            return;
        }
        
        $settings = $preset['settings'];
        
        // Get ALL settings with defaults
        $preset_type = isset($settings['preset_type']) ? $settings['preset_type'] : 'posts';
        $posts_per_page = isset($settings['posts_per_page']) ? absint($settings['posts_per_page']) : 6;
        $pagination_type = isset($settings['pagination_type']) ? $settings['pagination_type'] : 'pagination';
        $columns = isset($settings['columns']) ? $settings['columns'] : '2';
        $lazy_load = isset($settings['lazy_load']) ? $settings['lazy_load'] : true;
        $show_search = isset($settings['show_search']) ? $settings['show_search'] : true;
        $show_count = isset($settings['show_count']) ? $settings['show_count'] : true;
        $show_excerpt = isset($settings['show_excerpt']) ? $settings['show_excerpt'] : false;
        $show_read_more = isset($settings['show_read_more']) ? $settings['show_read_more'] : false;
        $show_meta = isset($settings['show_meta']) ? $settings['show_meta'] : false;
        $show_categories = isset($settings['show_categories']) ? $settings['show_categories'] : false;
        $selected_categories = isset($settings['selected_categories']) ? $settings['selected_categories'] : array();
        $form_title = isset($settings['form_title']) ? $settings['form_title'] : 'Filter by Categories';
        $load_more_text = isset($settings['load_more_text']) ? $settings['load_more_text'] : 'Load More';
        $loading_text = isset($settings['loading_text']) ? $settings['loading_text'] : 'Loading...';
        $read_more_text = isset($settings['read_more_text']) ? $settings['read_more_text'] : 'Read More';
        $add_to_cart_text = isset($settings['add_to_cart_text']) ? $settings['add_to_cart_text'] : 'Add to Cart';
        $hide_out_of_stock = isset($settings['hide_out_of_stock']) ? $settings['hide_out_of_stock'] : false;
        
        // Debug output (remove in production)
        if (current_user_can('manage_options')) {
            echo '<!-- Filter Debug: Posts Per Page = ' . $posts_per_page . ', Pagination Type = ' . $pagination_type . ' -->';
        }
        ?>
        
        <div id="post-product-filter-wrapper" 
             class="post-product-filter-<?php echo esc_attr($preset_slug); ?>"
             data-preset-type="<?php echo esc_attr($preset_type); ?>"
             data-lazy-load="<?php echo $lazy_load ? 'true' : 'false'; ?>"
             data-pagination-type="<?php echo esc_attr($pagination_type); ?>"
             data-posts-per-page="<?php echo esc_attr($posts_per_page); ?>"
             data-load-more-text="<?php echo esc_attr($load_more_text); ?>"
             data-loading-text="<?php echo esc_attr($loading_text); ?>"
             data-show-excerpt="<?php echo $show_excerpt ? 'true' : 'false'; ?>"
             data-show-read-more="<?php echo $show_read_more ? 'true' : 'false'; ?>"
             data-show-meta="<?php echo $show_meta ? 'true' : 'false'; ?>"
             data-show-categories="<?php echo $show_categories ? 'true' : 'false'; ?>"
             data-read-more-text="<?php echo esc_attr($read_more_text); ?>"
             data-add-to-cart-text="<?php echo esc_attr($add_to_cart_text); ?>"
             data-hide-out-of-stock="<?php echo $hide_out_of_stock ? 'true' : 'false'; ?>"
             data-columns="<?php echo esc_attr($columns); ?>">
            
            <div class="filter-container">
                <!-- Filter Sidebar -->
                <div class="filter-sidebar">
                    <div class="filter-widget">
                        <h3 class="filter-title"><?php echo esc_html($form_title); ?></h3>
                        
                        <?php if ($show_search) : ?>
                        <div class="filter-search">
                            <input type="text" id="category-search" placeholder="<?php esc_attr_e('Search categories...', 'post-product-filter'); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <div class="filter-options">
                            <?php
                            // Get categories based on type
                            if ($preset_type === 'products' && class_exists('WooCommerce')) {
                                $categories = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'hide_empty' => true,
                                    'include' => !empty($selected_categories) ? $selected_categories : ''
                                ));
                            } else {
                                $categories = get_categories(array(
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'hide_empty' => true,
                                    'include' => !empty($selected_categories) ? $selected_categories : ''
                                ));
                            }
                            
                            foreach ($categories as $category) :
                                $count = $category->count;
                            ?>
                                <label class="filter-option" data-category-name="<?php echo esc_attr(strtolower($category->name)); ?>">
                                    <input type="checkbox" 
                                           name="category_filter" 
                                           value="<?php echo esc_attr($category->term_id); ?>"
                                           data-count="<?php echo esc_attr($count); ?>"
                                           class="auto-filter">
                                    <span class="filter-label">
                                        <?php echo esc_html($category->name); ?>
                                        <?php if ($show_count) : ?>
                                        <span class="filter-count">(<?php echo absint($count); ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="active-filters" style="display: none;">
                        <h4><?php esc_html_e('Active Filters:', 'post-product-filter'); ?></h4>
                        <div id="active-filters-list"></div>
                    </div>
                </div>
                
                <!-- Posts Container -->
                <div class="posts-container">
                    <div class="posts-header">
                        <div class="results-count">
                            <?php esc_html_e('Showing', 'post-product-filter'); ?> 
                            <span id="results-count">0</span> 
                            <?php esc_html_e('results', 'post-product-filter'); ?>
                        </div>
                        
                        <div class="loading-overlay" style="display: none;">
                            <div class="loader"></div>
                            <p class="loading-text"><?php echo esc_html($loading_text); ?></p>
                        </div>
                    </div>
                    
                    <div id="posts-grid" class="posts-grid" style="grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);">
                        <!-- Posts will be loaded here via AJAX -->
                    </div>
                    
                    <div id="posts-pagination" class="posts-pagination">
                        <!-- Pagination will be loaded here via AJAX -->
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    public function add_seo_meta() {
        if (!is_singular()) {
            return;
        }
        
        global $wp_query;
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        
        if ($paged > 1) {
            $prev_page = $paged - 1;
            if ($prev_page == 1) {
                echo '<link rel="prev" href="' . esc_url(get_permalink()) . '" />' . "\n";
            } else {
                echo '<link rel="prev" href="' . esc_url(add_query_arg('paged', $prev_page, get_permalink())) . '" />' . "\n";
            }
        }
        
        if ($paged < $wp_query->max_num_pages) {
            $next_page = $paged + 1;
            echo '<link rel="next" href="' . esc_url(add_query_arg('paged', $next_page, get_permalink())) . '" />' . "\n";
        }
    }
}
