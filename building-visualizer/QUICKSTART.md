# Building Visualizer - Quick Start Guide

## What You Get

A complete WordPress plugin that creates an interactive building configurator with:
- Real-time 3D visualization
- Customizable dimensions and colors
- Download functionality
- Admin color management

## Installation (3 Steps)

### Step 1: Upload Plugin
1. Download `building-visualizer.zip` from the repository
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" then "Activate"

### Step 2: Configure Colors (Optional)
1. Go to WordPress Admin > Settings > Building Visualizer
2. Default colors are already configured, but you can:
   - Add more color options
   - Modify existing colors
   - Remove colors you don't want
3. Click "Save Colors"

### Step 3: Add to Your Page
1. Edit any page or post
2. Add the shortcode: `[building_visualizer]`
3. Or in Elementor: Add Shortcode widget with `[building_visualizer]`
4. Save and view your page

## What Your Customers See

```
┌─────────────────────────────────────────────────────────────────┐
│                    Building Visualizer                           │
│              Customize your building and see it come to life!    │
├──────────────────┬──────────────────────────────────────────────┤
│                  │                                               │
│  Dimensions      │                                               │
│  ┌────────────┐  │        [3D Building Preview]                 │
│  │ Width: 20  │  │                                               │
│  │ Length: 40 │  │          /\                                  │
│  │ Height: 12 │  │         /  \                                 │
│  │ Pitch: 4   │  │        /____\                                │
│  └────────────┘  │       /│    │\                               │
│                  │      / │    │ \                              │
│  Colors          │     /  │    │  \                             │
│  ┌────────────┐  │    /___│____│___\                           │
│  │ Roofing ▼  │  │   │    │    │    │                          │
│  │ Siding  ▼  │  │   │    │    │    │                          │
│  │ Wainsc. ▼  │  │   └────┴────┴────┘                          │
│  └────────────┘  │                                               │
│                  │   Building Size: 20' x 40'                    │
│  Actions         │   Wall Height: 12'                            │
│  ┌────────────┐  │   Roof Pitch: 4:12                           │
│  │ Download   │  │                                               │
│  └────────────┘  │                                               │
│                  │                                               │
└──────────────────┴──────────────────────────────────────────────┘
```

## How It Works

### For Customers:
1. **Adjust Dimensions**
   - Change width: Building gets wider/narrower
   - Change length: Building gets longer/shorter
   - Change wall height: Walls get taller/shorter
   - Change roof pitch: Roof gets steeper/flatter

2. **Choose Colors**
   - Select roofing color from dropdown
   - Select siding color from dropdown
   - Select wainscott color (bottom 3' of walls) from dropdown

3. **See Results**
   - Preview updates instantly
   - View from isometric 3D angle
   - See front wall, side wall, and roof

4. **Download Image**
   - Click "Download Image" button
   - Get PNG file named: `building-20x40-[timestamp].png`
   - Use for records, quotes, or reference

## Admin Color Management

### Adding New Colors:

1. Go to: **Settings > Building Visualizer**

2. You'll see three sections:
   - **Roofing Colors**
   - **Siding Colors**
   - **Wainscott Colors**

3. For each color:
   ```
   Color Name: [Barn Red        ]  ← Descriptive name
   RGB Values: [139,26,33        ]  ← R,G,B format (no spaces!)
   Preview:    [█████            ]  ← Live preview
   ```

4. Click "Add [Type] Color" to add more options

5. Click "Save Colors" when done

### RGB Color Format:
- Format: `R,G,B`
- Example: `139,26,33` (red)
- Example: `54,69,79` (gray/blue)
- Example: `34,94,68` (green)
- NO spaces between numbers!

## Shortcode Options

### Basic (uses defaults):
```
[building_visualizer]
```
Result: 20' x 40' building, 12' walls, 4:12 pitch

### Custom Defaults:
```
[building_visualizer width="30" length="50" wall_height="14" roof_pitch="6"]
```
Result: 30' x 50' building, 14' walls, 6:12 pitch

## Common Questions

**Q: Can customers change dimensions?**
A: Yes! All dimensions have input fields they can adjust.

**Q: How many colors can I add?**
A: Unlimited! Add as many as you want in the settings.

**Q: Does it work on mobile?**
A: Yes! It's fully responsive and works on all devices.

**Q: Can I use it with Elementor?**
A: Yes! Use the Shortcode widget with `[building_visualizer]`

**Q: Does it work with other page builders?**
A: Yes! Any page builder that supports shortcodes will work.

**Q: Where is the data saved?**
A: Colors are saved in WordPress options. No custom database tables needed.

**Q: Do images get saved on the server?**
A: No, images are generated client-side and downloaded directly to the customer's device.

## Visual Features

### What the 3D View Shows:
- ✅ Front wall (with siding and wainscott)
- ✅ Left side wall (with siding and wainscott)
- ✅ Roof (both visible planes)
- ✅ Roof peak (calculated from pitch)
- ✅ Sky background (gradient)
- ✅ Ground plane
- ✅ Professional shading for depth

### Wainscott Display:
- Always 3 feet high
- Shows on bottom of all visible walls
- Different color from main siding
- Realistic appearance

### Roof Pitch:
- 4:12 means 4 inches rise per 12 inches run
- Higher pitch = steeper roof
- Lower pitch = flatter roof
- Range: 1:12 to 12:12

## File Structure After Installation

WordPress will create:
```
/wp-content/plugins/building-visualizer/
├── building-visualizer.php      ← Main plugin
├── assets/
│   ├── css/
│   │   ├── style.css           ← Frontend styling
│   │   └── admin-style.css     ← Admin styling
│   └── js/
│       └── app.js              ← 3D rendering
└── includes/
    ├── class-bv-admin-settings.php
    └── class-bv-shortcode.php
```

## Customization Tips

### Change Canvas Size:
Edit `style.css`, find `#bv-canvas` and adjust width/height

### Change Default Dimensions:
Use shortcode attributes:
```
[building_visualizer width="25" length="45"]
```

### Add More Color Categories:
Would require code modification (future enhancement)

### Change Sky Color:
Edit `app.js`, find `skyGradient` and change color values

### Change Ground Color:
Edit `app.js`, find ground section and change `#8B7355`

## Testing the Plugin

### Using demo.html:
1. Open `building-visualizer/demo.html` in a web browser
2. This shows the plugin without WordPress
3. All features work exactly the same
4. Use for testing before deploying

### In WordPress:
1. Create a test page
2. Add shortcode
3. Preview the page
4. Test all controls
5. Try downloading an image

## Support Resources

- **README.md** - Overview and quick start
- **INSTALLATION.md** - Detailed installation guide
- **FEATURES.md** - Complete feature list
- **readme.txt** - WordPress plugin info
- **demo.html** - Standalone demo

## Production Ready

The plugin is complete and tested:
- ✅ No syntax errors
- ✅ Security best practices
- ✅ WordPress coding standards
- ✅ Responsive design
- ✅ Cross-browser compatible
- ✅ Documentation complete
- ✅ Ready to use!

## Next Steps

1. Download `building-visualizer.zip`
2. Install in WordPress
3. Configure colors (optional)
4. Add to your pages
5. Let customers design their buildings!

---

**Need Help?**
- Check INSTALLATION.md for detailed setup
- Review FEATURES.md for technical details
- Open demo.html to see it in action
