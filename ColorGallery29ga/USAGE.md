# Color Gallery 29ga - Usage Guide

## Overview

Color Gallery 29ga is a WordPress plugin designed to create beautiful, interactive color galleries with hover effects and expandable views. It's perfect for showcasing color palettes, design systems, or any color collections.

## Installation

1. Copy the `ColorGallery29ga` folder to your WordPress plugins directory (`/wp-content/plugins/`)
2. Activate the plugin through the WordPress admin panel (Plugins > Installed Plugins)
3. You'll see a new menu item "Color Galleries" in your admin sidebar

## Creating a Gallery

### Step 1: Create a New Gallery

1. Go to **Color Galleries > Add New**
2. Enter a gallery name (e.g., "Standard Colors")
3. Set the number of columns per row (default is 6)
4. Publish the gallery
5. Note the shortcode displayed in the meta box (e.g., `[color_gallery_29ga_standard-colors]`)

### Step 2: Add Colors to Your Gallery

1. Go to **Color Galleries > Colors > Add New**
2. Enter the color name (this will appear below the color chip)
3. Assign the color to a gallery using the dropdown
4. Choose one of two options for the color display:
   - **Option A:** Enter a hex color value (e.g., `#FF5733`)
   - **Option B:** Upload a custom image using "Set featured image"
5. Publish the color

### Step 3: Display Your Gallery

Use the shortcode on any page or post:

```
[color_gallery_29ga_standard-colors]
```

Replace `standard-colors` with the slug of your gallery name.

## Features

### Square Color Chips with Sharp Edges

- All color tiles are perfectly square (1:1 aspect ratio)
- No rounded corners - crisp, sharp edges
- High-quality rendering for professional appearance

### Hover Effects

- Colors slightly enlarge when you hover over them
- Smooth animation transition
- Shadow effect increases for depth

### Click to Expand

- Click any color tile to open it in a modal view
- Large, detailed view of the color
- Color name displayed prominently

### Close Modal

Three ways to close the expanded view:
1. Click the X button in the top-right corner
2. Click outside the color box (on the dark background)
3. Press the ESC key on your keyboard

### Responsive Design

The gallery automatically adjusts columns based on screen size:
- Desktop (1200px+): Your configured columns (default 6)
- Tablet (768-1199px): 4-5 columns
- Mobile (< 768px): 2-3 columns

## Shortcode Format

The shortcode format is: `[color_gallery_29ga_<gallery-slug>]`

Where `<gallery-slug>` is the URL-friendly version of your gallery name:
- "Standard Colors" → `[color_gallery_29ga_standard-colors]`
- "Primary Palette" → `[color_gallery_29ga_primary-palette]`
- "MyGallery" → `[color_gallery_29ga_mygallery]`

## Customization Options

### Number of Columns

You can set the number of columns per row in the gallery settings:
- Minimum: 1 column
- Maximum: 12 columns
- Default: 6 columns

### Color Display Options

Each color can display either:
1. **Solid Color:** Use hex values like `#FF5733`, `#3498DB`
2. **Custom Image:** Upload any image as the Featured Image

## Technical Details

### File Structure

```
ColorGallery29ga/
├── color-gallery-29ga.php    # Main plugin file
├── readme.txt                 # WordPress readme
├── demo.html                  # Standalone demo
├── USAGE.md                   # This file
├── assets/
│   ├── css/
│   │   └── style.css         # Frontend styles
│   ├── js/
│   │   └── app.js            # Frontend JavaScript
│   └── img/                  # Image assets
```

### Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

### Performance

- Lightweight CSS and JavaScript
- No external dependencies (uses jQuery from WordPress)
- Optimized animations with CSS transitions
- Lazy loading compatible

## Troubleshooting

### Gallery not displaying

- Make sure you've added at least one color to the gallery
- Check that the gallery slug in your shortcode matches the gallery name
- Verify the plugin is activated

### Colors not appearing

- Ensure colors are assigned to the correct gallery
- Check that colors are published (not drafts)
- Verify hex color values start with `#`

### Hover effect not working

- Make sure JavaScript is enabled in your browser
- Check browser console for JavaScript errors
- Clear your browser cache

## Tips & Best Practices

1. **Naming:** Use descriptive names for your colors (e.g., "Ocean Blue" instead of just "Blue")
2. **Organization:** Create separate galleries for different color schemes or projects
3. **Columns:** Use 6 columns for desktop viewing, but test on mobile devices
4. **Images:** If using images, use square images for best results
5. **Hex Colors:** Always include the `#` symbol before hex values

## Support

For issues, questions, or feature requests, please refer to the plugin documentation or contact support.

## Version History

### 1.0.0 (Current)
- Initial release
- Core gallery functionality
- Hover and expand effects
- Responsive design
- Configurable columns
