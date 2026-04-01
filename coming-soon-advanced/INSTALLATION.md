# Coming Soon Advanced - WordPress Plugin

## Overview
A stunning WordPress "Coming Soon" page plugin with golden metallic text effects, smoke overlay, and full customization options.

## Features

✨ **Visual Effects**
- Matte black background (default)
- Animated smoke overlay effect
- Golden metallic glowing text
- Animated loading sheen on "Coming Soon..." text
- Fully responsive design

🎨 **Customization Options**
- Enable/Disable toggle for the coming soon page
- Logo upload (displayed centered above description)
- Background image upload (replaces default matte black)
- Custom description text
- Default text: "Home Of The Most Advanced Basketball Player Database and Player Portal."

🔒 **Admin Features**
- Administrators can access the site normally while coming soon is active
- Easy-to-use settings page
- Preview button to view the coming soon page
- WordPress media library integration

## Installation Instructions

### Method 1: Upload via WordPress Admin
1. Download the `coming-soon-advanced.zip` file
2. Log into your WordPress admin dashboard
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin** button at the top
5. Click **Choose File** and select `coming-soon-advanced.zip`
6. Click **Install Now**
7. Click **Activate Plugin**

### Method 2: Manual Installation via FTP
1. Download and unzip `coming-soon-advanced.zip`
2. Upload the `coming-soon-advanced` folder to `/wp-content/plugins/` directory
3. Log into WordPress admin dashboard
4. Navigate to **Plugins**
5. Find "Coming Soon Advanced" and click **Activate**

## Configuration

After activation, configure the plugin:

1. Navigate to **Coming Soon** in the WordPress admin menu (left sidebar)
2. Configure your settings:
   - **Enable Coming Soon Page**: Toggle ON to activate
   - **Logo**: Click "Choose Logo" to upload your logo image
   - **Background Image**: Click "Choose Background" to upload a custom background
   - **Description Text**: Enter or edit the main message (default provided)
3. Click **Save Changes**

## Usage

### Enabling the Coming Soon Page
1. Go to **Coming Soon > Settings**
2. Toggle **Enable Coming Soon Page** to ON
3. Click **Save Changes**
4. Visitors will now see the coming soon page
5. Admins can still access the site normally

### Previewing
- When enabled, click the **Preview Coming Soon Page** button to view in a new tab

### Disabling
1. Go to **Coming Soon > Settings**
2. Toggle **Enable Coming Soon Page** to OFF
3. Click **Save Changes**
4. Your normal site is now visible to all visitors

## File Structure
```
coming-soon-advanced/
├── coming-soon-advanced.php    # Main plugin file
├── readme.txt                   # WordPress plugin readme
├── includes/
│   └── admin-settings.php      # Admin settings page
└── assets/
    ├── css/
    │   └── frontend.css        # Frontend styles
    ├── js/
    │   ├── admin.js           # Admin JavaScript
    │   └── frontend.js        # Frontend JavaScript
    └── img/                   # Images directory (for future assets)
```

## Technical Details

- **Version**: 1.0.0
- **Requires WordPress**: 5.0+
- **Tested up to**: 6.4
- **PHP Version**: 7.0+
- **License**: GPL-2.0+

## FAQ

**Q: Can I access my site when coming soon is active?**
A: Yes! Administrators (users with manage_options capability) can access the site normally.

**Q: What image formats are supported?**
A: All standard formats: JPG, PNG, GIF, WebP. PNG recommended for logos with transparency.

**Q: Will this affect SEO?**
A: The page is designed for pre-launch use. For best SEO, disable it when your site is ready to launch.

**Q: Can I customize the colors?**
A: The current version features the golden metallic color scheme as specified. Future versions may include color customization.

## Support

For issues or questions, please contact the plugin author or submit an issue in the repository.

## Changelog

### Version 1.0.0
- Initial release
- Matte black background with animated smoke overlay
- Golden metallic glowing text effects
- Animated loading sheen on "Coming Soon..." text
- Logo upload functionality
- Background image upload functionality
- Custom description text
- Easy on/off toggle
- Responsive design
- WordPress media library integration

---

Created by DWood | License: GPL-2.0+
