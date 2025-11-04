<?php
/**
 * AJAX Handler for post and product filtering
 *
 * @package Post_Product_Filter
 * @author  Ahmed haj abed
 */

if (!defined('ABSPATH')) {
    exit;
}

class Post_Product_Filter_Ajax_Handler {
    
    public function filter_posts() {
        // Verify nonce
        check_ajax_referer('post_filter_nonce', 'nonce');
        
        // Get selected categories
        $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : array();
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 6;
        $lazy_load = isset($_POST['lazy_load']) && $_POST['lazy_load'] === 'true';
        $pagination_type = isset($_POST['pagination_type']) ? sanitize_text_field($_POST['pagination_type']) : 'pagination';
        $preset_type = isset($_POST['preset_type']) ? sanitize_text_field($_POST['preset_type']) : 'posts';
        
        // Display options
        $show_excerpt = isset($_POST['show_excerpt']) && $_POST['show_excerpt'] === 'true';
        $show_read_more = isset($_POST['show_read_more']) && $_POST['show_read_more'] === 'true';
        $show_meta = isset($_POST['show_meta']) && $_POST['show_meta'] === 'true';
        $show_categories = isset($_POST['show_categories']) && $_POST['show_categories'] === 'true';
        $read_more_text = isset($_POST['read_more_text']) ? sanitize_text_field($_POST['read_more_text']) : 'Read More';
        $add_to_cart_text = isset($_POST['add_to_cart_text']) ? sanitize_text_field($_POST['add_to_cart_text']) : 'Add to Cart';
        
        // Product specific options
        $hide_out_of_stock = isset($_POST['hide_out_of_stock']) && $_POST['hide_out_of_stock'] === 'true';
        
        // Query arguments
        if ($preset_type === 'products' && class_exists('WooCommerce')) {
            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => '_thumbnail_id',
                        'compare' => 'EXISTS'
                    )
                )
            );
            
            // Hide out of stock products
            if ($hide_out_of_stock) {
                $args['meta_query'][] = array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                );
            }
            
            // Add product category filter
            if (!empty($categories)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $categories
                    )
                );
            }
        } else {
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => '_thumbnail_id',
                        'compare' => 'EXISTS'
                    )
                )
            );
            
            // Add category filter if categories are selected
            if (!empty($categories)) {
                $args['category__in'] = $categories;
            }
        }
        
        $query = new WP_Query($args);
        
        $response = array(
            'posts' => '',
            'pagination' => '',
            'found_posts' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
            'current_page' => $paged
        );
        
        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                
                // Get thumbnail
                $thumbnail_id = get_post_thumbnail_id();
                $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';
                $thumbnail_srcset = $thumbnail_id ? wp_get_attachment_image_srcset($thumbnail_id, 'large') : '';
                
                if ($preset_type === 'products' && class_exists('WooCommerce')) {
                    // Render product
                    global $product;
                    $product = wc_get_product(get_the_ID());
                    if (!$product) continue;
                    ?>
                    <article class="product-item" data-product-id="<?php echo get_the_ID(); ?>">
                        <?php if ($thumbnail_url) : ?>
                            <div class="product-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if ($lazy_load) : ?>
                                        <img 
                                            class="lazy-load" 
                                            data-src="<?php echo esc_url($thumbnail_url); ?>"
                                            <?php if ($thumbnail_srcset) : ?>
                                            data-srcset="<?php echo esc_attr($thumbnail_srcset); ?>"
                                            <?php endif; ?>
                                            alt="<?php echo esc_attr(get_the_title()); ?>"
                                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3C/svg%3E">
                                    <?php else : ?>
                                        <img 
                                            src="<?php echo esc_url($thumbnail_url); ?>"
                                            <?php if ($thumbnail_srcset) : ?>
                                            srcset="<?php echo esc_attr($thumbnail_srcset); ?>"
                                            <?php endif; ?>
                                            alt="<?php echo esc_attr(get_the_title()); ?>">
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-content">
                            <?php if ($show_categories) : ?>
                            <div class="product-categories">
                                <?php
                                $product_categories = get_the_terms(get_the_ID(), 'product_cat');
                                if ($product_categories && !is_wp_error($product_categories)) {
                                    foreach ($product_categories as $category) {
                                        echo '<span class="product-category">' . esc_html($category->name) . '</span>';
                                    }
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <h2 class="product-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="product-price">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            
                            <?php if ($show_excerpt) : ?>
                            <div class="product-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="product-actions">
                                <?php
                                if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
                                    echo sprintf(
                                        '<a href="%s" data-product_id="%s" class="button add_to_cart_button product_type_simple" rel="nofollow">%s</a>',
                                        esc_url($product->add_to_cart_url()),
                                        esc_attr($product->get_id()),
                                        esc_html($add_to_cart_text)
                                    );
                                } else {
                                    echo sprintf(
                                        '<a href="%s" class="button view-product">View Product</a>',
                                        esc_url(get_permalink())
                                    );
                                }
                                ?>
                            </div>
                        </div>
                    </article>
                    <?php
                } else {
                    // Render post
                    ?>
                    <article class="post-item" data-post-id="<?php echo get_the_ID(); ?>">
                        <?php if ($thumbnail_url) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if ($lazy_load) : ?>
                                        <img 
                                            class="lazy-load" 
                                            data-src="<?php echo esc_url($thumbnail_url); ?>"
                                            <?php if ($thumbnail_srcset) : ?>
                                            data-srcset="<?php echo esc_attr($thumbnail_srcset); ?>"
                                            <?php endif; ?>
                                            alt="<?php echo esc_attr(get_the_title()); ?>"
                                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3C/svg%3E">
                                    <?php else : ?>
                                        <img 
                                            src="<?php echo esc_url($thumbnail_url); ?>"
                                            <?php if ($thumbnail_srcset) : ?>
                                            srcset="<?php echo esc_attr($thumbnail_srcset); ?>"
                                            <?php endif; ?>
                                            alt="<?php echo esc_attr(get_the_title()); ?>">
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <?php if ($show_categories) : ?>
                            <div class="post-categories">
                                <?php
                                $post_categories = get_the_category();
                                if ($post_categories) {
                                    foreach ($post_categories as $category) {
                                        echo '<span class="post-category">' . esc_html($category->name) . '</span>';
                                    }
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <?php if ($show_meta) : ?>
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-author">by <?php the_author(); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($show_excerpt) : ?>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($show_read_more) : ?>
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                <?php echo esc_html($read_more_text); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php
                }
            }
            $response['posts'] = ob_get_clean();
            
            // Pagination or Load More
            if ($query->max_num_pages > 1) {
                ob_start();
                
                if ($pagination_type === 'load_more') {
                    if ($paged < $query->max_num_pages) {
                        $load_more_text = isset($_POST['load_more_text']) ? sanitize_text_field($_POST['load_more_text']) : 'Load More';
                        ?>
                        <div class="load-more-wrapper">
                            <button type="button" class="button load-more-btn" data-page="<?php echo ($paged + 1); ?>">
                                <?php echo esc_html($load_more_text); ?>
                            </button>
                        </div>
                        <?php
                    }
                } else if ($pagination_type === 'pagination') {
                    echo '<div class="pagination">';
                    echo paginate_links(array(
                        'total' => $query->max_num_pages,
                        'current' => $paged,
                        'format' => '?paged=%#%',
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                    ));
                    echo '</div>';
                }
                
                $response['pagination'] = ob_get_clean();
            }
        } else {
            $no_items_message = $preset_type === 'products' ? 
                'No products found matching your criteria.' : 
                'No posts found matching your criteria.';
            $response['posts'] = '<div class="no-posts">' . esc_html($no_items_message) . '</div>';
        }
        
        wp_reset_postdata();
        
        wp_send_json_success($response);
    }
}
