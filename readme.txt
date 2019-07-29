=== Media Cloud ===
Contributors: mediacloud, interfacelab
Tags: uploads, amazon, s3, imgix, minio, google cloud storage, digital ocean spaces, wasabi, media, cdn, rekognition, cloudfront, images, crop, image editing, image editor, media library, offload, offload s3, filepicker, smush, ewww, imagify, shortpixel
Requires at least: 4.4
Tested up to: 5.2.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Stable tag: 3.1.2
Requires PHP: 5.6.4

Automatically store media on Amazon S3, Google Cloud Storage, DigitalOcean Spaces + others. Serve CSS/JS assets through CDNs.  Integrate with Imgix.

== Description ==

https://www.youtube.com/watch?v=3tB3rKkwAJY

Media cloud is a revolutionary plug-in for WordPress that will supercharge the performance of your website and radically transform the way that you work with media in WordPress.

Media Cloud works by moving your images, media and other files from your WordPress server to online cloud storage such as Amazon S3, Google Cloud Storage, DigitalOcean Spaces and many others.  You can then serve that media through a CDN like Amazon Cloud front, Cloudflare, Fastly and others.

Beyond cloud storage, Media Cloud also has deep integration with Imgix, the leading real-time image manipulation and optimization CDN.  Media Cloud is the first plugin for WordPress to bring the full benefit of what Imgix offers - simplifying your development efforts, reducing your site’s page load times and opening up creative options that simply haven’t existed until now.

Media Cloud also provides advanced image editing tools that provide improved cropping options, effects, filters, watermarking and more.

**NOTE**: This plugin requires PHP 5.6x or higher (PHP 7.x preferred)

= Upload to S3, Minio, Google Cloud Storage, Wasabi and Digital Ocean Spaces =
Automatically copy media uploads to the cloud and serve them directly from your cloud storage provider, CloudFront or any other CDN.  Additionally, easily import your existing media library with the push of a button.

