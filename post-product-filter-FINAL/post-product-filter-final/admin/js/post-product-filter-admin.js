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
        const $form = $('#preset-form');
        
        if (presetType === 'products') {
            $form.addClass('preset-type-products');
        } else {
            $form.removeClass('preset-type-products');
        }
    });
    
    // Open Add Preset Modal
    $('#add-preset-btn').on('click', function() {
        $('#modal-title').text('Add New Preset');
        $('#preset-form')[0].reset();
        $('#preset_slug').val('');
        $('#preset_type').val('posts').trigger('change');
        
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
    
    // Populate form with preset data - FIXED to include name
    function populatePresetForm(data) {
        if (!data.settings) return;
        
        // IMPORTANT: Set preset name
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
        
        // Categories
        if (data.settings.selected_categories) {
            $('#selected_categories option').prop('selected', false);
            data.settings.selected_categories.forEach(function(catId) {
                $(`#selected_categories option[value="${catId}"]`).prop('selected', true);
            });
        }
        
        // Product settings
        $('#form_title').val(data.settings.form_title || 'Filter by Categories');
        $('#empty_fields').prop('checked', data.settings.empty_fields !== false);
        $('#hide_product_sorting').prop('checked', data.settings.hide_product_sorting === true);
        $('#hide_product_count').prop('checked', data.settings.hide_product_count === true);
        $('#hide_out_of_stock').prop('checked', data.settings.hide_out_of_stock === true);
        $('#hide_pagination').prop('checked', data.settings.hide_pagination === true);
        $('#toggle_field_groups').prop('checked', data.settings.toggle_field_groups === true);
        
        $(`input[name="reset_button_position"][value="${data.settings.reset_button_position || 'none'}"]`).prop('checked', true);
        $('#reset_label').val(data.settings.reset_label || 'Reset');
        $('#scroll_to_result').prop('checked', data.settings.scroll_to_result === true);
        $(`input[name="taxonomy_relation"][value="${data.settings.taxonomy_relation || 'OR'}"]`).prop('checked', true);
        $(`input[name="result_page_template"][value="${data.settings.result_page_template || 'same'}"]`).prop('checked', true);
        $('#no_products_message').val(data.settings.no_products_message || 'No products were found matching your selection.');
        $('#show_variations').prop('checked', data.settings.show_variations === true);
        
        // Styling
        $('#title_font_size').val(data.settings.title_font_size || 20);
        $('#title_color').val(data.settings.title_color || '#333333');
        $('#title_hover_color').val(data.settings.title_hover_color || '#2271b1');
        $('#price_color').val(data.settings.price_color || '#333333');
        $('#sale_price_color').val(data.settings.sale_price_color || '#ff0000');
        $('#primary_color').val(data.settings.primary_color || '#2271b1');
        $('#button_color').val(data.settings.button_color || '#2271b1');
        $('#button_text_color').val(data.settings.button_text_color || '#ffffff');
        $('#button_hover_color').val(data.settings.button_hover_color || '#135e96');
        $('#add_to_cart_bg_color').val(data.settings.add_to_cart_bg_color || '#2271b1');
        $('#add_to_cart_text_color').val(data.settings.add_to_cart_text_color || '#ffffff');
        $('#add_to_cart_hover_color').val(data.settings.add_to_cart_hover_color || '#135e96');
        $('#button_style').val(data.settings.button_style || 'rounded');
        
        // Button text
        $('#load_more_text').val(data.settings.load_more_text || 'Load More');
        $('#loading_text').val(data.settings.loading_text || 'Loading...');
        $('#read_more_text').val(data.settings.read_more_text || 'Read More');
        $('#add_to_cart_text').val(data.settings.add_to_cart_text || 'Add to Cart');
        
        // Custom CSS
        $('#custom_css').val(data.settings.custom_css || '');
    }
    
    // Close Modal
    $('.close-modal, .preset-modal').on('click', function(e) {
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
    $('.copy-shortcode-btn').on('click', function() {
        const shortcode = $(this).data('shortcode');
        const button = $(this);
        const $row = button.closest('tr');
        const filterName = $row.find('.preset-name strong').text();
        const filterType = $row.find('.preset-type-badge').text();
        
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcode).select();
        document.execCommand('copy');
        tempInput.remove();
        
        const originalText = button.html();
        button.html('<span class="dashicons dashicons-yes"></span> Copied!');
        button.addClass('button-primary');
        
        // Show notification with filter info
        showNotice(`"${filterName}" (${filterType} Filter) shortcode copied to clipboard!`, 'success');
        
        setTimeout(function() {
            button.html(originalText);
            button.removeClass('button-primary');
        }, 2000);
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
        
        $(this).find('button[type="submit"]').addClass('loading').prop('disabled', true);
    });
    
    // Auto-generate slug from preset name
    $('#preset_name').on('input', function() {
        if ($('#preset_slug').val() === '') {
            const slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            $('#preset_slug').val(slug);
        }
    });
    
    // Category search
    if ($('#selected_categories').length) {
        const searchInput = $('<input>', {
            type: 'text',
            class: 'widefat',
            placeholder: 'Search categories...',
            css: { marginBottom: '10px' }
        });
        
        searchInput.insertBefore('#selected_categories');
        
        searchInput.on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('#selected_categories option').each(function() {
                const optionText = $(this).text().toLowerCase();
                $(this).toggle(optionText.includes(searchTerm));
            });
        });
    }
    
    // Confirm delete
    $('.delete-preset-btn').on('click', function(e) {
        const presetName = $(this).closest('tr').find('.preset-name strong').text();
        
        if (!confirm('Are you sure you want to delete the preset "' + presetName + '"?')) {
            e.preventDefault();
            return false;
        }
    });
    
    $('.presets-table-wrapper tbody tr').each(function(index) {
        $(this).css({ opacity: 0, transform: 'translateY(20px)' })
            .delay(index * 50).animate({ opacity: 1 }, 300, function() {
                $(this).css('transform', 'translateY(0)');
            });
    });
    
    $('.notice-success').hide().slideDown(300);
});

// Handle empty state button click
$('#add-first-preset-btn').on('click', function() {
    $('#add-preset-btn').trigger('click');
});

