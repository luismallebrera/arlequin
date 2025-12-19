# Changelog

All notable changes to the WooCommerce Custom Product Add-ons plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.2] - 2024-12-19

### Changed
- **Text Input Styling**: Updated custom text field input styling
  - Reduced field margin from 15px to 7px for more compact layout
  - Adjusted padding from 10px to 7px for slimmer inputs
  - Increased font size from 14px to 17px for better readability
  - Changed border color from #ddd to #e1e1e1 for lighter appearance
  - Added margin-bottom: 0 to inputs to prevent extra spacing
  - Applied !important flags to ensure styling overrides theme defaults

## [1.3.1] - 2024-12-19

### Fixed
- **Required Field Validation**: Implemented both client-side (JavaScript) and server-side (PHP) validation
  - JavaScript validation prevents form submission if required fields are empty
  - Alert message shows list of missing required fields
  - Server-side validation displays WooCommerce error notice if validation fails
  - Fields highlight in pink when empty on validation attempt
  - Prevents adding to cart without filling all required text fields (except Observaciones)

## [1.3.0] - 2024-12-19

### Changed
- **Section Order**: "Información adicional" now appears BEFORE "Personaliza tu pedido"
- **Required Fields**: All text fields are now required except "Observaciones"
- **Styling Update**: Complete CSS redesign with custom brand colors (#c34591)
  - Removed dark theme support
  - Updated to use brand pink color (#c34591) for borders and accents
  - Changed font weights to 800 for bold headings
  - Updated background to white (#fff)
  - Border radius set to 0 for sharper corners
  - Required field indicator (*) in brand pink color

### Fixed
- Text field validation now enforced on frontend
- Consistent styling across all form elements

## [1.2.0] - 2024-12-19

### Added
- 11 custom text input fields for event information:
  - NOMBRE DE LA NIÑA/NIÑO (Child's name)
  - NOMBRE DEL BEBÉ (Baby's name)
  - FECHA DEL BAUTIZO (Baptism date)
  - FECHA DE LA COMUNIÓN (Communion date)
  - NOMBRE DE LA IGLESIA (Church name)
  - LOCALIDAD (Location/Town)
  - HORA DE LA MISA (Mass time)
  - NOMBRE RESTAURANTE/LUGAR DE LA CELEBRACIÓN (Restaurant/Celebration venue)
  - NOMBRE DEL O LA PROFE (Teacher's name)
  - AÑO CURSO (School year)
  - OBSERVACIONES (Observations/Notes)
- Individual enable/disable control for each text field in admin
- Text fields are disabled by default per product
- Text field values saved to cart and orders
- Professional styling for text input fields

### Changed
- Admin interface now includes "Custom Text Fields" section
- Product page displays text fields when enabled
- Cart and order details include custom text field values

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
