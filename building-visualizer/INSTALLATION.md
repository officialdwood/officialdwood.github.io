# Building Visualizer Plugin - Installation & Usage Guide

## Overview
The Building Visualizer is a WordPress plugin that provides an interactive 3D building configurator. Customers can customize building dimensions, roof pitch, and colors in real-time, then download an image of their configured building.

## Installation Instructions

### Method 1: Direct Installation (Recommended)
1. Download the `building-visualizer.zip` file from the repository
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin** at the top
5. Click **Choose File** and select `building-visualizer.zip`
6. Click **Install Now**
7. After installation, click **Activate Plugin**

### Method 2: Manual Installation
1. Download and extract the `building-visualizer.zip` file
2. Upload the entire `building-visualizer` folder to `/wp-content/plugins/` on your server
3. Log in to WordPress admin dashboard
4. Navigate to **Plugins**
5. Find "Building Visualizer" and click **Activate**

## Configuration

### Step 1: Configure Color Options
1. After activation, go to **Settings > Building Visualizer** in WordPress admin
2. You'll see three sections for colors:
   - **Roofing Colors**: Colors for the roof
   - **Siding Colors**: Colors for the main wall area
   - **Wainscott Colors**: Colors for the bottom 3' of walls

3. For each color section:
   - **Color Name**: Enter a descriptive name (e.g., "Charcoal", "Barn Red")
   - **RGB Values**: Enter the color in R,G,B format (e.g., "139,26,33")
   - Click **Add [Type] Color** to add more colors

4. Click **Save Colors** when finished

### Default Colors (Pre-configured)
**Roofing:**
- Charcoal: 54,69,79
- Barn Red: 139,26,33
- Evergreen: 34,94,68

**Siding:**
- White: 245,245,245
- Tan: 210,180,140
- Gray: 128,128,128

**Wainscott:**
- Brown: 101,67,33
- Black: 30,30,30
- Dark Gray: 64,64,64

## Using the Plugin

### In Regular WordPress Posts/Pages
1. Edit any post or page
2. Add the shortcode: `[building_visualizer]`
3. Save and preview

### In Elementor
1. Edit your page with Elementor
2. Drag a **Shortcode** widget onto your page
3. In the widget settings, enter: `[building_visualizer]`
4. Update the page

### Shortcode Options
You can set default values using shortcode attributes:

```
[building_visualizer width="30" length="50" wall_height="14" roof_pitch="6"]
```

**Available Attributes:**
- `width` - Building width in feet (default: 20, range: 10-100)
- `length` - Building length in feet (default: 40, range: 10-100)
- `wall_height` - Wall height in feet (default: 12, range: 8-20)
- `roof_pitch` - Roof pitch rise per 12" (default: 4, range: 1-12)

## Customer Usage

### Building Configuration
Customers can adjust the following parameters:

1. **Dimensions Section:**
   - Width: Building width in feet
   - Length: Building length in feet
   - Wall Height: Height of walls in feet
   - Roof Pitch: Rise per 12 inches (affects roof steepness)

2. **Colors Section:**
   - Roofing Color: Select from configured roofing colors
   - Siding Color: Select from configured siding colors
   - Wainscott Color: Select from configured wainscott colors

3. **Actions Section:**
   - Download Image: Saves the current building configuration as a PNG image

### Visual Feedback
- The 3D preview updates instantly as parameters change
- Building info is displayed below the canvas showing current dimensions
- The building is rendered in an isometric 3D view showing:
  - Front wall with siding and wainscott
  - Left side wall with siding and wainscott
  - Roof with both visible planes
  - Wainscott appears on the bottom 3' of all visible walls

## Features

### Real-time 3D Rendering
- Isometric 3D view of the building
- Instant updates when dimensions or colors change
- Professional gradient sky background
- Ground plane for context

### Responsive Design
- Works on desktop, tablet, and mobile devices
- Canvas scales appropriately
- Controls stack vertically on smaller screens

### Download Functionality
- High-quality PNG export
- Filename includes dimensions and timestamp
- Uses HTML5 Canvas toDataURL for image generation

## Technical Details

### Files Structure
```
building-visualizer/
├── building-visualizer.php          # Main plugin file
├── readme.txt                        # Plugin readme
├── assets/
│   ├── css/
│   │   ├── style.css                # Frontend styles
│   │   └── admin-style.css          # Admin styles
│   └── js/
│       └── app.js                   # JavaScript for 3D rendering
└── includes/
    ├── class-bv-admin-settings.php  # Admin settings page
    └── class-bv-shortcode.php       # Shortcode handler
```

### Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Requires HTML5 Canvas support

### WordPress Requirements
- WordPress 5.0+
- PHP 7.0+
- jQuery (bundled with WordPress)

## Troubleshooting

### Plugin doesn't appear in admin
- Ensure all files are in `/wp-content/plugins/building-visualizer/`
- Check file permissions (755 for directories, 644 for files)
- Verify main plugin file is `building-visualizer.php`

### Visualizer doesn't display
- Check browser console for JavaScript errors
- Ensure jQuery is loaded
- Verify shortcode is spelled correctly: `[building_visualizer]`

### Colors don't change
- Verify colors are saved in Settings > Building Visualizer
- Check RGB format is correct (R,G,B with no spaces)
- Clear browser cache and reload page

### Download doesn't work
- Ensure browser allows downloads
- Check popup blocker settings
- Try different browser if issue persists

## Support & Customization

### Adding More Color Options
Navigate to **Settings > Building Visualizer** and use the "Add [Type] Color" buttons to add more options.

### Changing Default Values
Modify the shortcode:
```
[building_visualizer width="25" length="45" wall_height="10" roof_pitch="5"]
```

### Styling Customization
Edit `/building-visualizer/assets/css/style.css` to customize the appearance.

### JavaScript Customization
Edit `/building-visualizer/assets/js/app.js` to modify rendering logic.

## License
GPL-2.0+

## Version
1.0.0
