<?php
/**
 * Render admin page - Complete admin interface with modal
 */

if (!defined('ABSPATH')) {
    exit;
}

function post_product_filter_render_admin_page($presets) {
    $has_presets = !empty($presets);
    
    // Get categories for modal
    $categories = get_categories(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false
    ));
    
    $product_categories = array();
    if (class_exists('WooCommerce')) {
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false
        ));
    }
    ?>
    <div class="post-product-filter-admin-wrapper">
        <div class="admin-content">
            <div class="admin-header">
                <div>
                    <h1><?php esc_html_e('Post/Product Filter - Presets', 'post-product-filter'); ?></h1>
                    <p class="description"><?php esc_html_e('Create and manage filter presets for your posts and products.', 'post-product-filter'); ?></p>
                </div>
                <button type="button" class="button button-primary" id="add-preset-btn">
                    <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add New Preset', 'post-product-filter'); ?>
                </button>
            </div>
            
            <?php if (!$has_presets) : ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <span class="dashicons dashicons-filter"></span>
                    </div>
                    <h2><?php esc_html_e('No Presets Yet', 'post-product-filter'); ?></h2>
                    <p><?php esc_html_e('Create your first preset to start filtering posts or products.', 'post-product-filter'); ?></p>
                    <button type="button" class="button button-primary button-hero" id="add-first-preset-btn">
                        <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Create Your First Preset', 'post-product-filter'); ?>
                    </button>
                </div>
            <?php else : ?>
                <div class="presets-table-wrapper">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Preset Name', 'post-product-filter'); ?></th>
                                <th><?php esc_html_e('Type', 'post-product-filter'); ?></th>
                                <th><?php esc_html_e('Shortcode', 'post-product-filter'); ?></th>
                                <th class="column-actions"><?php esc_html_e('Actions', 'post-product-filter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presets as $slug => $preset) : 
                                $preset_type = isset($preset['settings']['preset_type']) ? $preset['settings']['preset_type'] : 'posts';
                                $type_label = ucfirst($preset_type);
                                $type_class = strtolower($preset_type);
                            ?>
                            <tr>
                                <td class="preset-name">
                                    <strong><?php echo esc_html($preset['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="preset-type-badge <?php echo esc_attr($type_class); ?>">
                                        <?php echo esc_html($type_label); ?>
                                    </span>
                                </td>
                                <td class="preset-shortcode">
                                    <div class="shortcode-info">
                                        <div class="shortcode-code-wrapper">
                                            <code class="shortcode-text">[post_product_filter slug="<?php echo esc_attr($slug); ?>"]</code>
                                            <button type="button" class="button button-small copy-shortcode-btn" data-shortcode='[post_product_filter slug="<?php echo esc_attr($slug); ?>"]'>
                                                <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy', 'post-product-filter'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="column-actions">
                                    <button type="button" class="button edit-preset-btn" data-preset="<?php echo esc_attr($slug); ?>">
                                        <span class="dashicons dashicons-edit"></span> <?php esc_html_e('Edit', 'post-product-filter'); ?>
                                    </button>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=post-product-filter&action=delete&preset_id=' . urlencode($slug)), 'delete_preset_' . $slug); ?>" 
                                       class="button delete-preset-btn"
                                       onclick="return confirm('Are you sure you want to delete this preset?');">
                                        <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'post-product-filter'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add/Edit Preset Modal -->
    <div id="preset-modal" class="preset-modal" style="display: none;">
        <div class="preset-modal-content">
            <div class="preset-modal-header">
                <h2 id="modal-title"><?php esc_html_e('Add New Preset', 'post-product-filter'); ?></h2>
                <button type="button" class="close-modal">&times;</button>
            </div>
            
            <div class="preset-tabs">
                <button type="button" class="preset-tab-btn active" data-tab="general"><?php esc_html_e('General', 'post-product-filter'); ?></button>
                <button type="button" class="preset-tab-btn" data-tab="display"><?php esc_html_e('Display', 'post-product-filter'); ?></button>
                <button type="button" class="preset-tab-btn" data-tab="styling"><?php esc_html_e('Styling', 'post-product-filter'); ?></button>
            </div>
            
            <form id="preset-form" method="post" action="">
                <?php wp_nonce_field(POST_PRODUCT_FILTER_SAVE_NONCE); ?>
                <input type="hidden" name="preset_slug" id="preset_slug" value="">
                
                <!-- General Tab -->
                <div class="preset-tab-content active" data-tab="general">
                    <div class="form-group">
                        <label for="preset_name"><?php esc_html_e('Preset Name', 'post-product-filter'); ?> *</label>
                        <input type="text" name="preset_name" id="preset_name" class="widefat" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="preset_type"><?php esc_html_e('Filter Type', 'post-product-filter'); ?></label>
                        <select name="preset_type" id="preset_type" class="widefat">
                            <option value="posts"><?php esc_html_e('Posts', 'post-product-filter'); ?></option>
                            <?php if (class_exists('WooCommerce')) : ?>
                            <option value="products"><?php esc_html_e('Products', 'post-product-filter'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="posts_per_page"><?php esc_html_e('Items Per Page', 'post-product-filter'); ?></label>
                        <input type="number" name="posts_per_page" id="posts_per_page" class="widefat" value="6" min="1" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="pagination_type"><?php esc_html_e('Pagination Type', 'post-product-filter'); ?></label>
                        <select name="pagination_type" id="pagination_type" class="widefat">
                            <option value="pagination"><?php esc_html_e('Standard Pagination', 'post-product-filter'); ?></option>
                            <option value="load_more"><?php esc_html_e('Load More Button', 'post-product-filter'); ?></option>
                            <option value="infinite"><?php esc_html_e('Infinite Scroll', 'post-product-filter'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="columns"><?php esc_html_e('Grid Columns', 'post-product-filter'); ?></label>
                        <select name="columns" id="columns" class="widefat">
                            <option value="2"><?php esc_html_e('2 Columns', 'post-product-filter'); ?></option>
                            <option value="3"><?php esc_html_e('3 Columns', 'post-product-filter'); ?></option>
                            <option value="4"><?php esc_html_e('4 Columns', 'post-product-filter'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="form_title"><?php esc_html_e('Filter Title', 'post-product-filter'); ?></label>
                        <input type="text" name="form_title" id="form_title" class="widefat" value="Filter by Categories" maxlength="100">
                    </div>
                    
                    <!-- POST CATEGORIES -->
                    <div class="form-group">
                        <label for="post_categories"><?php esc_html_e('Post Categories (leave empty for all)', 'post-product-filter'); ?></label>
                        <select name="selected_categories[]" id="post_categories" class="widefat" multiple size="8">
                            <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?> (<?php echo absint($category->count); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- PRODUCT CATEGORIES -->
                    <?php if (class_exists('WooCommerce')) : ?>
                    <div class="form-group" style="display:none;">
                        <label for="product_categories"><?php esc_html_e('Product Categories (leave empty for all)', 'post-product-filter'); ?></label>
                        <select name="selected_categories[]" id="product_categories" class="widefat" multiple size="8">
                            <?php foreach ($product_categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?> (<?php echo absint($category->count); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Display Tab -->
                <div class="preset-tab-content" data-tab="display">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="lazy_load" id="lazy_load" checked>
                            <?php esc_html_e('Enable Lazy Loading', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="show_search" id="show_search" checked>
                            <?php esc_html_e('Show Category Search', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="show_count" id="show_count" checked>
                            <?php esc_html_e('Show Category Count', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="show_categories" id="show_categories">
                            <?php esc_html_e('Show Category Badges', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="show_excerpt" id="show_excerpt">
                            <?php esc_html_e('Show Excerpt', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <!-- POST-SPECIFIC OPTIONS -->
                    <div class="form-group field-posts-only">
                        <label>
                            <input type="checkbox" name="show_read_more" id="show_read_more">
                            <?php esc_html_e('Show Read More Button', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group field-posts-only">
                        <label>
                            <input type="checkbox" name="show_meta" id="show_meta">
                            <?php esc_html_e('Show Post Meta (Date, Author)', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <!-- PRODUCT-SPECIFIC OPTIONS -->
                    <div class="form-group field-products-only" style="display:none;">
                        <label>
                            <input type="checkbox" name="show_price" id="show_price" checked>
                            <?php esc_html_e('Show Product Price', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group field-products-only" style="display:none;">
                        <label>
                            <input type="checkbox" name="show_add_to_cart" id="show_add_to_cart" checked>
                            <?php esc_html_e('Show Add to Cart Button', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <div class="form-group field-products-only" style="display:none;">
                        <label>
                            <input type="checkbox" name="hide_out_of_stock" id="hide_out_of_stock">
                            <?php esc_html_e('Hide Out of Stock Products', 'post-product-filter'); ?>
                        </label>
                    </div>
                    
                    <h3><?php esc_html_e('Button Text', 'post-product-filter'); ?></h3>
                    
                    <div class="form-group">
                        <label for="load_more_text"><?php esc_html_e('Load More Text', 'post-product-filter'); ?></label>
                        <input type="text" name="load_more_text" id="load_more_text" class="widefat" value="Load More" maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="loading_text"><?php esc_html_e('Loading Text', 'post-product-filter'); ?></label>
                        <input type="text" name="loading_text" id="loading_text" class="widefat" value="Loading..." maxlength="50">
                    </div>
                    
                    <div class="form-group field-posts-only">
                        <label for="read_more_text"><?php esc_html_e('Read More Text', 'post-product-filter'); ?></label>
                        <input type="text" name="read_more_text" id="read_more_text" class="widefat" value="Read More" maxlength="50">
                    </div>
                    
                    <div class="form-group field-products-only" style="display:none;">
                        <label for="add_to_cart_text"><?php esc_html_e('Add to Cart Text', 'post-product-filter'); ?></label>
                        <input type="text" name="add_to_cart_text" id="add_to_cart_text" class="widefat" value="Add to Cart" maxlength="50">
                    </div>
                </div>
                
                <!-- Styling Tab -->
                <div class="preset-tab-content" data-tab="styling">
                    <h3><?php esc_html_e('Title Styling', 'post-product-filter'); ?></h3>
                    
                    <div class="form-group">
                        <label for="title_font_size"><?php esc_html_e('Title Font Size (px)', 'post-product-filter'); ?></label>
                        <input type="number" name="title_font_size" id="title_font_size" class="widefat" value="20" min="10" max="60">
                    </div>
                    
                    <div class="form-group">
                        <label for="title_color"><?php esc_html_e('Title Color', 'post-product-filter'); ?></label>
                        <input type="color" name="title_color" id="title_color" class="color-picker" value="#333333">
                    </div>
                    
                    <div class="form-group">
                        <label for="title_hover_color"><?php esc_html_e('Title Hover Color', 'post-product-filter'); ?></label>
                        <input type="color" name="title_hover_color" id="title_hover_color" class="color-picker" value="#2271b1">
                    </div>
                    
                    <!-- PRODUCT PRICE STYLING -->
                    <div id="price-styling-section" class="field-products-only" style="display:none;">
                        <h3><?php esc_html_e('Price Styling', 'post-product-filter'); ?></h3>
                        
                        <div class="form-group">
                            <label for="price_color"><?php esc_html_e('Regular Price Color', 'post-product-filter'); ?></label>
                            <input type="color" name="price_color" id="price_color" class="color-picker" value="#333333">
                        </div>
                        
                        <div class="form-group">
                            <label for="sale_price_color"><?php esc_html_e('Sale Price Color', 'post-product-filter'); ?></label>
                            <input type="color" name="sale_price_color" id="sale_price_color" class="color-picker" value="#ff0000">
                        </div>
                    </div>
                    
                    <!-- READ MORE BUTTON STYLING -->
                    <div id="read-more-styling-section" class="field-posts-only">
                        <h3><?php esc_html_e('Read More Button Styling', 'post-product-filter'); ?></h3>
                        
                        <div class="form-group">
                            <label for="button_color"><?php esc_html_e('Button Color', 'post-product-filter'); ?></label>
                            <input type="color" name="button_color" id="button_color" class="color-picker" value="#2271b1">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_text_color"><?php esc_html_e('Button Text Color', 'post-product-filter'); ?></label>
                            <input type="color" name="button_text_color" id="button_text_color" class="color-picker" value="#ffffff">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_hover_color"><?php esc_html_e('Button Hover Color', 'post-product-filter'); ?></label>
                            <input type="color" name="button_hover_color" id="button_hover_color" class="color-picker" value="#135e96">
                        </div>
                    </div>
                    
                    <!-- ADD TO CART BUTTON STYLING -->
                    <div id="add-to-cart-styling-section" class="field-products-only" style="display:none;">
                        <h3><?php esc_html_e('Add to Cart Button Styling', 'post-product-filter'); ?></h3>
                        
                        <div class="form-group">
                            <label for="add_to_cart_bg_color"><?php esc_html_e('Button Color', 'post-product-filter'); ?></label>
                            <input type="color" name="add_to_cart_bg_color" id="add_to_cart_bg_color" class="color-picker" value="#2271b1">
                        </div>
                        
                        <div class="form-group">
                            <label for="add_to_cart_text_color"><?php esc_html_e('Button Text Color', 'post-product-filter'); ?></label>
                            <input type="color" name="add_to_cart_text_color" id="add_to_cart_text_color" class="color-picker" value="#ffffff">
                        </div>
                        
                        <div class="form-group">
                            <label for="add_to_cart_hover_color"><?php esc_html_e('Button Hover Color', 'post-product-filter'); ?></label>
                            <input type="color" name="add_to_cart_hover_color" id="add_to_cart_hover_color" class="color-picker" value="#135e96">
                        </div>
                    </div>
                    
                    <h3><?php esc_html_e('Custom CSS', 'post-product-filter'); ?></h3>
                    <p class="description"><?php esc_html_e('Add custom CSS (dangerous properties will be filtered for security)', 'post-product-filter'); ?></p>
                    
                    <div class="form-group">
                        <label for="custom_css"><?php esc_html_e('Custom CSS', 'post-product-filter'); ?></label>
                        <textarea name="custom_css" id="custom_css" class="widefat" rows="8" maxlength="5000"></textarea>
                    </div>
                </div>
                
                <div class="preset-modal-footer">
                    <button type="button" class="button close-modal"><?php esc_html_e('Cancel', 'post-product-filter'); ?></button>
                    <button type="submit" name="save_preset" class="button button-primary button-large"><?php esc_html_e('Save Preset', 'post-product-filter'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
