jQuery(document).ready(function($) {
    'use strict';
    
    // Tab switching
    $('.preset-tab-btn').on('click', function() {
        const tabName = $(this).data('tab');
        $('.preset-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.preset-tab-content').removeClass('active');
        $(`.preset-tab-content[data-tab="${tabName}"]`).addClass('active');
    });
    
    // Preset type switching - show/hide relevant fields
    $('#preset_type').on('change', function() {
        const presetType = $(this).val();
        
        if (presetType === 'products') {
            // Show product fields, hide post fields
            $('.field-products-only').show();
            $('.field-posts-only').hide();
            
            // Disable post categories, enable product categories
            $('#post_categories').prop('disabled', true);
            $('#product_categories').prop('disabled', false);
        } else {
            // Show post fields, hide product fields
            $('.field-posts-only').show();
            $('.field-products-only').hide();
            
            // Enable post categories, disable product categories
            $('#post_categories').prop('disabled', false);
            $('#product_categories').prop('disabled', true);
        }
    });
    
    // Open Add Preset Modal
    $('#add-preset-btn, #add-first-preset-btn').on('click', function() {
        $('#modal-title').text('Add New Preset');
        $('#preset-form')[0].reset();
        $('#preset_slug').val('');
        $('#preset_type').val('posts').trigger('change'); // Trigger change to show/hide fields
        
        // Reset to first tab
        $('.preset-tab-btn').removeClass('active').first().addClass('active');
        $('.preset-tab-content').removeClass('active').first().addClass('active');
        
        $('#preset-modal').fadeIn(200);
        $('body').addClass('modal-open');
    });
    
    // Edit Preset - Load data via AJAX
    $('.edit-preset-btn').on('click', function() {
        const presetSlug = $(this).data('preset');
        
        $('#modal-title').text('Edit Preset');
        $('#preset_slug').val(presetSlug);
        
        // Reset to first tab
        $('.preset-tab-btn').removeClass('active').first().addClass('active');
        $('.preset-tab-content').removeClass('active').first().addClass('active');
        
        // Fetch preset data
        $.ajax({
            url: postProductFilterAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_preset_data',
                nonce: postProductFilterAdmin.nonce,
                preset_slug: presetSlug
            },
            success: function(response) {
                if (response.success && response.data) {
                    populatePresetForm(response.data);
                }
            }
        });
        
        $('#preset-modal').fadeIn(200);
        $('body').addClass('modal-open');
    });
    
    // Populate form with preset data
    function populatePresetForm(data) {
        if (!data.settings) return;
        
        // Set preset name
        $('#preset_name').val(data.name || '');
        
        // General settings
        $('#preset_type').val(data.settings.preset_type || 'posts').trigger('change');
        $('#posts_per_page').val(data.settings.posts_per_page || 6);
        $('#pagination_type').val(data.settings.pagination_type || 'pagination');
        $('#columns').val(data.settings.columns || '2');
        
        // Checkboxes
        $('#lazy_load').prop('checked', data.settings.lazy_load !== false);
        $('#show_search').prop('checked', data.settings.show_search !== false);
        $('#show_count').prop('checked', data.settings.show_count !== false);
        $('#show_excerpt').prop('checked', data.settings.show_excerpt === true);
        $('#show_read_more').prop('checked', data.settings.show_read_more === true);
        $('#show_meta').prop('checked', data.settings.show_meta === true);
        $('#show_categories').prop('checked', data.settings.show_categories === true);
        $('#show_price').prop('checked', data.settings.show_price !== false);
        $('#show_add_to_cart').prop('checked', data.settings.show_add_to_cart !== false);
        $('#hide_out_of_stock').prop('checked', data.settings.hide_out_of_stock === true);
        
        // Categories - select in both dropdowns (only one will be visible)
        if (data.settings.selected_categories) {
            $('#post_categories option, #product_categories option').prop('selected', false);
            data.settings.selected_categories.forEach(function(catId) {
                $(`#post_categories option[value="${catId}"], #product_categories option[value="${catId}"]`).prop('selected', true);
            });
        }
        
        // Styling
        $('#title_font_size').val(data.settings.title_font_size || 20);
        $('#title_color').val(data.settings.title_color || '#333333');
        $('#title_hover_color').val(data.settings.title_hover_color || '#2271b1');
        $('#price_color').val(data.settings.price_color || '#333333');
        $('#sale_price_color').val(data.settings.sale_price_color || '#ff0000');
        $('#button_color').val(data.settings.button_color || '#2271b1');
        $('#button_text_color').val(data.settings.button_text_color || '#ffffff');
        $('#button_hover_color').val(data.settings.button_hover_color || '#135e96');
        $('#add_to_cart_bg_color').val(data.settings.add_to_cart_bg_color || '#2271b1');
        $('#add_to_cart_text_color').val(data.settings.add_to_cart_text_color || '#ffffff');
        $('#add_to_cart_hover_color').val(data.settings.add_to_cart_hover_color || '#135e96');
        
        // Button text
        $('#load_more_text').val(data.settings.load_more_text || 'Load More');
        $('#read_more_text').val(data.settings.read_more_text || 'Read More');
        $('#add_to_cart_text').val(data.settings.add_to_cart_text || 'Add to Cart');
        
        // Custom CSS
        $('#custom_css').val(data.settings.custom_css || '');
    }
    
    // Close Modal
    $('.close-modal').on('click', function(e) {
        e.preventDefault();
        closeModal();
    });
    
    $('.preset-modal').on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    $('.preset-modal-content').on('click', function(e) {
        e.stopPropagation();
    });
    
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#preset-modal').is(':visible')) {
            closeModal();
        }
    });
    
    function closeModal() {
        $('#preset-modal').fadeOut(200);
        $('body').removeClass('modal-open');
        $('#preset-form')[0].reset();
    }
    
    // Copy Shortcode
    $('.copy-shortcode-btn').on('click', function(e) {
        e.preventDefault();
        const shortcode = $(this).data('shortcode');
        const $button = $(this);
        
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(shortcode).select();
        
        try {
            document.execCommand('copy');
            $temp.remove();
            
            const originalHtml = $button.html();
            $button.html('<span class="dashicons dashicons-yes"></span> Copied!');
            $button.addClass('button-primary');
            
            setTimeout(function() {
                $button.html(originalHtml);
                $button.removeClass('button-primary');
            }, 2000);
        } catch (err) {
            $temp.remove();
            alert('Failed to copy');
        }
    });
    
    // Form Validation
    $('#preset-form').on('submit', function(e) {
        const presetName = $('#preset_name').val().trim();
        
        if (presetName === '') {
            e.preventDefault();
            alert('Please enter a preset name');
            $('#preset_name').focus();
            return false;
        }
    });
    
    // Auto-generate slug
    $('#preset_name').on('input', function() {
        if ($('#preset_slug').val() === '') {
            const slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            $('#preset_slug').val(slug);
        }
    });
    
    // Initialize - trigger change event on page load to set correct visibility
    $('#preset_type').trigger('change');
});
