=== Color Gallery 29ga ===
Contributors: Color Gallery
Tags: color, gallery, palette, color chips, design
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A beautiful color gallery plugin with hover effects and expandable views for showcasing color palettes.

== Description ==

Color Gallery 29ga is a modern WordPress plugin designed for showcasing color palettes in an elegant and interactive way. Perfect for designers, architects, and anyone who wants to display color collections beautifully.

**Features:**

* Create multiple color galleries with custom names
* **Bulk upload multiple colors at once from media library**
* Add colors to galleries with names and hex values or custom images
* Square color chips with sharp, crisp edges
* Smooth hover enlarge effect
* Click to expand colors in a modal view with close button
* Configurable number of columns per row (default: 6)
* Responsive design that adapts to different screen sizes
* Easy-to-use shortcode system: [color_gallery_29ga_<gallery_name>]

**Usage:**

1. Create a new Color Gallery from the admin menu
2. Go to "Bulk Upload" to add multiple colors quickly
3. Select your gallery and choose multiple images from your WordPress media library
4. Name each color after upload
5. Use the shortcode to display your gallery: [color_gallery_29ga_29ga_standard_color]
   (where "29ga_standard_color" matches your gallery name in lowercase with spaces as underscores)

== Installation ==

1. Upload the `ColorGallery29ga` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create your first gallery from the "Color Galleries" menu
4. Add colors to your gallery
5. Use the shortcode on any page or post

== Frequently Asked Questions ==

= How do I create a gallery? =

Go to Color Galleries > Add New in your WordPress admin. Give your gallery a name and configure the number of columns.

= How do I add colors to a gallery? =

Go to Color Galleries > Colors > Add New. Add a color name, select a gallery, and upload an image from your WordPress media library using "Set featured image" (recommended), or enter a hex color value as an alternative.

= What is the shortcode format? =

Use [color_gallery_29ga_<gallery_name>] where <gallery_name> is your gallery title converted to lowercase with spaces replaced by underscores. For example, if your gallery is titled "29ga_Standard_Color", use [color_gallery_29ga_29ga_standard_color]

= Can I use images instead of solid colors? =

Yes! Upload images from your WordPress media library using the "Set featured image" button. This is the recommended method if you already have color images. Featured images take priority over hex color values.

= How many columns can I have? =

You can configure any number from 1 to 12 columns. The default is 6 columns per row.

== Changelog ==

= 1.0.0 =
* Initial release
* Color gallery management
* Hover zoom effects
* Click to expand with modal view
* Configurable columns
* Responsive design
