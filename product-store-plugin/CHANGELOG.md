# Changelog

All notable changes to the Product Store Plugin will be documented in this file.

## [1.0.2] - 2026-02-04

### Added
- Length field for Panel products in cart (3'0" to 45'0" with quarter-inch increments)
- Support for custom lengths like 15'5.5" or 5'3.25" for panel items
- Quarter-inch measurement increments (0", 0.25", 0.5", 0.75")

### Changed
- Moved cart button from bottom-left to top-right
- Updated cart button styling to gray/white theme matching site design
- Removed product tags from store display (now shows only name, picture, and add to cart)
- Cleaned product titles by removing leading numbers and symbols
- Improved cart layout for panel items with quantity and length fields

### Removed
- Product tags no longer display under items in the store grid

## [1.0.1] - 2026-02-04

### Fixed
- Fixed admin menu not appearing in WordPress dashboard after plugin activation
- Admin classes now initialize earlier to properly register menu items
- Added menu position to make "Steel Store" menu more visible in dashboard
- Added "All Products" label to improve menu clarity

### Changed
- Plugin now properly displays "Steel Store" menu item with submenu options
- Menu appears below Comments in the WordPress admin sidebar
- Settings submenu now has clearer "Store Settings" page title

## [1.0.0] - 2024

### Added
- Initial release of Steel Store plugin
- Custom post type for steel products
- Product management interface in WordPress admin
- Tag-based product categorization (Panels, Trim, Accessories, Fasteners, Building Materials, Tools)
- Modern, minimal frontend design with Montserrat font
- Product grid with hover effects (3% scale, subtle glow)
- Shopping cart functionality
- Add/remove products from cart
- Adjust product quantities in cart
- Search functionality for products
- Category filtering tabs
- Cart modal with print support
- Order submission form
- Email notifications for orders sent to admin
- Responsive design for mobile, tablet, and desktop
- WordPress media library integration for product images
- Support for .webp and all standard image formats
- Elementor-compatible shortcode `[steel_store]`
- Settings page for email configuration
- LocalStorage-based cart persistence
- Print-friendly cart view
- AJAX-powered cart submission
- Built-in placeholder image for products without photos
- Comprehensive documentation (README, INSTALLATION guide)

### Features
- No pricing required (designed for quotation-based sales)
- Clean, professional design with light theme
- Smooth animations and transitions
- Easy product management
- Tag system for organization
- Customer-facing store interface
- Admin settings panel
- Security nonces for AJAX requests
- Sanitized inputs for security

### Technical Details
- WordPress 5.0+ compatible
- PHP 7.2+ required
- jQuery-based frontend
- CSS3 animations
- HTML5 semantic markup
- Follows WordPress coding standards
- GPL-2.0+ licensed

---

## Future Roadmap

### Planned Features
- [ ] Pricing support with price fields
- [ ] Payment gateway integration
- [ ] Order management dashboard
- [ ] Customer accounts
- [ ] Order history
- [ ] Export orders to CSV
- [ ] Product variants (sizes, colors, etc.)
- [ ] Product stock management
- [ ] Email templates customization
- [ ] Multi-currency support
- [ ] Tax calculation
- [ ] Shipping options
- [ ] Discount codes/coupons
- [ ] Product reviews
- [ ] Related products
- [ ] Product quick view
- [ ] Wishlist functionality
- [ ] Compare products
- [ ] Advanced search filters
- [ ] Product import/export

---

**Created by Bright Idea Marketing**  
Developer: Dylan Wood
