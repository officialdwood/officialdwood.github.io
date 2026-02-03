# Building Visualizer WordPress Plugin

## 🏗️ What is This?

A complete, ready-to-use WordPress plugin that provides an interactive 3D building configurator. Perfect for building supply companies, shed manufacturers, and construction businesses.

## ✨ Key Features

- **Interactive 3D Visualization** - Real-time isometric view of buildings
- **Customizable Dimensions** - Width, length, wall height, and roof pitch
- **Color Customization** - Roofing, siding, and wainscott colors
- **Download Functionality** - Save configured building as PNG image
- **WordPress Admin Integration** - Manage color options through dashboard
- **Shortcode Support** - Works with Elementor and all page builders
- **Mobile Responsive** - Works on all devices

## 📦 Installation

### Quick Start
1. Download `building-visualizer.zip` from this repository
2. In WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Click **Activate Plugin**
5. Go to **Settings > Building Visualizer** to configure colors
6. Add `[building_visualizer]` shortcode to any page

### Detailed Instructions
See [INSTALLATION.md](building-visualizer/INSTALLATION.md) for complete installation and configuration guide.

## 🚀 Usage

### Basic Shortcode
```
[building_visualizer]
```

### With Custom Defaults
```
[building_visualizer width="30" length="50" wall_height="14" roof_pitch="6"]
```

### In Elementor
1. Add a **Shortcode** widget
2. Enter `[building_visualizer]`
3. Update page

## 🎨 Configuration

Navigate to **Settings > Building Visualizer** in WordPress admin to:
- Add roofing color options (with RGB values)
- Add siding color options (with RGB values)
- Add wainscott color options (with RGB values)

Color format: RGB values separated by commas (e.g., "139,26,33" for barn red)

## 📋 What's Included

```
building-visualizer.zip          ← Install this file in WordPress
building-visualizer/
├── building-visualizer.php      ← Main plugin file
├── readme.txt                   ← WordPress plugin readme
├── INSTALLATION.md              ← Detailed installation guide
├── FEATURES.md                  ← Complete feature list
├── demo.html                    ← Standalone demo page
├── assets/
│   ├── css/
│   │   ├── style.css           ← Frontend styles
│   │   └── admin-style.css     ← Admin styles
│   └── js/
│       └── app.js              ← 3D rendering engine
└── includes/
    ├── class-bv-admin-settings.php   ← Admin settings
    └── class-bv-shortcode.php        ← Shortcode handler
```

## 🎯 How It Works

### For Customers
1. Open page with building visualizer
2. Adjust building dimensions (width, length, wall height)
3. Change roof pitch to see roof steepness
4. Select colors for roofing, siding, and wainscott
5. See instant 3D preview
6. Download the configured building image

### For Administrators
1. Install and activate plugin
2. Configure available color options
3. Add shortcode to pages
4. Customers can now use the visualizer

## 🔧 Technical Details

- **Requirements**: WordPress 5.0+, PHP 7.0+
- **File Size**: ~14KB (zipped)
- **Dependencies**: jQuery (included with WordPress)
- **Browser Support**: All modern browsers with HTML5 Canvas
- **Database**: Uses WordPress options API (no custom tables)
- **Security**: Follows WordPress coding standards

## 📚 Documentation

- **[INSTALLATION.md](building-visualizer/INSTALLATION.md)** - Complete installation and setup guide
- **[FEATURES.md](building-visualizer/FEATURES.md)** - Detailed feature list and technical specifications
- **[readme.txt](building-visualizer/readme.txt)** - WordPress plugin readme
- **[demo.html](building-visualizer/demo.html)** - Standalone demo (can open in browser)

## 🎨 Default Colors

The plugin comes pre-configured with professional color options:

**Roofing:** Charcoal, Barn Red, Evergreen  
**Siding:** White, Tan, Gray  
**Wainscott:** Brown, Black, Dark Gray

You can easily add more colors or modify these through the admin interface.

## 🖼️ Visual Features

- Isometric 3D view showing front and left walls
- Realistic roof with proper pitch calculation
- Wainscott visible on bottom 3' of walls
- Sky gradient background
- Ground plane for context
- Professional shading for depth

## ⚙️ Customization

All aspects can be customized:
- **Colors**: Add unlimited color options through admin
- **Styles**: Edit CSS files for visual customization
- **Rendering**: Modify JavaScript for different visualizations
- **Defaults**: Set default dimensions via shortcode attributes

## 🔒 Security

- WordPress nonce verification
- Input sanitization and validation
- XSS protection
- Follows WordPress security best practices
- No external API calls

## 📝 Shortcode Parameters

| Parameter | Description | Default | Range |
|-----------|-------------|---------|-------|
| `width` | Building width in feet | 20 | 10-100 |
| `length` | Building length in feet | 40 | 10-100 |
| `wall_height` | Wall height in feet | 12 | 8-20 |
| `roof_pitch` | Roof pitch (rise per 12") | 4 | 1-12 |

## 🎓 Example Use Cases

- Shed manufacturers letting customers design their shed
- Building supply companies showing product options
- Construction businesses for quotes and estimates
- Homebuilders for garage/outbuilding visualization
- Agricultural suppliers for barn/storage building design

## 🐛 Troubleshooting

**Plugin doesn't appear:**
- Check file location: `/wp-content/plugins/building-visualizer/`
- Verify main file: `building-visualizer.php`

**Visualizer doesn't show:**
- Verify shortcode spelling: `[building_visualizer]`
- Check browser console for errors
- Ensure jQuery is loaded

**Colors don't work:**
- Save colors in Settings > Building Visualizer
- Use correct RGB format: "R,G,B" (no spaces)
- Clear browser cache

## 📄 License

GPL-2.0+ (WordPress Compatible)

## 🔄 Version

1.0.0 - Initial Release

## 💡 Future Enhancements

Potential additions:
- Door and window placement
- Multiple roof styles
- Trim color options
- Texture patterns
- Price calculator
- Email quote functionality
- Save/load configurations

## 📞 Support

For questions or issues:
1. Check the [INSTALLATION.md](building-visualizer/INSTALLATION.md) guide
2. Review [FEATURES.md](building-visualizer/FEATURES.md) for technical details
3. Try the [demo.html](building-visualizer/demo.html) to see expected behavior

## ✅ Ready to Use

The plugin is complete and ready for production use:
- ✅ All code files created
- ✅ PHP syntax validated (no errors)
- ✅ JavaScript syntax validated (no errors)
- ✅ Follows WordPress coding standards
- ✅ Security best practices implemented
- ✅ Responsive design included
- ✅ Complete documentation provided
- ✅ Bundled in installable .zip file

## 🚀 Get Started

Download `building-visualizer.zip` and install it in your WordPress site today!
