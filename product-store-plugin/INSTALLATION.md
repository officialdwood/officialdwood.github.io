# Steel Store Plugin - Installation & Setup Guide

## Quick Start

### 1. Installation

**Option A: Upload via WordPress Admin**
1. Download the `wp-steel-store` folder
2. Zip the entire folder
3. Go to WordPress Admin → Plugins → Add New → Upload Plugin
4. Choose the zip file and click "Install Now"
5. Click "Activate Plugin"

**Option B: FTP/File Manager Upload**
1. Upload the entire `wp-steel-store` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "Steel Store" and click "Activate"

### 2. Initial Configuration

1. After activation, go to **Steel Store > Settings**
2. Enter the email address where you want to receive order notifications
3. Click "Save Settings"

### 3. Add Your First Products

1. Go to **Steel Store > Add New**
2. Enter product details:
   - **Title**: Product name (e.g., "26 Gauge Steel Panel")
   - **Description**: Detailed product information
   - **Product Image**: Click "Upload Image" and select/upload your .webp image
   - **Product Tags**: Check the categories that apply (Panels, Trim, etc.)
3. Click **Publish**

### 4. Display the Store on Your Website

**In WordPress Classic Editor:**
1. Create/edit a page
2. Add the shortcode: `[steel_store]`
3. Save/publish the page

**In Elementor:**
1. Create/edit a page with Elementor
2. Add a "Shortcode" widget
3. Enter: `[steel_store]`
4. Save/publish the page

**In Block Editor (Gutenberg):**
1. Create/edit a page
2. Add a "Shortcode" block
3. Enter: `[steel_store]`
4. Save/publish the page

## Default Product Categories

The plugin comes with 6 default categories (tags):
- Panels
- Trim
- Accessories
- Fasteners
- Building Materials
- Tools

You can add more tags by going to **Steel Store > Tags**.

## How Customers Use the Store

1. **Browse Products**: Customers can click category tabs to filter products
2. **Search**: Use the search bar to find specific products
3. **Add to Cart**: Click "Add to Cart" on any product
4. **View Cart**: Click the cart button (bottom right) to view items
5. **Adjust Quantities**: Change quantities in the cart view
6. **Print Cart**: Print the cart for offline reference
7. **Submit Order**: Fill out contact form and submit order
8. **Email Notification**: You receive an email with the order details

## Tips for Best Results

### Image Guidelines
- **Recommended format**: .webp for best performance
- **Recommended size**: 800x600 pixels or similar aspect ratio
- **File size**: Keep under 200KB for fast loading
- **Alternative formats**: .jpg, .png also work fine

### Product Organization
- Use clear, descriptive titles
- Assign appropriate tags to each product
- Add detailed descriptions to help customers
- Keep product images consistent in style

### Managing Orders
- Check the email address in settings regularly
- Set up email filters/folders for incoming orders
- Consider using a dedicated email for orders (e.g., orders@yourdomain.com)
- Respond to customer orders promptly

## Customization Options

### Change Default Category
You can specify which category shows first:
```
[steel_store default_tag="Trim"]
```

### Styling
The plugin uses these colors by default:
- Background: White (#ffffff)
- Light Gray: (#f3f4f6, #e5e7eb)
- Medium Gray: (#6b7280, #374151)
- Text/Buttons: Dark Gray/Black (#111827)

To customize colors, you can add custom CSS in your theme's customizer or a child theme.

## Troubleshooting

### Products Not Showing
- Make sure products are published (not drafts)
- Check that products have the correct tags assigned
- Clear browser cache and refresh the page

### Images Not Uploading
- Check WordPress media upload limits
- Verify file permissions on wp-content/uploads
- Try a smaller image size
- Contact your hosting provider if issues persist

### Cart Not Working
- Ensure JavaScript is enabled in the browser
- Check browser console for errors
- Clear browser cache and cookies
- Try a different browser to isolate the issue

### Email Not Sending
- Verify the email address in settings is correct
- Check your WordPress site's email functionality
- Consider installing an SMTP plugin (WP Mail SMTP recommended)
- Check spam folders for test orders

## Future: Adding Pricing

When you're ready to add pricing to products:

1. The plugin is designed to support pricing in the future
2. You'll need to add price fields to the product editor
3. Update the frontend to display prices
4. Add total calculation to the cart
5. Optionally integrate payment gateways

Contact your developer to implement pricing features when needed.

## Support

For technical support or custom modifications:
- Email: info@brightidea.media
- Website: https://www.brightidea.media

## Version Information

- **Current Version**: 1.0.0
- **WordPress Required**: 5.0+
- **PHP Required**: 7.2+
- **Tested Up To**: WordPress 6.4

---

**Created by**: Bright Idea Marketing  
**Developer**: Dylan Wood  
**License**: GPL-2.0+
