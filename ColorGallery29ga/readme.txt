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
* Add colors to galleries with names and hex values or custom images
* Square color chips with sharp, crisp edges
* Smooth hover enlarge effect
* Click to expand colors in a modal view with close button
* Configurable number of columns per row (default: 6)
* Responsive design that adapts to different screen sizes
* Easy-to-use shortcode system: [color_gallery_29ga_<gallery_name>]

**Usage:**

1. Create a new Color Gallery from the admin menu
2. Add colors to your gallery by creating Color items and assigning them to a gallery
3. Use the shortcode to display your gallery: [color_gallery_29ga_standard]
   (where "standard" is the slug of your gallery name)

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

Go to Color Galleries > Colors > Add New. Add a color name, select a gallery, and either enter a hex color value or upload an image as the Featured Image.

= What is the shortcode format? =

Use [color_gallery_29ga_<gallery_name>] where <gallery_name> is the URL slug of your gallery. For example, if your gallery is titled "Standard Colors", use [color_gallery_29ga_standard-colors]

= Can I use images instead of solid colors? =

Yes! You can upload a Featured Image for each color, and it will be used instead of the hex color value.

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
