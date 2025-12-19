# Changelog

All notable changes to the WooCommerce Custom Product Add-ons plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2024-12-19

### Added
- Per-product enable/disable control for add-ons
- Admin checkbox in Product Data → General tab to enable add-ons
- Add-ons are now disabled by default for all products

### Changed
- Add-ons must be explicitly enabled per product (default: disabled)
- Improved label visibility with darker color (#333 instead of #555)
- Added margin-bottom to labels for better spacing

### Fixed
- Enhanced label styling for better visibility

## [1.0.0] - 2024-12-19

### Added
- Initial release of WooCommerce Custom Product Add-ons plugin
- Three pre-configured add-on fields:
  - AÑADIR PULSERAS (Add Bracelets) - €0.30 per unit
  - AÑADIR ETIQUETAS (Add Labels/Tags) - €0.50 per unit
  - AÑADIR RECORDATORIO (Add Reminder) - €1.30 per unit
- Dynamic price calculation using JavaScript
- Real-time price updates on product pages
- Support for simple and variable WooCommerce products
- Cart integration with add-on data persistence
- Formatted display of add-ons in cart (quantity × price = total)
- Checkout integration with add-on visibility
- Order meta data storage for add-ons
- Display of add-ons in admin order details
- Add-on information in order confirmation emails
- Responsive design for mobile devices
- Nonce verification for security
- Input sanitization and validation
- Translation-ready structure with text domain
- Professional styling with WooCommerce theme compatibility
- Dark mode support in CSS
- WordPress coding standards compliance
- Comprehensive documentation (README.md, INSTALLATION.md)
- Uninstall script for clean removal
- Browser compatibility (Chrome, Firefox, Safari, Edge)
- No database modifications (uses WooCommerce's existing structure)

### Technical Details
- WordPress hooks properly implemented
- WooCommerce filters and actions used correctly
- Modular code structure with single-class architecture
- Proper enqueueing of scripts and styles
- WooCommerce price formatting functions utilized
- jQuery for JavaScript compatibility
- Clean separation of concerns (PHP, JS, CSS)

### Security
- Nonce verification on form submission
- Input sanitization using WordPress functions (absint, sanitize_text_field)
- Output escaping (esc_html, esc_attr, esc_url)
- No direct file access protection
- No SQL injection vulnerabilities (uses WooCommerce APIs)
- XSS prevention with proper escaping

### Known Limitations
- Add-on prices are hardcoded (no admin interface for configuration)
- Add-ons apply globally to all products (no per-product configuration)
- Labels are in Spanish (translation files not yet included)
- Maximum quantity set to 100 (can be increased if needed)

### Future Enhancements
Planned for future versions:
- Admin settings page for configuring add-ons
- Per-product or per-category add-on rules
- Custom add-on creation interface
- Image support for add-ons
- Conditional logic for add-on display
- Import/export of add-on configurations
- Analytics and reporting for add-on sales
- Multi-language .po/.mo files

---

## Version History

- **1.0.0** - Initial release (December 19, 2024)
