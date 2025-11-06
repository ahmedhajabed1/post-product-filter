<?php
/**
 * Helper Functions - SECURITY ENHANCED v1.0.4
 * - Fixed CSS injection vulnerability
 * - Added category validation
 * - Added MORE styling options (SECURE)
 * - Added sanitize_hex_color fallback
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fallback for sanitize_hex_color if not available
 */
if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {
        if ('' === $color) {
            return '';
        }
        
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }
        
        return '';
    }
}

/**
 * Sanitize CSS - Remove dangerous CSS properties
 */
function post_product_filter_sanitize_css($css) {
    if (empty($css)) {
        return '';
    }
    
    $css = wp_strip_all_tags($css);
    
    $dangerous_patterns = array(
        '/expression\s*\(/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/@import/i',
        '/binding\s*:/i',
        '/behaviour\s*:/i',
        '/url\s*\(\s*["\']?\s*data:/i',
    );
    
    foreach ($dangerous_patterns as $pattern) {
        $css = preg_replace($pattern, '', $css);
    }
    
    $css = sanitize_textarea_field($css);
    
    return $css;
}

/**
 * Save preset with enhanced security + NEW STYLING OPTIONS
 */
function post_product_filter_save_preset() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], POST_PRODUCT_FILTER_SAVE_NONCE)) {
        return false;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    $presets = get_option('post_product_filter_presets', array());
    
    $preset_slug = isset($_POST['preset_slug']) && !empty($_POST['preset_slug']) 
        ? sanitize_key($_POST['preset_slug']) 
        : '';
    
    $preset_name = isset($_POST['preset_name']) && !empty($_POST['preset_name']) 
        ? sanitize_text_field($_POST['preset_name']) 
        : '';
    
    if (empty($preset_name)) {
        return false;
    }
    
    if (empty($preset_slug)) {
        $preset_slug = sanitize_key(str_replace(' ', '-', strtolower($preset_name)));
    }
    
    $preset_type = isset($_POST['preset_type']) ? sanitize_key($_POST['preset_type']) : 'posts';
    $allowed_types = array('posts', 'products');
    if (!in_array($preset_type, $allowed_types, true)) {
        $preset_type = 'posts';
    }
    
    $pagination_type = isset($_POST['pagination_type']) ? sanitize_key($_POST['pagination_type']) : 'pagination';
    $allowed_pagination = array('pagination', 'load_more', 'infinite');
    if (!in_array($pagination_type, $allowed_pagination, true)) {
        $pagination_type = 'pagination';
    }
    
    $columns = isset($_POST['columns']) ? sanitize_key($_POST['columns']) : '2';
    $allowed_columns = array('2', '3', '4');
    if (!in_array($columns, $allowed_columns, true)) {
        $columns = '2';
    }
    
    // Validate selected categories
    $selected_categories = isset($_POST['selected_categories']) && is_array($_POST['selected_categories']) 
        ? array_map('absint', $_POST['selected_categories']) 
        : array();
    
    $valid_categories = array();
    if (!empty($selected_categories)) {
        $taxonomy = ($preset_type === 'products' && class_exists('WooCommerce')) ? 'product_cat' : 'category';
        
        foreach ($selected_categories as $cat_id) {
            if ($cat_id > 0 && term_exists($cat_id, $taxonomy)) {
                $valid_categories[] = $cat_id;
            }
        }
    }
    
    // Validate numeric inputs
    $posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : 6;
    $posts_per_page = max(1, min(100, $posts_per_page));
    
    $title_font_size = isset($_POST['title_font_size']) ? absint($_POST['title_font_size']) : 20;
    $title_font_size = max(10, min(60, $title_font_size));
    
    // NEW: Additional padding/spacing options (SECURE - bounded)
    $container_padding = isset($_POST['container_padding']) ? absint($_POST['container_padding']) : 40;
    $container_padding = max(0, min(100, $container_padding));
    
    $item_spacing = isset($_POST['item_spacing']) ? absint($_POST['item_spacing']) : 30;
    $item_spacing = max(0, min(100, $item_spacing));
    
    $category_font_size = isset($_POST['category_font_size']) ? absint($_POST['category_font_size']) : 14;
    $category_font_size = max(10, min(24, $category_font_size));
    
    $result_count_font_size = isset($_POST['result_count_font_size']) ? absint($_POST['result_count_font_size']) : 16;
    $result_count_font_size = max(12, min(24, $result_count_font_size));
    
    // Sanitize text fields
    $form_title = isset($_POST['form_title']) ? substr(sanitize_text_field($_POST['form_title']), 0, 100) : 'Filter by Categories';
    $load_more_text = isset($_POST['load_more_text']) ? substr(sanitize_text_field($_POST['load_more_text']), 0, 50) : 'Load More';
    $loading_text = isset($_POST['loading_text']) ? substr(sanitize_text_field($_POST['loading_text']), 0, 50) : 'Loading...';
    $read_more_text = isset($_POST['read_more_text']) ? substr(sanitize_text_field($_POST['read_more_text']), 0, 50) : 'Read More';
    $add_to_cart_text = isset($_POST['add_to_cart_text']) ? substr(sanitize_text_field($_POST['add_to_cart_text']), 0, 50) : 'Add to Cart';
    
    $custom_css = isset($_POST['custom_css']) ? post_product_filter_sanitize_css($_POST['custom_css']) : '';
    
    $settings = array(
        'preset_type' => $preset_type,
        'posts_per_page' => $posts_per_page,
        'pagination_type' => $pagination_type,
        'columns' => $columns,
        'lazy_load' => isset($_POST['lazy_load']),
        'show_search' => isset($_POST['show_search']),
        'show_count' => isset($_POST['show_count']),
        'show_excerpt' => isset($_POST['show_excerpt']),
        'show_read_more' => isset($_POST['show_read_more']),
        'show_meta' => isset($_POST['show_meta']),
        'show_categories' => isset($_POST['show_categories']),
        'selected_categories' => $valid_categories,
        'form_title' => $form_title,
        'hide_out_of_stock' => isset($_POST['hide_out_of_stock']),
        'show_price' => isset($_POST['show_price']),
        'show_add_to_cart' => isset($_POST['show_add_to_cart']),
        
        // Typography
        'title_font_size' => $title_font_size,
        'category_font_size' => $category_font_size,
        'result_count_font_size' => $result_count_font_size,
        
        // Colors
        'title_color' => isset($_POST['title_color']) ? sanitize_hex_color($_POST['title_color']) : '#333333',
        'title_hover_color' => isset($_POST['title_hover_color']) ? sanitize_hex_color($_POST['title_hover_color']) : '#2271b1',
        'price_color' => isset($_POST['price_color']) ? sanitize_hex_color($_POST['price_color']) : '#333333',
        'sale_price_color' => isset($_POST['sale_price_color']) ? sanitize_hex_color($_POST['sale_price_color']) : '#ff0000',
        
        // Button styling
        'button_color' => isset($_POST['button_color']) ? sanitize_hex_color($_POST['button_color']) : '#2271b1',
        'button_text_color' => isset($_POST['button_text_color']) ? sanitize_hex_color($_POST['button_text_color']) : '#ffffff',
        'button_hover_color' => isset($_POST['button_hover_color']) ? sanitize_hex_color($_POST['button_hover_color']) : '#135e96',
        
        // NEW: Load More button styling (SECURE)
        'load_more_bg_color' => isset($_POST['load_more_bg_color']) ? sanitize_hex_color($_POST['load_more_bg_color']) : '#2271b1',
        'load_more_text_color' => isset($_POST['load_more_text_color']) ? sanitize_hex_color($_POST['load_more_text_color']) : '#ffffff',
        'load_more_hover_color' => isset($_POST['load_more_hover_color']) ? sanitize_hex_color($_POST['load_more_hover_color']) : '#135e96',
        
        // NEW: Category list styling (SECURE)
        'category_bg_color' => isset($_POST['category_bg_color']) ? sanitize_hex_color($_POST['category_bg_color']) : '#f0f0f0',
        'category_text_color' => isset($_POST['category_text_color']) ? sanitize_hex_color($_POST['category_text_color']) : '#666666',
        'category_hover_bg' => isset($_POST['category_hover_bg']) ? sanitize_hex_color($_POST['category_hover_bg']) : '#f8f9fa',
        
        // NEW: Result count styling (SECURE)
        'result_count_color' => isset($_POST['result_count_color']) ? sanitize_hex_color($_POST['result_count_color']) : '#666666',
        
        // Add to Cart styling
        'add_to_cart_bg_color' => isset($_POST['add_to_cart_bg_color']) ? sanitize_hex_color($_POST['add_to_cart_bg_color']) : '#2271b1',
        'add_to_cart_text_color' => isset($_POST['add_to_cart_text_color']) ? sanitize_hex_color($_POST['add_to_cart_text_color']) : '#ffffff',
        'add_to_cart_hover_color' => isset($_POST['add_to_cart_hover_color']) ? sanitize_hex_color($_POST['add_to_cart_hover_color']) : '#135e96',
        
        // NEW: Spacing/Padding (SECURE - bounded)
        'container_padding' => $container_padding,
        'item_spacing' => $item_spacing,
        
        // Button text
        'load_more_text' => $load_more_text,
        'loading_text' => $loading_text,
        'read_more_text' => $read_more_text,
        'add_to_cart_text' => $add_to_cart_text,
        'custom_css' => $custom_css
    );
    
    $presets[$preset_slug] = array(
        'name' => $preset_name,
        'slug' => $preset_slug,
        'settings' => $settings
    );
    
    update_option('post_product_filter_presets', $presets);
    
    return true;
}

