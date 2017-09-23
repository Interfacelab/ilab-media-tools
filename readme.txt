=== Media Cloud ===
Contributors: interfacelab
Tags: uploads, amazon, s3, amazon s3, imgix, media, cdn, rekognition, cloudfront, images, crop, image editing, image editor, media library, offload, offload s3, minio, google cloud storage, digital ocean spaces, backblaze
Requires at least: 4.4
Tested up to: 4.8.2
License: GPLv3 or later
Donate link: http://www2.jdrf.org/site/TR?fr_id=6912&pg=personal&px=11429802
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Stable tag: 2.0.2
Requires PHP: 5.6

Automatically upload media to Amazon S3 and integrate with Imgix, a real-time image processing CDN.  Boosts site performance and simplifies workflows.

== Description ==

Media Cloud by ILAB is a suite of tools designed to enhance media handling in WordPress in a number of ways.

**NOTE**: This plugin requires PHP 5.6x or higher (PHP 7.x preferred)

= Upload to S3, Minio, Google Cloud Storage and Digital Ocean Spaces =
Automatically copy media uploads to S3 (and S3 compatible services) and hosts your media directly from S3, CloudFront or any other CDN.  Additionally, easily import your existing media library to Amazon S3 with the push of a button.

= Integrate with Imgix =
[Imgix](https://imgix.com) will radically change the way that you build your WordPress sites and themes.  This plugin is the best integration available for WordPress.  Upload your images to S3 with our S3 tool and then host the media with Imgix, providing you with real-time image processing and automatic format delivery.  Forget ImageMagick, Imgix is light years ahead in quality and speed.

= Native support for Google Cloud Storage =
Now supports using Google Cloud Storage for uploads without having to use Google's S3 compatible interop mode.  Supports direct uploads too.

= Native Support For Backblaze B2 Cloud Storage =
Now supports using Backblaze's new B2 Cloud Storage for uploads.

= Upload Directly To S3, Minio and Google Cloud Storage =
Directly upload your media and documents to S3, bypassing your WordPress server completely.  Note that this feature requires Imgix.

= Automatically Tag and Categorize with Amazon Rekognition =
Use Amazon's latest AI tools to tag and categorize your images when uploading to S3.  With Rekognition, you can automatically detect objects, scenes, and faces in images.

= Advanced Image Editing =
When integrating with [Imgix](https://imgix.com), Media Cloud by ILAB provides the most advanced WordPress image editor.  Alter contrast, saturation, vibrancy and over 30 other image editing operations - in real time right inside the WordPress admin interface!  Completely non-destructive!

= Image Cropping =
Media Cloud by ILAB ships with the most advanced image cropping tool available for WordPress, based on Chen Fengyuan's amazing Cropper plugin for jQuery.

**Best of all you get this functionality for free.**

* Upload to S3
* Host media from S3 or any CDN that can connect to S3
* Integrate with Imgix media hosting
* Direct uploads to S3
* Use Amazon Rekognition to automatically tag and categorize images
* Use third party cloud file providers that use S3 compatible APIs
* Advanced image cropping tool
* Advanced image editing with saturation, contrast, auto-correct,
  sharpen, blur and more (only when integrated with Imgix)

For more info (grab popcorn, it's kind of long!):

https://www.youtube.com/watch?v=rOmziu30nPI

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ilab-media-tools` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Enable the tools you want through the *ILab Media Tools -> Tools* settings page.
4. For S3, enter your AWS credentials in the *ILab Media Tools -> S3* Settings* page.
5. For Imgix, enter your Imgix settings in the *ILab Media Tools -> Imgix Settings* page.
6. Once your settings are complete, use the *ILab Media Tools -> S3 Importer* to import your current media library to
   Amazon S3.

== Frequently Asked Questions ==

= How does this compare to WP Offload S3? =

This essentially does everything that WP Offload S3 does but is free.  It includes an import function for importing your current library to S3 that only the pro version of WP Offload S3 has.  Otherwise, they work almost exactly the same.

= Why should I use Imgix? =

One of the headaches of managing a WordPress site is dealing with server disk space.  If you just use the S3 functionality of this plugin, you are already one step ahead.  Using S3, all of your media is centrally located in one place that you can then distribute through a high performing content delivery network to improve page load speeds for your site.  You also don't have to worry about disk space on your servers anymore.

Imgix is a content delivery network with a twist.  In addition to distributing your media, it also allows you to edit them, in real-time. and deliver the edited version through their CDN without altering the original.  Want to add a new image size to your theme?  You can do this with Imgix without having to use a plugin to recut all of your existing media to this new size.  Imgix optimizes format delivery and a bunch of other things.  It's seriously the greatest thing to happen to WordPress and web development in the history of ever.

= Are you a paid shill for Imgix? =

No, I'm just one very enthusiastic customer.

== Screenshots ==

1. Easy image cropping for all croppable image sizes defined in your theme.
2. Make adjustments to your images like saturation, vibrancy, contrast when using Imgix.
3. Stylize your images easily when using Imgix.
3. Watermarking is easy and non-destructive, change the watermark at any time and all watermarked images will automatically update.
4. Amazon S3 settings.
6. Imgix settings.

== Changelog ==

= 2.0.2 =
* Fix for srcset not being generated on WordPress generated image tags.  Note that this will only fix future uploads, not existing uploads.

= 2.0.1 =
* Major refactoring of the code base
* Storage services are now "pluggable", meaning new ones can be added that aren't S3 compatible
* Added native Google Cloud Storage support
* Direct uploads to Google Cloud Storage
* Added native Backblaze B2 support
* Improved support for other S3 compatible services
* Other misc. fixes

= 2.0.0 =
* Major refactoring of the code base
* Storage services are now "pluggable", meaning new ones can be added that aren't S3 compatible
* Added native Google Cloud Storage support
* Direct uploads to Google Cloud Storage
* Added native Backblaze B2 support
* Improved support for other S3 compatible services
* Other misc. fixes

= 1.5.3 =
* Enabled Amazon S3 Transfer Acceleration for even faster uploads.  You should enable this ASAP.
* Direct uploads to Minio storage servers now work (still requires Imgix).
* Fix for missing mime_content_type() errors on misconfigured PHP installs
* Added path style endpoint option for S3 compatible endpoints
* Misc. fixes

= 1.5.2 =
* Added Rekognition importer utility for running existing media on S3 through Rekognition
* Added bulk actions for S3 importing and Rekognition processing to the WordPress media library
* Added S3 icon overlay in the media library (grid mode)
* Added S3 column to the media library (list mode)
* Misc. fixes

= 1.5.1 =
* Added support for Amazon Rekognition for auto tagging and categorizing uploaded images
* Added debug for support tickets
* Misc. fixes

= 1.5.0 =
* Upload media directly to S3, bypassing WordPress.  This feature requires Imgix.
* Ability to cancel S3 Importer
* Fixes to S3 importing
* Misc. fixes

= 1.4.9 =
* Compatibility with S3-compatible services like Minio, Google Cloud Storage (when in interoperability mode) and Digital Ocean Spaces.  (thanks to Vladimir Pouzanov)
* Fix for S3 Importer on Windows (seriously stop hosting WordPress on Windows)
* Misc. fixes

= 1.4.7 =
* Fixed collision with Types plugin
* Added donation link.  If you find this plugin useful, PLEASE donate to my JDRF fund raiser for finding a cure for type 1 diabetes.

= 1.4.6 =
* Added flag to skip bucket checks to avoid rate limiting errors from Amazon on media heavy sites
* Fixed the log spam for the isset offset error

= 1.4.5 =
* Updated AWS SDK to 3.26.5
* Fix for PDF upload bug
* Misc. bug fixes

= 1.4.4 =
* Fix incompatibility with WP Rocket
* Fix for env() bug

= 1.4.3 =
* Improve new user on-boarding

= 1.4.2 =
* Changed plugin name
* Ability to import non-standard image types such as TIFF, Adobe Photoshop, and Adobe Illustrator (requires Imgix)
* Ability to render PDF as images (requires Imgix)
* Turn off auto-upload for non-image types
* Specify specific MIME types to NOT upload to S3
* Improved compatibility with Offload S3
* Added ilab_s3_upload_params filter for the ability to specify custom S3 metadata for each upload
* Ability to define custom ACL for uploads
* S3 info displayed in Attachment Details pop up modal
* S3 metabox added to Attachment edit pages, allowing you to edit the Cache-Control, Expires and ACL for the S3 upload
* Fix for cropping images on S3
* Other bug fixes.

= 1.4.1 =
* Changed plugin name
* Ability to import non-standard image types such as TIFF, Adobe Photoshop, and Adobe Illustrator (requires Imgix)
* Ability to render PDF as images (requires Imgix)
* Turn off auto-upload for non-image types
* Specify specific MIME types to NOT upload to S3
* Improved compatibility with Offload S3
* Added ilab_s3_upload_params filter for the ability to specify custom S3 metadata for each upload
* Ability to define custom ACL for uploads
* S3 info displayed in Attachment Details pop up modal
* S3 metabox added to Attachment edit pages, allowing you to edit the Cache-Control, Expires and ACL for the S3 upload
* Fix for cropping images on S3
* Other bug fixes.

= 1.4 =
* Changed plugin name
* Ability to import non-standard image types such as TIFF, Adobe Photoshop, and Adobe Illustrator (requires Imgix)
* Ability to render PDF as images (requires Imgix)
* Turn off auto-upload for non-image types
* Specify specific MIME types to NOT upload to S3
* Improved compatibility with Offload S3
* Added ilab_s3_upload_params filter for the ability to specify custom S3 metadata for each upload
* Ability to define custom ACL for uploads
* S3 info displayed in Attachment Details pop up modal
* S3 metabox added to Attachment edit pages, allowing you to edit the Cache-Control, Expires and ACL for the S3 upload
* Fix for cropping images on S3
* Other bug fixes.

= 1.2.3 =
* Fix for importing non-image files to S3 with the importer tool

= 1.2.2 =
* Ability to specify Cache-Control and Expires metadata for S3 uploads (thanks to metadan)
* Fix for @{versioning} bucket prefix token

= 1.2.1 =
* Fix for admin notice dismissal when used with Bedrock

= 1.2 =
* Fix for imgix admin notice
* Added filter for defining default imgix parameters (thanks to eightam)
* Fix for deleting files from S3 (thanks to Lotykun)
* Support for auto compress (thanks to JulienMelissas)

= 1.1.1 =
* Fix for image sizes disappearing in the insert media dialog.

= 1.1.0 =
* Fixed a bug where SEO Framework and some other plugins were resizing images on the fly, causing an image to be
  downloaded to be resized.
* You can now specify a prefix to be prepended to file names when uploaded to S3.

= 1.0.5 =
* Improved compatibility with other plugins that are using the AWS SDK.  There should be zero compatibility issues.
* Forward compatibility with Offload S3.  Any media uploaded with Offload S3 will continue to work normally when
  switching to Media Cloud by ILAB.
* Check to make sure WordPress is being run on 5.5 or better.

= 1.0.4 =
* Updated readme.txt to indicate PHP version

= 1.0.3 =
* Fix for generating Imgix URLs for dynamically sized images.

= 1.0.2 =
* Correct versioning

= 1.0.1 =
* Update readme.txt

= 1.0.0 =
* First release
