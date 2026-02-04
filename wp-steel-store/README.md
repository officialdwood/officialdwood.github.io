# Steel Store - WordPress Plugin

A modern, minimal WordPress plugin for displaying and managing steel products with cart functionality and email-based order submission.

## Features

- **Modern Design**: Sleek, minimal interface with Montserrat font
- **Product Management**: Easy-to-use admin interface for adding, editing, and removing products
- **Tag System**: Organize products by categories (Panels, Trim, Accessories, Fasteners, Building Materials, Tools)
- **Search Functionality**: Customers can search products by name or tag
- **Shopping Cart**: Add products to cart, adjust quantities, view, and submit orders
- **Email Submission**: Orders are emailed to specified admin address
- **Print Support**: Print cart contents for offline reference
- **Elementor Compatible**: Use shortcode `[steel_store]` in any Elementor widget
- **Image Support**: Upload .webp or any standard image format
- **Responsive Design**: Works perfectly on mobile, tablet, and desktop
- **No Pricing Required**: Built for quotation-based sales (pricing can be added later)

## Installation

1. Upload the `wp-steel-store` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under **Steel Store > Settings**
4. Add products under **Steel Store > All Products**
5. Use shortcode `[steel_store]` on any page or in Elementor

## Usage

### Adding Products

1. Go to **Steel Store > Add New**
2. Enter product title and description
3. Upload a product image (recommended: .webp format)
4. Assign tags (categories) to the product
5. Click **Publish**

### Settings

Go to **Steel Store > Settings** to configure:
- **Order Notification Email**: Email address where order notifications will be sent

### Shortcode

Add the store to any page using:
```
[steel_store]
```

Or with a default category:
```
[steel_store default_tag="Panels"]
```

## Design

- **Font**: Montserrat (Bold 700, Semi-Bold 600, Extra Bold 800)
- **Colors**: 
  - White (#ffffff)
  - Light Gray (#f3f4f6, #e5e7eb)
  - Dark Gray (#6b7280, #374151)
  - Black (#111827)
- **Effects**: 3% image scale on hover with subtle glow

## Future Enhancements

This plugin is built to support pricing in the future. When ready to add pricing:
1. Add price meta field to products
2. Display prices in product cards
3. Calculate totals in cart
4. Add payment gateway integration

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Modern web browser with localStorage support

## Support

For issues or questions, contact the plugin developer.

## Credits

Created by Bright Idea Marketing
Developer: Dylan Wood
