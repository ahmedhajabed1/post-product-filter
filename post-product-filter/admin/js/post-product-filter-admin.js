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
            
            // For categories - hide post, show product
            $('#post_categories').closest('.form-group').hide();
            $('#product_categories').closest('.form-group').show();
        } else {
            // Show post fields, hide product fields
            $('.field-posts-only').show();
            $('.field-products-only').hide();
            
            // For categories - show post, hide product
            $('#post_categories').closest('.form-group').show();
            $('#product_categories').closest('.form-group').hide();
        }
        
        // Update styling visibility based on enabled features
        updateStylingVisibility();
    });
    
    // Update styling visibility when checkboxes change
    $('#show_read_more, #show_add_to_cart, #show_price').on('change', function() {
        updateStylingVisibility();
    });
    
    // Function to show/hide styling sections based on enabled features
    function updateStylingVisibility() {
        const presetType = $('#preset_type').val();
        
        if (presetType === 'products') {
            // For products, show/hide based on enabled features
            const showPrice = $('#show_price').is(':checked');
            const showAddToCart = $('#show_add_to_cart').is(':checked');
            
            // Price styling
            if (showPrice) {
                $('#price-styling-section').show();
            } else {
                $('#price-styling-section').hide();
            }
            
            // Add to Cart styling
            if (showAddToCart) {
                $('#add-to-cart-styling-section').show();
            } else {
                $('#add-to-cart-styling-section').hide();
            }
            
            // Hide post-specific styling
            $('#read-more-styling-section').hide();
        } else {
            // For posts, show/hide based on enabled features
            const showReadMore = $('#show_read_more').is(':checked');
            
            // Read More styling
            if (showReadMore) {
                $('#read-more-styling-section').show();
            } else {
                $('#read-more-styling-section').hide();
            }
            
            // Hide product-specific styling
            $('#price-styling-section').hide();
            $('#add-to-cart-styling-section').hide();
        }
    }
    
    // Open Add Preset Modal
    $('#add-preset-btn, #add-first-preset-btn').on('click', function() {
        $('#modal-title').text('Add New Preset');
        $('#preset-form')[0].reset();
        $('#preset_slug').val('');
        $('#preset_type').val('posts').trigger('change');
        
        // Set default checkboxes
        $('#lazy_load').prop('checked', true);
        $('#show_search').prop('checked', true);
        $('#show_count').prop('checked', true);
        $('#show_price').prop('checked', true);
        $('#show_add_to_cart').prop('checked', true);
        
        // Reset to first tab
        $('.preset-tab-btn').removeClass('active').first().addClass('active');
        $('.preset-tab-content').removeClass('active').first().addClass('active');
        
        $('#preset-modal').fadeIn(200);
        $('body').addClass('modal-open');
        
        // Update styling visibility
        updateStylingVisibility();
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
        $('#preset_type').val(data.settings.preset_type || 'posts');
        $('#posts_per_page').val(data.settings.posts_per_page || 6);
        $('#pagination_type').val(data.settings.pagination_type || 'pagination');
        $('#columns').val(data.settings.columns || '2');
        
        // Trigger change to show correct fields BEFORE populating categories
        $('#preset_type').trigger('change');
        
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
        
        // Categories - populate the correct select based on type
        if (data.settings.selected_categories) {
            // Clear both selects first
            $('#post_categories option, #product_categories option').prop('selected', false);
            
            // Populate based on type
            const presetType = data.settings.preset_type || 'posts';
            if (presetType === 'products') {
                data.settings.selected_categories.forEach(function(catId) {
                    $(`#product_categories option[value="${catId}"]`).prop('selected', true);
                });
            } else {
                data.settings.selected_categories.forEach(function(catId) {
                    $(`#post_categories option[value="${catId}"]`).prop('selected', true);
                });
            }
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
        
        // Update styling visibility after populating
        updateStylingVisibility();
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
    updateStylingVisibility();
});
