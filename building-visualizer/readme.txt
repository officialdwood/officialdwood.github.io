# Building Visualizer WordPress Plugin

## Description
Interactive 3D building configurator that allows customers to customize building dimensions, roof pitch, and colors in real-time. Perfect for building supply companies, construction businesses, and shed manufacturers.

## Features
- **Customizable Dimensions**: Set building width, length, wall height
- **Adjustable Roof Pitch**: Change roof pitch from 1:12 to 12:12
- **Color Customization**: Choose colors for:
  - Roofing
  - Siding
  - Wainscott (bottom 3' of walls)
- **Real-time 3D Preview**: See changes instantly as you adjust parameters
- **Download Capability**: Save the configured building as a PNG image
- **Admin Settings**: Manage available color options through WordPress dashboard

## Installation

1. Upload the `building-visualizer` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Building Visualizer to configure color options
4. Use the shortcode `[building_visualizer]` in any page or post

## Usage

### Basic Shortcode
```
[building_visualizer]
```

### Shortcode with Default Values
```
[building_visualizer width="30" length="50" wall_height="14" roof_pitch="6"]
```

### Parameters
- `width` - Building width in feet (default: 20)
- `length` - Building length in feet (default: 40)
- `wall_height` - Wall height in feet (default: 12)
- `roof_pitch` - Roof pitch rise per 12" (default: 4)

### Using with Elementor
1. Add a Shortcode widget to your page
2. Enter `[building_visualizer]` in the shortcode field
3. Save and preview

## Admin Configuration

Navigate to **Settings > Building Visualizer** to configure:

1. **Roofing Colors**: Add color options for roofing
2. **Siding Colors**: Add color options for siding
3. **Wainscott Colors**: Add color options for wainscott

For each color, provide:
- **Name**: Display name (e.g., "Charcoal", "Barn Red")
- **RGB Values**: Color in R,G,B format (e.g., "139,26,33")

## Requirements
- WordPress 5.0 or higher
- PHP 7.0 or higher
- Modern web browser with HTML5 Canvas support

## Support
For issues or questions, please contact support.

## License
GPL-2.0+

## Changelog

### 1.0.0
- Initial release
- 3D building visualization
- Customizable dimensions and roof pitch
- Color customization for roofing, siding, and wainscott
- Download functionality
- Admin settings page
