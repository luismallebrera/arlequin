# WooCommerce Custom Product Add-ons

A WordPress/WooCommerce plugin that adds custom quantity-based add-on fields and custom text fields to product pages with dynamic price calculation and per-product control.

## Description

This plugin enhances WooCommerce product pages by adding customizable add-on fields with real-time price calculation, plus custom text input fields for event information. Perfect for businesses that want to offer additional products or services alongside their main products, or collect custom information for events like baptisms, communions, and celebrations.

**Per-Product Control**: Both add-ons and text fields can be enabled or disabled individually on a per-product basis, giving you full control over which products offer these options.

## Features

### Quantity-Based Add-on Fields
Three pre-configured add-on options that can be enabled per product:

1. **AÑADIR PULSERAS** (Add Bracelets) - €0.30 per unit
2. **AÑADIR ETIQUETAS** (Add Labels/Tags) - €0.50 per unit
3. **AÑADIR RECORDATORIO** (Add Reminder) - €1.30 per unit

### Custom Text Fields
11 custom text input fields for event information (each can be individually enabled/disabled):

1. **NOMBRE DE LA NIÑA/NIÑO** (Child's name)
2. **NOMBRE DEL BEBÉ** (Baby's name)
3. **FECHA DEL BAUTIZO** (Baptism date)
4. **FECHA DE LA COMUNIÓN** (Communion date)
5. **NOMBRE DE LA IGLESIA** (Church name)
6. **LOCALIDAD** (Location/Town)
7. **HORA DE LA MISA** (Mass time)
8. **NOMBRE RESTAURANTE/LUGAR DE LA CELEBRACIÓN** (Restaurant/Celebration venue)
9. **NOMBRE DEL O LA PROFE** (Teacher's name)
10. **AÑO CURSO** (School year)
11. **OBSERVACIONES** (Observations/Notes)

### Functionality

- **Per-Product Activation**: Enable or disable add-ons and text fields individually for each product (default: disabled)
- **Dynamic Price Calculation**: Real-time price updates for quantity-based add-ons
- **Custom Data Collection**: Collect customer-provided text information
- **Cart Integration**: Selected add-ons and text field values are saved and displayed in the cart
- **Order Details**: All data appears in order details, emails, and admin orders
- **Responsive Design**: Mobile-friendly interface
- **WooCommerce Compatible**: Works with simple and variable products
- **Translation Ready**: Fully translatable with .pot file support

## Installation

### Manual Installation

1. Download or clone this repository
2. Upload the `woocommerce-custom-product-addons` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure fields for specific products in the product edit page (see Usage for Administrators)

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 5.0 or higher

## Usage

### For Administrators

1. Edit a product in WooCommerce
2. In the Product Data section, under the General tab, find two sections:
   - **Enable Custom Add-ons**: Check to enable quantity-based add-ons (Pulseras, Etiquetas, Recordatorio)
   - **Custom Text Fields**: Check individual boxes to enable specific text fields for this product
3. Save/Update the product
4. The enabled fields will now appear on that product's page

**Note**: All fields are disabled by default. You must explicitly enable them per product.

### For Customers

1. Navigate to a product page where fields are enabled
2. Select quantities for add-ons (if enabled)
3. Fill in text fields (if enabled)
3. Watch the price update automatically
4. Add the product to cart
5. Review add-ons in cart, checkout, and order confirmation

### For Administrators

The plugin works automatically once activated. Add-on information appears in:

- Cart page (as item meta data)
- Checkout page
- Order details (admin and customer)
- Order confirmation emails

## File Structure

```
woocommerce-custom-product-addons/
├── woocommerce-custom-product-addons.php (Main plugin file)
├── assets/
│   ├── js/
│   │   └── custom-addons.js (Frontend JavaScript)
│   └── css/
│       └── custom-addons.css (Styles)
├── languages/ (Translation files - to be added)
└── README.md (This file)
```

## Customization

### Changing Prices

To modify the add-on prices, edit the `$addon_prices` array in the main plugin file:

```php
private static $addon_prices = array(
    'pulseras'     => 0.30,  // Change this value
    'etiquetas'    => 0.50,  // Change this value
    'recordatorio' => 1.30,  // Change this value
);
```

### Changing Labels

Labels are translation-ready. Use a translation plugin or edit the text in the `display_custom_fields()` method.

### Styling

Customize the appearance by editing `assets/css/custom-addons.css`.

### JavaScript Behavior

Modify the dynamic price calculation logic in `assets/js/custom-addons.js`.

## Technical Details

### WordPress Hooks Used

- `plugins_loaded` - Initialize plugin
- `wp_enqueue_scripts` - Load CSS and JavaScript
- `woocommerce_before_add_to_cart_button` - Display custom fields
- `woocommerce_add_cart_item_data` - Save add-on data to cart
- `woocommerce_get_item_data` - Display add-ons in cart
- `woocommerce_before_calculate_totals` - Calculate prices
- `woocommerce_checkout_create_order_line_item` - Save to order

### Security Features

- Nonce verification for form submissions
- Input sanitization using `absint()` for quantities
- Proper escaping of output with `esc_html()`, `esc_attr()`
- WooCommerce price formatting functions

### Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11 with graceful degradation
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Add-ons not showing on product page

1. Ensure WooCommerce is installed and activated
2. Check that the product type is 'simple' or 'variable'
3. Clear browser cache and WooCommerce cache

### Prices not updating

1. Check browser console for JavaScript errors
2. Ensure jQuery is loaded
3. Verify that the JavaScript file is enqueued correctly

### Add-ons not appearing in cart

1. Verify that quantities were selected before adding to cart
2. Check that nonce verification is passing
3. Review cart session data

## Development

### Code Standards

- Follows WordPress Coding Standards
- WooCommerce development best practices
- Proper documentation and comments
- Modular and maintainable code structure

### Future Enhancements

Potential improvements for future versions:

- Admin settings page to configure add-ons
- Support for custom add-ons per product
- Conditional display based on product categories
- Maximum quantity limits per add-on
- Image upload for add-ons
- Multi-language support files

## Support

For issues, questions, or contributions:

- GitHub: https://github.com/luismallebrera/arlequin
- Create an issue in the repository

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

**Author**: Luis Mallebrera
**Version**: 1.0.0
**Requires**: WordPress 5.8+, WooCommerce 5.0+

## Changelog

### 1.0.0 - 2024-12-19
* Initial release
* Add three custom add-on fields (Pulseras, Etiquetas, Recordatorio)
* Dynamic price calculation with JavaScript
* Full cart and checkout integration
* Responsive design
* Translation ready
