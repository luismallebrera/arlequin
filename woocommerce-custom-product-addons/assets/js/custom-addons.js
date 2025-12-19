/**
 * WooCommerce Custom Product Add-ons - Frontend JavaScript
 * Handles dynamic price calculation for custom add-ons
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Check if we have the required data
    if (typeof wcCustomAddons === 'undefined') {
        return;
    }
    
    var $addonInputs = $('.wc-custom-addon-qty');
    var $totalDisplay = $('.wc-addons-total-price');
    var $quantityInput = $('input.qty');
    var $priceDisplay = $('.woocommerce-Price-amount.amount').first();
    var $variationForm = $('.variations_form');
    
    var originalPrice = 0;
    var currentVariationPrice = 0;
    
    /**
     * Format price according to WooCommerce settings
     */
    function formatPrice(price) {
        var formattedPrice = price.toFixed(wcCustomAddons.price_decimals);
        
        // Replace separators
        formattedPrice = formattedPrice.replace('.', wcCustomAddons.price_decimal_separator);
        
        // Add thousand separator
        var parts = formattedPrice.split(wcCustomAddons.price_decimal_separator);
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, wcCustomAddons.price_thousand_separator);
        formattedPrice = parts.join(wcCustomAddons.price_decimal_separator);
        
        // Add currency symbol based on position
        switch (wcCustomAddons.currency_position) {
            case 'left':
                return wcCustomAddons.currency_symbol + formattedPrice;
            case 'right':
                return formattedPrice + wcCustomAddons.currency_symbol;
            case 'left_space':
                return wcCustomAddons.currency_symbol + ' ' + formattedPrice;
            case 'right_space':
                return formattedPrice + ' ' + wcCustomAddons.currency_symbol;
            default:
                return wcCustomAddons.currency_symbol + formattedPrice;
        }
    }
    
    /**
     * Get the current product base price
     */
    function getBasePrice() {
        var price = 0;
        
        // For variable products, try to get the variation price
        if ($variationForm.length > 0) {
            var variation = $variationForm.data('variation');
            if (variation && variation.display_price) {
                price = parseFloat(variation.display_price);
            }
        }
        
        // If no variation price or simple product, get from price display
        if (price === 0 && $priceDisplay.length > 0) {
            var priceText = $priceDisplay.text().replace(/[^\d.,]/g, '');
            // Handle different decimal separators
            priceText = priceText.replace(wcCustomAddons.price_thousand_separator, '');
            priceText = priceText.replace(wcCustomAddons.price_decimal_separator, '.');
            price = parseFloat(priceText);
        }
        
        return isNaN(price) ? 0 : price;
    }
    
    /**
     * Calculate total addon cost
     */
    function calculateAddonTotal() {
        var total = 0;
        
        $addonInputs.each(function() {
            var $input = $(this);
            var quantity = parseInt($input.val()) || 0;
            var price = parseFloat($input.data('price')) || 0;
            total += quantity * price;
        });
        
        return total;
    }
    
    /**
     * Update the addon total display
     */
    function updateAddonTotal() {
        var addonTotal = calculateAddonTotal();
        $totalDisplay.html(formatPrice(addonTotal));
    }
    
    /**
     * Update the main product price display
     */
    function updateProductPrice() {
        var basePrice = getBasePrice();
        var addonTotal = calculateAddonTotal();
        var productQuantity = parseInt($quantityInput.val()) || 1;
        
        // Calculate total with addons
        var totalPrice = (basePrice + addonTotal) * productQuantity;
        
        // Update price display
        if ($priceDisplay.length > 0) {
            var newPriceHTML = '<span class="woocommerce-Price-amount amount">' + formatPrice(totalPrice) + '</span>';
            
            // For variable products, update the variation price
            if ($variationForm.length > 0 && $('.single_variation_wrap .woocommerce-variation-price').length > 0) {
                $('.single_variation_wrap .woocommerce-variation-price .price').html(newPriceHTML);
            } else {
                // For simple products
                $('p.price').html(newPriceHTML);
            }
        }
    }
    
    /**
     * Update all prices
     */
    function updateAllPrices() {
        updateAddonTotal();
        updateProductPrice();
    }
    
    /**
     * Initialize the plugin
     */
    function init() {
        // Store original price on page load
        originalPrice = getBasePrice();
        
        // Update prices on addon quantity change
        $addonInputs.on('change input', function() {
            updateAllPrices();
        });
        
        // Update prices on main product quantity change
        $quantityInput.on('change input', function() {
            updateAllPrices();
        });
        
        // Handle variation changes for variable products
        if ($variationForm.length > 0) {
            $variationForm.on('found_variation', function(event, variation) {
                currentVariationPrice = parseFloat(variation.display_price);
                setTimeout(updateAllPrices, 100);
            });
            
            $variationForm.on('reset_data', function() {
                currentVariationPrice = 0;
                setTimeout(updateAllPrices, 100);
            });
        }
        
        // Initial update
        setTimeout(updateAllPrices, 500);
    }
    
    // Initialize on DOM ready
    init();
    
    // Re-initialize on AJAX complete (for AJAX add to cart)
    $(document).ajaxComplete(function() {
        setTimeout(init, 500);
    });
});
