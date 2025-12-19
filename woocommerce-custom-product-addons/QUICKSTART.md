# Quick Start Guide

Get the WooCommerce Custom Product Add-ons plugin up and running in 5 minutes!

## ⚡ Quick Installation (3 Steps)

### Step 1: Check Requirements
- ✅ WordPress 5.8+
- ✅ WooCommerce 5.0+
- ✅ PHP 7.4+

### Step 2: Install Plugin

**Fastest Method:**
1. Upload the `woocommerce-custom-product-addons` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Click "Activate" next to "WooCommerce Custom Product Add-ons"

**Alternative (ZIP Upload):**
1. Create a ZIP file of the `woocommerce-custom-product-addons` folder
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now" → "Activate"

### Step 3: Enable Add-ons for Products
1. Go to WordPress Admin → Products
2. Edit a product where you want add-ons
3. In Product Data section → General tab
4. Check "Enable Custom Add-ons"
5. Click "Update"

### Step 4: Verify It's Working
1. Visit the product page where you enabled add-ons
2. You should see "Personaliza tu pedido" section with three add-on fields
3. ✅ Done! The plugin is active and working

**Note**: Add-ons are **disabled by default** for all products. You must enable them per product.

## 🎯 What You Get

The plugin adds these add-ons to products where you enable them:

| Add-on | Spanish Label | Price |
|--------|---------------|-------|
| Bracelets | AÑADIR PULSERAS | €0.30/unit |
| Labels/Tags | AÑADIR ETIQUETAS | €0.50/unit |
| Reminder | AÑADIR RECORDATORIO | €1.30/unit |

## 🚀 How It Works

### For Administrators
1. **Product Edit Page**: Check "Enable Custom Add-ons" in Product Data → General
2. **Per-Product Control**: Enable only for products where you want add-ons
3. **Default**: Add-ons are disabled unless you enable them

### For Customers
1. **Product Page**: Customer selects add-on quantities (0-100) on enabled products
2. **Price Updates**: Total price updates automatically in real-time
3. **Add to Cart**: Add-ons are saved with the product
4. **Cart**: Add-ons display with details (e.g., "Pulseras: 2 × €0.30 = €0.60")
5. **Checkout**: Add-ons appear in order review
6. **Order**: Add-ons show in confirmation, emails, and admin

## 🎨 Customization (Optional)

### Change Prices
Edit `woocommerce-custom-product-addons.php` around line 37:

```php
private static $addon_prices = array(
    'pulseras'     => 0.50,  // Change from 0.30 to 0.50
    'etiquetas'    => 0.75,  // Change from 0.50 to 0.75
    'recordatorio' => 2.00,  // Change from 1.30 to 2.00
);
```

### Change Labels
Replace text in the `display_custom_fields()` method around line 145:

```php
<?php esc_html_e( 'AÑADIR PULSERAS', 'wc-custom-addons' ); ?>
```

Change to:

```php
<?php esc_html_e( 'YOUR NEW LABEL', 'wc-custom-addons' ); ?>
```

### Change Styling
Edit `assets/css/custom-addons.css` to match your theme's colors and style.

### Change Maximum Quantity
Edit the HTML inputs in `display_custom_fields()` around line 153:

```php
max="100"  // Change from 100 to your desired maximum
```

## 🧪 Quick Test

1. Edit a product and enable "Enable Custom Add-ons"
2. Go to that product's page
3. You should see the add-on fields with labels
4. Set "AÑADIR PULSERAS" to 2 → Should show €0.60 additional cost
5. Set "AÑADIR ETIQUETAS" to 1 → Should show €1.10 additional cost (0.60 + 0.50)
6. Click "Add to Cart"
7. Go to cart → Should see both add-ons listed with prices
8. ✅ If all works, you're all set!

## 📝 Need More Details?

- **Full Documentation**: See [README.md](README.md)
- **Installation Guide**: See [INSTALLATION.md](INSTALLATION.md)
- **Version History**: See [CHANGELOG.md](CHANGELOG.md)

## ❓ Troubleshooting

**Add-ons not showing?**
- Make sure you've enabled "Enable Custom Add-ons" in the product settings
- Check that WooCommerce is installed and active
- Verify you're viewing a Simple or Variable product
- Clear browser cache

**Labels not visible?**
- Check browser console (F12) for CSS errors
- Ensure the CSS file is loading properly
- Check for theme conflicts with label styling

**Prices not updating?**
- Check browser console (F12) for JavaScript errors
- Make sure jQuery is loaded
- Try disabling other plugins to check for conflicts

**Still having issues?**
- Check WordPress debug log
- Review server error logs
- Create an issue on GitHub with details

## 🎉 You're Done!

The plugin is now active and will automatically add custom add-ons to all your WooCommerce products. Customers can now personalize their orders with bracelets, labels, and reminders!

---

**Need Help?** Create an issue at: https://github.com/luismallebrera/arlequin/issues
