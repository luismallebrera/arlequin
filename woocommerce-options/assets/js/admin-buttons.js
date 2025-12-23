/**
 * Admin Scripts for Custom Product Buttons
 * WooCommerce Custom Options
 */

(function($) {
    'use strict';

    let buttonIndex = 0;

    $(document).ready(function() {
        // Initialize existing product selects
        initProductSelects();

        // Set initial button index based on existing buttons
        buttonIndex = $('.custom-button-row').length;

        // Add button handler
        $('#add-custom-button').on('click', function(e) {
            e.preventDefault();
            addButtonRow();
        });

        // Remove button handler (delegated)
        $(document).on('click', '.remove-custom-button', function(e) {
            e.preventDefault();
            $(this).closest('.custom-button-row').fadeOut(300, function() {
                $(this).remove();
                updateNoButtonsMessage();
            });
        });

        // Update no buttons message on load
        updateNoButtonsMessage();
    });

    /**
     * Initialize WooCommerce product search selects
     */
    function initProductSelects() {
        $('.wc-product-search').each(function() {
            initSingleProductSelect($(this));
        });
    }

    /**
     * Initialize a single product search select
     */
    function initSingleProductSelect($select) {
        $select.selectWoo({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term,
                        action: 'woocommerce_json_search_products',
                        security: woocommerce_admin_meta_boxes.search_products_nonce,
                        exclude: [$select.data('exclude')],
                        include: [$select.data('include')],
                        limit: $select.data('limit')
                    };
                },
                processResults: function(data) {
                    var terms = [];
                    if (data) {
                        $.each(data, function(id, text) {
                            terms.push({
                                id: id,
                                text: text
                            });
                        });
                    }
                    return {
                        results: terms
                    };
                },
                cache: true
            },
            minimumInputLength: 3,
            placeholder: wcOptionsAdmin.placeholder,
            allowClear: true
        });
    }

    /**
     * Add new button row
     */
    function addButtonRow() {
        const $container = $('#custom-buttons-container');
        
        const buttonHtml = `
            <div class="custom-button-row" style="display: none;">
                <div>
                    <label>
                        Producto de destino:
                    </label>
                    <select name="_custom_product_buttons[${buttonIndex}][product_id]" class="wc-product-search" data-placeholder="${wcOptionsAdmin.placeholder}" data-allow_clear="true">
                    </select>
                </div>
                <div>
                    <label>
                        Texto del botón:
                    </label>
                    <input type="text" name="_custom_product_buttons[${buttonIndex}][button_text]" value="" placeholder="Ver producto">
                </div>
                <button type="button" class="button remove-custom-button">
                    Eliminar
                </button>
            </div>
        `;
        
        const $newRow = $(buttonHtml);
        $container.append($newRow);
        
        // Initialize the product select for the new row
        initSingleProductSelect($newRow.find('.wc-product-search'));
        
        // Fade in the new row
        $newRow.fadeIn(300);
        
        buttonIndex++;
        updateNoButtonsMessage();
    }

    /**
     * Update the "no buttons" message visibility
     */
    function updateNoButtonsMessage() {
        const $message = $('.no-buttons-message');
        const hasButtons = $('.custom-button-row').length > 0;
        
        if (hasButtons) {
            $message.hide();
        } else {
            if ($message.length === 0) {
                $('#custom-buttons-container').append(
                    '<p class="no-buttons-message" style="color: #999; font-style: italic;">No hay botones configurados. Haz clic en "Añadir Botón" para crear uno.</p>'
                );
            } else {
                $message.show();
            }
        }
    }

})(jQuery);