/**
 * Delete preset
 */
function post_product_filter_delete_preset($preset_slug) {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    $preset_slug = sanitize_key($preset_slug);
    $presets = get_option('post_product_filter_presets', array());
    
    if (isset($presets[$preset_slug])) {
        unset($presets[$preset_slug]);
        update_option('post_product_filter_presets', $presets);
        return true;
    }
    
    return false;
}

/**
 * Output custom CSS with NEW STYLING OPTIONS (SECURE)
 */
function post_product_filter_custom_css() {
    $presets = get_option('post_product_filter_presets', array());
    
    if (empty($presets)) {
        return;
    }
    
    echo '<style type="text/css" id="post-product-filter-custom-css">';
    
    foreach ($presets as $slug => $preset) {
        if (!isset($preset['settings'])) {
            continue;
        }
        
        $settings = $preset['settings'];
        $selector = '.post-product-filter-' . esc_attr($slug);
        
        // Container padding
        if (isset($settings['container_padding'])) {
            echo esc_html($selector) . ' { padding: ' . absint($settings['container_padding']) . 'px !important; }';
        }
        
        // Item spacing
        if (isset($settings['item_spacing'])) {
            echo esc_html($selector) . ' .posts-grid { gap: ' . absint($settings['item_spacing']) . 'px !important; }';
        }
        
        // Title styling
        if (!empty($settings['title_font_size'])) {
            echo esc_html($selector) . ' .post-title, ' . esc_html($selector) . ' .product-title { font-size: ' . absint($settings['title_font_size']) . 'px !important; }';
        }
        if (!empty($settings['title_color'])) {
            echo esc_html($selector) . ' .post-title a, ' . esc_html($selector) . ' .product-title a { color: ' . sanitize_hex_color($settings['title_color']) . ' !important; }';
        }
        if (!empty($settings['title_hover_color'])) {
            echo esc_html($selector) . ' .post-title a:hover, ' . esc_html($selector) . ' .product-title a:hover { color: ' . sanitize_hex_color($settings['title_hover_color']) . ' !important; }';
        }
        
        // Category styling (NEW - SECURE)
        if (isset($settings['category_font_size'])) {
            echo esc_html($selector) . ' .post-category, ' . esc_html($selector) . ' .product-category { font-size: ' . absint($settings['category_font_size']) . 'px !important; }';
        }
        if (!empty($settings['category_bg_color'])) {
            echo esc_html($selector) . ' .post-category, ' . esc_html($selector) . ' .product-category { background-color: ' . sanitize_hex_color($settings['category_bg_color']) . ' !important; }';
        }
        if (!empty($settings['category_text_color'])) {
            echo esc_html($selector) . ' .post-category, ' . esc_html($selector) . ' .product-category { color: ' . sanitize_hex_color($settings['category_text_color']) . ' !important; }';
        }
        if (!empty($settings['category_hover_bg'])) {
            echo esc_html($selector) . ' .filter-option:hover { background-color: ' . sanitize_hex_color($settings['category_hover_bg']) . ' !important; }';
        }
        
        // Result count styling (NEW - SECURE)
        if (isset($settings['result_count_font_size'])) {
            echo esc_html($selector) . ' .results-count { font-size: ' . absint($settings['result_count_font_size']) . 'px !important; }';
        }
        if (!empty($settings['result_count_color'])) {
            echo esc_html($selector) . ' .results-count { color: ' . sanitize_hex_color($settings['result_count_color']) . ' !important; }';
        }
        
        // Read More button
        if (!empty($settings['button_color'])) {
            echo esc_html($selector) . ' .read-more { background-color: ' . sanitize_hex_color($settings['button_color']) . ' !important; }';
        }
        if (!empty($settings['button_text_color'])) {
            echo esc_html($selector) . ' .read-more { color: ' . sanitize_hex_color($settings['button_text_color']) . ' !important; }';
        }
        if (!empty($settings['button_hover_color'])) {
            echo esc_html($selector) . ' .read-more:hover { background-color: ' . sanitize_hex_color($settings['button_hover_color']) . ' !important; }';
        }
        
        // Load More button (NEW - SECURE)
        if (!empty($settings['load_more_bg_color'])) {
            echo esc_html($selector) . ' .load-more-btn { background-color: ' . sanitize_hex_color($settings['load_more_bg_color']) . ' !important; }';
        }
        if (!empty($settings['load_more_text_color'])) {
            echo esc_html($selector) . ' .load-more-btn { color: ' . sanitize_hex_color($settings['load_more_text_color']) . ' !important; }';
        }
        if (!empty($settings['load_more_hover_color'])) {
            echo esc_html($selector) . ' .load-more-btn:hover { background-color: ' . sanitize_hex_color($settings['load_more_hover_color']) . ' !important; }';
        }
        
        // Price styling
        if (!empty($settings['price_color'])) {
            echo esc_html($selector) . ' .product-price { color: ' . sanitize_hex_color($settings['price_color']) . ' !important; }';
        }
        if (!empty($settings['sale_price_color'])) {
            echo esc_html($selector) . ' .product-price ins { color: ' . sanitize_hex_color($settings['sale_price_color']) . ' !important; }';
        }
        
        // Add to Cart button
        if (!empty($settings['add_to_cart_bg_color'])) {
            echo esc_html($selector) . ' .add_to_cart_button { background-color: ' . sanitize_hex_color($settings['add_to_cart_bg_color']) . ' !important; }';
        }
        if (!empty($settings['add_to_cart_text_color'])) {
            echo esc_html($selector) . ' .add_to_cart_button { color: ' . sanitize_hex_color($settings['add_to_cart_text_color']) . ' !important; }';
        }
        if (!empty($settings['add_to_cart_hover_color'])) {
            echo esc_html($selector) . ' .add_to_cart_button:hover { background-color: ' . sanitize_hex_color($settings['add_to_cart_hover_color']) . ' !important; }';
        }
        
        // Custom CSS
        if (!empty($settings['custom_css'])) {
            echo esc_html($selector) . ' { }';
            echo "\n" . post_product_filter_sanitize_css($settings['custom_css']) . "\n";
        }
    }
    
    echo '</style>';
}

require_once POST_PRODUCT_FILTER_PATH . 'includes/helper-functions-render.php';
