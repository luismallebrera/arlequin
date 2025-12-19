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

### Step 3: Verify It's Working
1. Visit any WooCommerce product page
2. You should see "Personaliza tu pedido" section with three add-on fields
3. ✅ Done! The plugin is active and working

## 🎯 What You Get

The plugin automatically adds these add-ons to ALL product pages:

| Add-on | Spanish Label | Price |
|--------|---------------|-------|
| Bracelets | AÑADIR PULSERAS | €0.30/unit |
| Labels/Tags | AÑADIR ETIQUETAS | €0.50/unit |
| Reminder | AÑADIR RECORDATORIO | €1.30/unit |

## 🚀 How Customers Use It

1. **Product Page**: Customer selects add-on quantities (0-100)
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

1. Go to any product page
2. Set "AÑADIR PULSERAS" to 2 → Should show €0.60 additional cost
3. Set "AÑADIR ETIQUETAS" to 1 → Should show €1.10 additional cost (0.60 + 0.50)
4. Click "Add to Cart"
5. Go to cart → Should see both add-ons listed with prices
6. ✅ If all works, you're all set!

## 📝 Need More Details?

- **Full Documentation**: See [README.md](README.md)
- **Installation Guide**: See [INSTALLATION.md](INSTALLATION.md)
- **Version History**: See [CHANGELOG.md](CHANGELOG.md)

## ❓ Troubleshooting

**Add-ons not showing?**
- Make sure WooCommerce is installed and active
- Check that you're viewing a Simple or Variable product
- Clear browser cache

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