= Integrate with Imgix =
[Imgix](https://imgix.com) will radically change the way that you build your WordPress sites and themes.  This plugin is the best integration available for WordPress.  Upload your images to S3 with our S3 tool and then host the media with Imgix, providing you with real-time image processing and automatic format delivery.  Forget ImageMagick, Imgix is light years ahead in quality and speed.

= Native support for Google Cloud Storage =
Now supports using Google Cloud Storage for uploads without having to use Google's S3 compatible interop mode.

= Automatically Tag and Categorize with Amazon Rekognition =
Use Amazon's latest AI tools to tag and categorize your images when uploading to Amazon S3.  With Rekognition, you can automatically detect objects, scenes, and faces in images.

= Advanced Image Editing =
When integrating with [Imgix](https://imgix.com), Media Cloud by ILAB provides the most advanced WordPress image editor.  Alter contrast, saturation, vibrancy and over 30 other image editing operations - in real time right inside the WordPress admin interface!  Completely non-destructive!

= Image Cropping =
Media Cloud by ILAB ships with the most advanced image cropping tool available for WordPress, based on Chen Fengyuan's amazing Cropper plugin for jQuery.

= WP-CLI Support =
Import your media library to the cloud, regenerate thumbnails and process your library with Amazon Rekognition using WP-CLI commands.

= Compatible With Leading Image Optimizers =
Compatible with Short Pixel, EWWW, Smush and Imagify image optimization plugins!

* Upload to any of a variety of cloud storage providers (Amazon S3, Google Cloud Storage, Minio, Wasabi, Backblaze, DigitalOcean Spaces or any other S3 compatible service)
* Host your media directly from your cloud storage provider or specify a CDN
* Import your existing library to cloud storage
* Integrate with Imgix media hosting
* Use Amazon Rekognition to automatically tag and categorize images
* Use third party cloud file providers that use S3 compatible APIs
* Advanced image cropping tool
* Advanced image editing with saturation, contrast, auto-correct,
  sharpen, blur and more (only when integrated with Imgix)

= Premium Upgrade with Improved Support Options and More Features =

* Direct uploads integrated directly into WordPress's media library
* Cloud storage browser that allows you to import media to your media library from the cloud
* Dynamic Images provides Imgix-like dynamic image generation directly in the plugin, no external service needed.
* WPML, WooCommerce and Easy Digital Downloads integration
* Push/pull your CSS and JS assets to the cloud and serve them from a CDN
* Use Google Cloud Vision as a vision provider
* Image size manager
* Network level multisite support

[Compare the premium plans](https://mediacloud.press/comparison/)

== Frequently Asked Questions ==

= Upgrading from 3.x from 2.x =

If you are using environment variables, please refer to [this documentation](https://mediacloud.press/documentation/advanced/environment-variables) for new environment variable names as many have been deprecated.

Additionally a number of hooks and actions have been deprecated, please refer to [the documentation](https://mediacloud.press/documentation/advanced/hooks) for more information.

= How does this compare to WP Offload S3? =

This essentially does everything that WP Offload S3 does but is free.  It includes an import function for importing your current library to S3 that only the pro version of WP Offload S3 has.  Otherwise, they work almost exactly the same.

= Why should I use Imgix? =

One of the headaches of managing a WordPress site is dealing with server disk space.  If you just use the S3 functionality of this plugin, you are already one step ahead.  Using S3, all of your media is centrally located in one place that you can then distribute through a high performing content delivery network to improve page load speeds for your site.  You also don't have to worry about disk space on your servers anymore.

Imgix is a content delivery network with a twist.  In addition to distributing your media, it also allows you to edit them, in real-time. and deliver the edited version through their CDN without altering the original.  Want to add a new image size to your theme?  You can do this with Imgix without having to use a plugin to recut all of your existing media to this new size.  Imgix optimizes format delivery and a bunch of other things.  It's seriously the greatest thing to happen to WordPress and web development in the history of ever.

= Are you a paid shill for Imgix? =

No, I'm just one very enthusiastic customer.

== Screenshots ==

1. Media Cloud integration with the WordPress Media library.
2. Media Cloud integration WordPress Media library list view.
3. Easy image cropping for all croppable image sizes defined in your theme.
4. Make adjustments to your images like saturation, vibrancy, contrast when using Imgix.
5. Stylize your images easily when using Imgix.
6. Bulk import your assets to cloud storage (a free feature in Media Cloud that costs money in other offload media plugins)
7. Watermarking is easy and non-destructive, change the watermark at any time and all watermarked images will automatically update.
8. Redesign settings.
9. Easily pin frequently accessed settings.


== Changelog ==

= 3.1.2 =

* Fix for blank settings pages that would appear on some hosting providers.
* Fixed bugs when Media Cloud is being used on a C-Panel/WHM managed servers.
* Fixed background processing when "Skip DNS" is enabled on C-Panel/WHM managed servers.
* Troubleshooter tool has been renamed System Compatibility Test.
* Running the system compatibility test will automatically tweak background processing settings until it finds a configuration that works.
* Ability to sort the media to be imported when using the Migrate to Cloud tool
* Fix for some hosts that have `allow_url_fopen` disabled
* Added 'Unlink From Cloud' bulk action that will remove Media Cloud metadata from selected files in the Media Library list view
* Fix for compatibility with Offload Media where the url contained an errant '-'


= 3.1.1 =

* Fixes for multi-site
* General bug fixes
* When transitioning from 2.x to 3.x, Media Cloud used to delete the old 2.x settings after copying them to the renamed 3.x settings.  This made it impossible to go back to 2.x without having to re-enter all of your settings.  The migration process no longer deletes your old 2.x settings.

= 3.1.0 =

* Backblaze support re-added.  Note that the *asset push* and *direct upload* features do not work with Backblaze.
* Added option to Migrate to Storage to skip uploading thumbnails. This option requires Imgix or Dynamic Images and will only appear if either is enabled.
* Added option to Migrate to Storage to control how upload paths are handled. This option requires that you have a custom prefix defined in Cloud Storage settings and will only appear if you do.
* Fixes for Migrate to Storage when run in ajax (non-background) mode.
* Updated migrate command line command to include switches for skipping thumbnails and handling upload paths.
* For more information about this release: [3.1.0 Release Notes](https://talk.mediacloud.press/topic/40/3-1-0-release-notes)

= 3.0.9 =

* Updated S3 regions
* Fix for duplicate thumbnail during import process (thanks @jeryj)
* You can now compress assets with gzip before pushing them to cloud storage (pro version)
* Bug fixes for Google Cloud Storage

= 3.0.7 =

* **IMPORTANT**  If you are using Backblaze, please do not update until the Backblaze addon has been approved by WordPress.org
* Over 100 bug fixes and general improvements
* Improved integration with Smart Slider 3, Master Slide, NextGEN Gallery
* Backblaze support removed from main plugin and provided as a free add-on (waiting for WordPress.org review)
* Crop tool now allows aspect ratio to be toggled on or off when cropping
* Redesigned settings
* Improved customizer support
* Added `unlink` WP-CLI command for unlinking the media library from cloud storage
* Batch settings for controlling how background batch processes run
* Extensive documentation
* Improved image optimizer integration
* Allow default credential provider to be used with S3

= 2.1.23 =
* Fix for non-image uploads not uploading
* Fix for the storage importer skipping audio or video items when importing

= 2.1.22 =
* Fix for thumbnails not being uploaded when using a custom `upload_dir` filter
* Fix for situations where your storage credentials don't work but you are unable to get Media Cloud "unstuck" from thinking the settings are bad
* Fix for the troubleshooter tool not being available if storage settings are turned off

= 2.1.21 =
* Debug logger will now log all php errors and warnings
* If you add a filter for WordPress's `upload_dir`, Media Cloud would ignore it.  It still does, but you can make it honor it by setting the Upload File Prefix setting to an empty value or return FALSE from the new `ilab_storage_should_use_custom_prefix` filter.

= 2.1.20 =
* Fixed EWWW Image Optimizer compatibility
* Fixed compatibility with Offload S3 when transition to Media Cloud from that plugin
* Fixed a bug for when you have the `wp-cli/wp-cli` package installed via composer when using Bedrock

= 2.1.19 =
* Completely revamped the importer, thumbnail regeneration and batch rekognition processor.
* Added a setting in Storage Settings that allows you to specify the connection timeout for background processing for the importer and other batch tools.  This could be helpful in some hosting situations.  If you've been getting the `cURL 28` error, try increasing this setting.
* Added a setting in Storage Settings to do the batch importing in the browser, instead of in the background on the server.  This is more reliable as the browser is directing the batch, **HOWEVER**, you cannot close the browser window while a batch is running.  If you cannot get background batch processing working, turn this on.
* Revamped the importer interface.  It now shows the thumbnail of the image that it currently being processed.


= 2.1.18 =
* PHP 5.6 related fixes.
* Added PHP version compatibilty check to the Troubleshooter tool
* The Troubleshooter tool is always enabled (as long as Storage is enabled)

= 2.1.17 =
* Added toggle to serve images from your storage provider using signed URLs (S3 only right now)
* Add option to serve GIFs from S3 (or whatever storage provider) instead of Imgix, if you have Imgix enabled.  Animated GIFs are a premium Imgix feature, so this option works around that a little.

= 2.1.16 =
* Fix for image_intermediate_size for imgix URLs (thanks Tobias Alex-Petersen)
* Fix for image scaling (thanks Tobias Alex-Petersen)
* Support for WordPress's crop positions (thanks Zac M-W)
* Support for defining default imgix parameters for sizes. Instead of using `add_image_size($name, $width, $height, $crop)`, use `addImigixImageSize($name, $width, $height, $crop, $imgixParams)`.
* System report now includes active plugins and active must use plugins
* Before running the importer, or bulk rebuilding thumbnails, first test that we can access the server without any issues.  If there is an issue it is displayed as an error to the end user.
* Added Troubleshooter page that will help you troubleshoot any basic setup problems you might be having.  To turn on, enabled Media Cloud Debugging and then navigate to "Troubleshooter" in the admin navigation.


= 2.1.15 =
* Added compatibility for ShortPixel Image Optimizer
* Added compatibility for EWW Image Optimizer
* Added compatibility for Smush Image Optimizer (Note: Smush is not compatible with Media Cloud when using Imgix).
* Added compatibility for Imagify Image Optimizer (The best we tested, btw)
* Ability to keep WordPress generated thumbnails when using Imgix

= 2.1.14 =
* Media Cloud debugging now logs to database, no need to configure php.ini.
* Improved stablity of the importer.  Even if one item fails to import, the rest should continue processing.

= 2.1.13 =
* Skipped for superstitious reasons

= 2.1.12 =
* Version bump to match github

= 2.1.11 =
* Fixed a cropping related bug

= 2.1.10 =
* Using WordPress's image editor now works with Media Cloud.  Crops, rotations, etc. will be re-uploaded to S3.
* Added more Rekognition regions
* When theme or plugin zip files are uploaded, skip handling them with Media Cloud

= 2.1.8 =
* Compatibility fixes for Foo Gallery, Master Slider, Photo Gallery, and Smart Slider 3
* Added warning and error notices for plugins that are incompatible with Media Cloud: NextGEN Gallery, Smush.it, ShortPixel Image Optimizer, MetaSlider, Imagify, and EWWW Image Optimizer.
* Updated FAQ

= 2.1.7 =
* Fix for bbPress front-end user uploads.  Should fix issues with other front-end user uploads too.
* Added warning for BuddyPress.  Imgix features of the plugin and BuddyPress are not compatible.  BuddyPress does some strange upload stuff and is a convoluted mess.  I tried, but I gave up.

= 2.1.6 =
* Fix for importer.  Imported file's directory structure is retained when importing into S3.
* Fix for compatibility with WooCommerce and a lot of other plugins


= 2.1.5 =
* Fix for crop tool

= 2.1.4 =
* Fix URL for PDF uploads

= 2.1.2 =
* Updated PDF library
* Added better Google Cloud instructions (thanks @michaeljberry)
* Fix ILAB_AWS_S3_BUCKET_PREFIX environment variable (thanks @JulienMelissas)

= 2.1.1 =
* Added 'flip' imgix parameter to the image editor

= 2.1.0 =
* Added support for Wasabi (https://wasabi.com) storage service
* Direct uploads via Wasabi (requires Imgix)
* Fix for direct uploads failing
* Fix for CSS issues on settings pages

= 2.0.9 =
* Added support for detecting faces using Imgix's API.  You can enable this setting in the Imgix settings.  With the detected faces, you can use those in the `Focus Crop` settings of the image editor.  **Note** that Rekognition does a vastly superior job in detecting faces (sorry Imgix) and I would urge you to use that instead.  But if you aren't using Amazon S3 for storage, you'll have to use the Imgix face detection if you want to use the Focus Crop feature.
* Added support for Imgix's Entropy and Edges cropping modes (https://docs.imgix.com/apis/url/size/crop).  You can access these in the image editor.

= 2.0.8 =
* Added support for Imgix's Focal Point Cropping (read about it here: https://docs.imgix.com/apis/url/size/crop).  Right now it only supports focal cropping to a point, however it can use faces that have been detected with the Amazon Rekognition feature of the plugin.  Yes, I know, Imgix has this feature using their face detection and I will add it in the future!
* To use the new Focal Point Cropping, click on `Edit Image` in attachment details to open up the Imgix Image Editor.  Click on the "Focus Crop" tab and go to town.

= 2.0.7 =
* Added WP-CLI support
* Added WP-CLI command `wp mediacloud import` to import the media library to cloud storage from the command line.
* Added WP-CLI command `wp mediacloud regenerate` to rebuild thumbnails for cloud storage from the command line.
* Added WP-CLI command `wp rekognition process` to run the media library through Amazon Rekognition from the command line.

= 2.0.6 =
* Fix for PHP 5.6.  Was using a couple of PHP 7.x features.
* Slimmed down the size of the plugin.

= 2.0.5 =
* Fix for uploading non-image files.  This bug was introduced in 2.0.3.  If you've uploaded non-image files since then, you'll need to re-upload them.  Sorry!
* Hovering over the cloud icon when in the media library grid will display storage info about that file

= 2.0.4 =
* Greatly enhanced the Storage Info panel on the attachment details edit page.  This now shows you all of the different sizes that have been generated, as well as any that might be missing.
* Added the ability to rebuild thumbnails.  If you are using Imgix, this is unnecessary.  If you aren't using Imgix, you should be using Imgix.  You're never going to do better than Imgix.  Relying on WordPress to resize your images and then serve those images from a CDN tied to your cloud storage is a better choice than just serving everything from WordPress, but you are still inserting management tasks into your workflow that Imgix alleviates entirely.  Seriously, check it out.  If you still want to persist with this madness, then this new thumbnail regeneration feature is built just for you.  Enjoy.
* Added "Regenerate Thumbnails" bulk action to WordPress's media library list view.  Use Imgix and you'll never have to worry about this stuff ever again.

= 2.0.3 =
* Fix for bug when deleting from remote storage
* Fix for CDN URL not being applied
* Documentation fix
* Settings clean up

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
