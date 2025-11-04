<?php
/**
 * Post/Product Filter - Admin Backend
 * 
 * Author: Ahmed haj abed
 * Version: 2.0.0
 */

if (!defined('ABSPATH')) exit;

class Post_Product_Filter_Admin {
    
    public function __construct() {
        add_action('wp_ajax_get_preset_data', array($this, 'post_product_filter_get_preset_data'));
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
            'post-product-filter-admin-style',
            POST_PRODUCT_FILTER_URL . 'admin/css/post-product-filter-admin.css',
            array(),
            POST_PRODUCT_FILTER_VERSION
        );
        
        wp_enqueue_script(
            'post-product-filter-admin-script',
            POST_PRODUCT_FILTER_URL . 'admin/js/post-product-filter-admin.js',
            array('jquery'),
            POST_PRODUCT_FILTER_VERSION,
            true
        );
        
        wp_localize_script('post-product-filter-admin-script', 'postProductFilterAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('post_product_filter_admin_nonce')
        ));
    }
    
    public function post_product_filter_get_preset_data() {
        check_ajax_referer('post_product_filter_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $preset_slug = isset($_POST['preset_slug']) ? sanitize_text_field($_POST['preset_slug']) : '';
        $presets = get_option('post_product_filter_presets', array());
        
        if (isset($presets[$preset_slug])) {
            wp_send_json_success($presets[$preset_slug]);
        } else {
            wp_send_json_error('Preset not found');
        }
    }
    
    public function presets_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'post-product-filter'));
        }
        
        // Handle preset deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['preset_id'])) {
            check_admin_referer('delete_preset_' . sanitize_text_field($_GET['preset_id']));
            post_product_filter_delete_preset(sanitize_text_field($_GET['preset_id']));
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Preset deleted successfully!', 'post-product-filter') . '</p></div>';
        }
        
        // Handle preset save
        if (isset($_POST['save_preset'])) {
            check_admin_referer('post_product_filter_save_preset');
            post_product_filter_save_preset();
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Preset saved successfully!', 'post-product-filter') . '</p></div>';
        }
        
        $presets = get_option('post_product_filter_presets', array());
        
        post_product_filter_render_admin_page($presets);
    }
    
    public function output_custom_css() {
        post_product_filter_custom_css();
    }
}

// Render admin page
function post_product_filter_render_admin_page($presets) {
    $has_presets = !empty($presets);
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
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <span class="dashicons dashicons-filter"></span>
                    </div>
                    <h2><?php esc_html_e('No Presets Yet', 'post-product-filter'); ?></h2>
                    <p><?php esc_html_e('To start, please create a preset to begin filtering your posts or products.', 'post-product-filter'); ?></p>
                    <button type="button" class="button button-primary button-hero" id="add-first-preset-btn">
                        <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Create Your First Preset', 'post-product-filter'); ?>
                    </button>
                </div>
            <?php else : ?>
                <!-- Presets Table -->
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
                                        <div class="shortcode-description">
                                            <strong><?php echo esc_html($preset['name']); ?></strong> • 
                                            <span class="filter-type-label"><?php echo esc_html($type_label); ?> <?php esc_html_e('Filter', 'post-product-filter'); ?></span>
                                        </div>
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
                                       onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this preset?', 'post-product-filter')); ?>');">
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
    <?php post_product_filter_render_modal(); ?>
    <?php
}

// Include the full modal rendering code here (truncated for length - will be in final file)
// ... rest of the admin functions ...

