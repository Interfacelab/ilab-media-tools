# ILAB Media Tools

ILAB Media Tools are a suite of tools designed to enhance media handling in WordPress in a number of ways.

##### Image Cropping
ILAB Media Tools ships with the most advanced image cropping tool 
available for WordPress, based on Chen Fengyuan's amazing Cropper 
plugin for jQuery.

##### Upload to S3
Automatically copy media uploads to S3 and hosts your media directly 
from S3 or CloudFront.  Additionally, easily import your existing
media library to Amazon S3 with the push of a button.

##### Integrate with Imgix
[Imgix](https://imgix.com) will radically change the way that you build
your WordPress sites and themes.  This plugin is the best integration
available for WordPress.  Upload your images to S3 with our S3 tool
and then host the media with Imgix, providing you with real-time image
processing and automatic format delivery.  Forget ImageMagick, Imgix
is light years ahead in quality and speed.

##### Advanced Image Editing
When integrating with [Imgix](https://imgix.com), ILAB Media Tools provides the most 
advanced WordPress image editor.  Alter contrast, saturation, vibrancy
and over 30 other image editing operations - in real time right inside
the WordPress admin interface!  Completely non-destructive!

**Best of all you get this functionality for free.**

* Advanced image cropping tool
* Upload to S3
* Host media from S3 or any CDN that can connect to S3
* Integrate with Imgix media hosting
* Advanced image editing with saturation, contrast, auto-correct, 
  sharpen, blur and more (only when integrated with Imgix)
  
## Installation

1. Upload the plugin files to the `/wp-content/plugins/ilab-media-tools` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Enable the tools you want through the *ILab Media Tools -> Tools* settings page.
4. For S3, enter your AWS credentials in the *ILab Media Tools -> S3* Settings* page.
5. For Imgix, enter your Imgix settings in the *ILab Media Tools -> Imgix Settings* page.
6. Once your settings are complete, use the *ILab Media Tools -> S3 Importer* to import your current media library to
   Amazon S3.

## Frequently Asked Questions

##### How does this compare to WP Offload S3?

This essentially does everything that WP Offload S3 does but is free.  It includes an import function for importing
your current library to S3 that only the pro version of WP Offload S3 has.  Otherwise, they work almost exactly the
same.

##### Why should I use Imgix?

One of the headaches of managing a WordPress site is dealing with server disk space.  If you just use the S3
functionality of this plugin, you are already one step ahead.  Using S3, all of your media is centrally located in
one place that you can then distribute through a high performing content delivery network to improve page load speeds
for your site.  You also don't have to worry about disk space on your servers anymore.

Imgix is a content delivery network with a twist.  In addition to distributing your media, it also allows you to edit
them, in real-time. and deliver the edited version through their CDN without altering the original.  Want to add a new
image size to your theme?  You can do this with Imgix without having to use a plugin to recut all of your existing
media to this new size.  Imgix optimizes format delivery and a bunch of other things.  It's seriously the greatest
thing to happen to WordPress and web development in the history of ever.

##### Are you a paid shill for Imgix?

No, I'm just one very enthusiastic customer.