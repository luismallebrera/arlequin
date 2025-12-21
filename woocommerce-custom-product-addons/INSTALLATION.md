# Installation and Testing Guide

This guide explains how to install and test the WooCommerce Custom Product Add-ons plugin.

## Installation Steps

### 1. Prerequisites
Before installing the plugin, ensure you have:
- WordPress 5.8 or higher installed
- WooCommerce 5.0 or higher installed and activated
- PHP 7.4 or higher
- A working WordPress/WooCommerce site (local or production)

### 2. Install the Plugin

**Option A: Manual Upload (WordPress Admin)**
1. Compress the `woocommerce-custom-product-addons` folder into a ZIP file
2. In WordPress admin, go to Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Click "Activate Plugin"

**Option B: Direct Server Upload**
1. Upload the entire `woocommerce-custom-product-addons` folder to `/wp-content/plugins/`
2. In WordPress admin, go to Plugins
3. Find "WooCommerce Custom Product Add-ons" and click "Activate"

**Option C: Git Clone (for development)**
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/luismallebrera/arlequin.git
cp -r arlequin/woocommerce-custom-product-addons .
```

### 3. Verify Installation
After activation, you should see:
- No error messages
- The plugin listed as "Active" in the Plugins page

## Testing the Plugin

### Frontend Testing (Customer View)

#### Test 1: Product Page Display
1. Navigate to any WooCommerce product page (simple or variable product)
2. **Expected Result:**
   - You should see a section titled "Personaliza tu pedido"
   - Three add-on fields should be visible:
     - AÑADIR PULSERAS (€0.30 por unidad)
     - AÑADIR ETIQUETAS (€0.50 por unidad)
     - AÑADIR RECORDATORIO (€1.30 por unidad)
   - Each field should have a number input starting at 0
   - A "Coste adicional" section should show €0.00

#### Test 2: Dynamic Price Calculation
1. On a product page, change the quantity of "AÑADIR PULSERAS" to 2
2. **Expected Result:**
   - "Coste adicional" should update to €0.60
   - The main product price should increase by €0.60

3. Change "AÑADIR ETIQUETAS" to 3
4. **Expected Result:**
   - "Coste adicional" should update to €2.10 (0.60 + 1.50)
   - The main product price should reflect the total

5. Change "AÑADIR RECORDATORIO" to 1
6. **Expected Result:**
   - "Coste adicional" should update to €3.40 (0.60 + 1.50 + 1.30)

7. Change the main product quantity to 2
8. **Expected Result:**
   - The total price should double to reflect 2 products + addons

#### Test 3: Cart Integration
1. Set add-on quantities:
   - AÑADIR PULSERAS: 2
   - AÑADIR ETIQUETAS: 1
   - AÑADIR RECORDATORIO: 0
2. Click "Add to Cart"
3. Go to the Cart page
4. **Expected Result:**
   - The product should be in the cart
   - Under the product name, you should see:
     - "Pulseras: 2 × €0.30 = €0.60"
     - "Etiquetas: 1 × €0.50 = €0.50"
   - The line total should include the addon prices
   - Cart total should be correct

#### Test 4: Multiple Products with Different Add-ons
1. Add a product with certain add-ons
2. Go back and add the same product with different add-ons
3. **Expected Result:**
   - Both should appear as separate cart items
   - Each should have its own add-on configuration

#### Test 5: Checkout Process
1. Proceed to checkout with items that have add-ons
2. **Expected Result:**
   - Add-ons should be visible in the order review section
   - Order total should include add-on costs

3. Complete the checkout process
4. **Expected Result:**
   - Order confirmation page shows add-ons
   - Email confirmation includes add-on details

### Backend Testing (Admin View)

#### Test 6: Order Details
1. In WordPress admin, go to WooCommerce → Orders
2. Open an order that includes add-ons
3. **Expected Result:**
   - Add-ons are visible in the order items
   - Each add-on shows quantity, unit price, and total
   - Order total is correct

#### Test 7: Email Notifications
1. Check the order confirmation email
2. **Expected Result:**
   - Add-ons are listed in the email
   - Formatted properly with quantity and price

### Edge Cases and Error Testing

#### Test 8: Zero Quantities
1. Add a product to cart with all add-ons set to 0
2. **Expected Result:**
   - Product is added normally
   - No add-on information is saved or displayed
   - Price is standard product price

#### Test 9: Maximum Quantities
1. Set an add-on quantity to 100
2. **Expected Result:**
   - Calculation works correctly
   - No errors or performance issues
   - Cart and checkout work normally

#### Test 10: Variable Products
1. Select a product variation
2. Change add-on quantities
3. **Expected Result:**
   - Price updates correctly based on variation price + add-ons
   - Changing variation updates the total price
   - Add-ons work the same as simple products

#### Test 11: Browser Compatibility
Test on:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

**Expected Result:**
- All functionality works on all browsers
- Responsive design works on mobile devices
- Price calculations are accurate everywhere

### Performance Testing

#### Test 12: JavaScript Console
1. Open browser developer tools (F12)
2. Navigate to a product page
3. **Expected Result:**
   - No JavaScript errors in the console
   - No 404 errors for CSS or JS files

#### Test 13: Page Load Time
1. Check page load time with browser tools
2. **Expected Result:**
   - Plugin adds minimal overhead
   - JavaScript loads efficiently
   - CSS is optimized

## Troubleshooting

### Problem: Add-ons not showing
**Solution:**
- Verify WooCommerce is active
- Check that the product type is simple or variable
- Clear browser cache
- Check WordPress debug log for errors

### Problem: Prices not updating
**Solution:**
- Check browser console for JavaScript errors
- Verify jQuery is loaded
- Ensure no JavaScript conflicts with other plugins
- Disable other plugins to isolate the issue

### Problem: Add-ons not in cart
**Solution:**
- Ensure quantities are > 0
- Check that form is submitting correctly
- Verify nonce is valid
- Check server error logs

### Problem: Styling issues
**Solution:**
- Clear browser cache
- Check if CSS file is loading (Network tab in dev tools)
- Verify there are no CSS conflicts
- Check theme compatibility

## Success Criteria

The plugin is working correctly if:

✅ Add-on fields appear on all product pages
✅ Dynamic price calculation works in real-time
✅ Add-ons are saved when adding to cart
✅ Add-ons display correctly in cart with prices
✅ Cart totals are accurate
✅ Add-ons appear in checkout
✅ Add-ons are saved to orders
✅ Add-ons appear in admin order details
✅ Add-ons appear in email notifications
✅ No JavaScript errors
✅ No PHP errors
✅ Responsive design works on mobile
✅ Works with both simple and variable products

## Next Steps

After successful testing:
1. Monitor for any user-reported issues
2. Consider adding admin settings page
3. Add translation files for other languages
4. Gather user feedback for improvements
5. Document any custom modifications needed for specific themes

## Support

For issues or questions:
- Check the main README.md file
- Review WordPress and server error logs
- Create an issue on GitHub
- Check WooCommerce documentation for hooks and filters
