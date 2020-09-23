=== Media Cloud for Amazon S3, Imgix, Google Cloud Storage, DigitalOcean Spaces and more ===
Contributors: mediacloud, interfacelab, freemius
Tags: offload, amazon, s3, imgix, uploads, video, video encoding, google cloud storage, digital ocean spaces, wasabi, media, cdn, rekognition, cloudfront, images, crop, image editing, image editor, optimize, image optimization, media library, offload, offload s3, filepicker, smush, imagify, shortpixel
Requires at least: 4.9
Tested up to: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Stable tag: 4.1.7
Requires PHP: 7.1

Automatically store media on Amazon S3, Google Cloud Storage, DigitalOcean Spaces + others. Serve CSS/JS assets through CDNs.  Integrate with Imgix.

== Description ==

https://www.youtube.com/watch?v=3tB3rKkwAJY

Media cloud is a revolutionary plug-in for WordPress that will supercharge the performance of your website and radically transform the way that you work with media in WordPress.

Media Cloud works by moving your images, media and other files from your WordPress server to online cloud storage such as Amazon S3, Google Cloud Storage, DigitalOcean Spaces, DreamHost Object Storage and many others.  You can then serve that media through a CDN like Amazon Cloud front, Cloudflare, Fastly and others.

Beyond cloud storage, Media Cloud also has deep integration with Imgix, the leading real-time image manipulation and optimization CDN.  Media Cloud is the first plugin for WordPress to bring the full benefit of what Imgix offers - simplifying your development efforts, reducing your site’s page load times and opening up creative options that simply haven’t existed until now.

Media Cloud also provides advanced image editing tools that provide improved cropping options, effects, filters, watermarking and more.

**NOTE**: This plugin requires PHP 7.1 or higher

= Upload to S3, Minio, Google Cloud Storage, Wasabi, Digital Ocean Spaces, DreamHost Object Storage and others =
Automatically copy media uploads to the cloud and serve them directly from your cloud storage provider, CloudFront or any other CDN.

= Video Encoding with Mux =
Upload videos and encode them nearly instantly to adaptive bitrate HLS that plays back smoothly and beautifully on any device regardless of bandwidth.  Requires an account with [Mux](https://mux.com).

= Integrate with Imgix =
[Imgix](https://imgix.com) will radically change the way that you build your WordPress sites and themes.  This plugin is the best integration available for WordPress.  Upload your images to S3 with our S3 tool and then host the media with Imgix, providing you with real-time image processing and automatic format delivery.  Forget ImageMagick, Imgix is light years ahead in quality and speed.

= Native support for Google Cloud Storage =
Now supports using Google Cloud Storage for uploads without having to use Google's S3 compatible interop mode.

= Automatically Tag, Categorize and Caption with Amazon Rekognition =
Use Amazon's latest AI tools to tag and categorize your images when uploading to Amazon S3.  With Rekognition, you can automatically detect objects, scenes, and faces in images.

= Advanced Image Editing =
When integrating with [Imgix](https://imgix.com), Media Cloud by ILAB provides the most advanced WordPress image editor.  Alter contrast, saturation, vibrancy and over 30 other image editing operations - in real time right inside the WordPress admin interface!  Completely non-destructive!

= Image Cropping =
Media Cloud by ILAB ships with the most advanced image cropping tool available for WordPress, based on Chen Fengyuan's amazing Cropper plugin for jQuery.

= Compatible With Leading Image Optimizers =
Compatible with Short Pixel, EWWW, Smush and Imagify image optimization plugins!

* Upload to any of a variety of cloud storage providers (Amazon S3, Google Cloud Storage, Minio, Wasabi, Backblaze, DigitalOcean Spaces or any other S3 compatible service)
* Host your media directly from your cloud storage provider or specify a CDN
* Integrate with Imgix media hosting
* Use Amazon Rekognition to automatically tag and categorize images
* Use third party cloud file providers that use S3 compatible APIs
* Advanced image cropping tool
* Advanced image editing with saturation, contrast, auto-correct,
  sharpen, blur and more (only when integrated with Imgix)
* Automatically import your settings from WP Offload Media and WP-Stateless

= Premium Upgrade with Improved Support Options and More Features =

* Built-in image optimization using leading image optimization services like ShortPixel, TinyPNG, Imagify and Kraken.io.  No more third party plugins needed because it's built into Media Cloud's process.
* Advanced security for encoded videos and a feature rich video player
* Easily import your existing media library with the push of a button
* WP-CLI support: Import your media library to the cloud, regenerate thumbnails and process your library with Amazon Rekognition using WP-CLI commands.
* Direct uploads integrated directly into WordPress's media library
* Cloud storage browser that allows you to import media to your media library from the cloud
* WPML, WooCommerce and Easy Digital Downloads integration
* Blubrry Pod Casting, Ultimate Membership integrations
* Push/pull your CSS and JS assets to the cloud and serve them from a CDN
* Use Google Cloud Vision as a computer vision provider
* Image size manager
* Network level multisite support
* and more!

[Compare the premium plans](https://mediacloud.press/comparison/)

== Frequently Asked Questions ==

= How does this compare to WP Offload Media? =

WP Offload Media provides a very small subset of everything Media Cloud provides.

This plugin is an essential part of our own development stack when creating WordPress solutions for our clients and as client needs grow around media, and dealing with media in WordPress, Media Cloud gains new features and improvements.

= Why should I use Imgix? =

One of the headaches of managing a WordPress site is dealing with server disk space.  If you just use the S3 functionality of this plugin, you are already one step ahead.  Using S3, all of your media is centrally located in one place that you can then distribute through a high performing content delivery network to improve page load speeds for your site.  You also don't have to worry about disk space on your servers anymore.

Imgix is a content delivery network with a twist.  In addition to distributing your media, it also allows you to edit them, in real-time. and deliver the edited version through their CDN without altering the original.  Want to add a new image size to your theme?  You can do this with Imgix without having to use a plugin to recut all of your existing media to this new size.  Imgix optimizes format delivery and a bunch of other things.  It's seriously the greatest thing to happen to WordPress and web development in the history of ever.


== Screenshots ==

1. Media Cloud integration with the WordPress Media library.
2. Media Cloud integration WordPress Media library list view.
3. Easy image cropping for all croppable image sizes defined in your theme.
4. Make adjustments to your images like saturation, vibrancy, contrast when using Imgix.
5. Stylize your images easily when using Imgix.
6. Watermarking is easy and non-destructive, change the watermark at any time and all watermarked images will automatically update.
7. Redesign settings.
8. Easily pin frequently accessed settings.


== Changelog ==

= 4.1.7 =

- When Debugging is enabled, a log file will be generated next to the CSV report.  This log file includes all the logging
  that would normally be in the Debug Log, but limited to the time period the task was running.  If you are running into
  issues with a task, make sure to turn on Debugging, re-run the task and then attach both the CSV and the log file
  to a support ticket https://support.mediacloud.press/submit-issue/
- The `report` command line command has been renamed to `verify`
- You can run the Verify Library task from the WordPress admin by going to Media Cloud -> Task Manager.  In the
  **Available Tasks** click on **Verify Library**.  When it is done running, a report will be in your
  `WP_CONTENT/mcloud-reports` directory
- Added a Sync Local task that copies down media from cloud storage to your local server.  You can run it from the
  WordPress admin by going to Media Cloud -> Task Manager.  In the **Available Tasks** click on **Sync Local**.
- You can also run the Sync Local task from the command line via `wp mediacloud syncLocal report-filename.csv`
- Fixed a bug with direct uploads where the cloud storage provider wasn't being saved in the cloud metadata.  If you
  run the Verify Library task, Media Cloud will fix the issue with any existing direct uploads in your library.
- Added paging to the `syncLocal` and `verify` command line commands, ex: `wp mediacloud verify verify.csv --limit=100 --page=1`
- Fixed Sync Local, Verify Library and Regenerate Thumbnails to work with Imgix enabled.

= 4.1.4 =

- Fix for Regenerate Thumbnails command, it will first attempt to download the original image, if that can't be found then
  it will use the "scaled" image that WordPress 5.5 generates.
- Added a new command, `wp mediacloud report report-name.csv` that iterates through all of the items in your media library and reports
  on their cloud storage status.
- The Migrate To Cloud, Import From Cloud, Clean Uploads and Regenerate Thumbnails tasks now generate CSV reports when
  they run so you can see more details about what they did.  The reports are located in your
  `WP_CONTENT/mcloud-reports` directory.
- You can toggle task reporting on or off in the Settings -> Batch Processing settings or through the
  `MCLOUD_TASKS_GENERATE_REPORTS` environment variable.  The default value is ON.
- The Migrate To Cloud task has a new toggle *Generate Verification Report* which causes the task to verify that the
  media migrated successfully.   This will generate a report in the aforementioned reports directory.
- The `migrateToCloud` wp-cli command now accepts a `--verify` flag to force verification.

= 4.1.2 =

- Fix for WooCommerce integration with files that have malformed metadata

= 4.1.1 =

- Fix for compatibility with Amp plugin and any other plugin using symfony polyfills.
- Fix for edge case issue where the S3 library was closing a resource stream causing a fatal error.
- Added hooks `media-cloud/tools/added-tools` and `media-cloud/tools/added-tools` for inserting other tools in other plugins into the media cloud menu.
- Fix for Mux database tables failing installation on constricted MySQL systems.
- Only check for Mux database tables if Mux is enabled.
- Fix for front end uploads with some form plugins.


= 4.1.0 =

* All third party libraries Media Cloud is using have been re-namespaced to avoid errors and issues with any other plugins using the same libraries.
* IMPORTANT: The old Backblaze driver is being deprecated, use the Backblaze S3 Compatible driver instead.  The old one will be removed in the next version.
* Added Backblaze S3 Compatible cloud storage driver.  If you are currently using Backblaze, you should migrate to this asap.
* If you are installing via composer and relied on any libraries that Media Cloud was using, you will need to install that package with composer yourself in your own project
* You can now install the premium version via composer.  Log into https://users.freemius.com/ for instructions on how.
* Re-namespaced the plugin code.  If you were using `ILAB\MediaCloud\` anywhere, it's now 'MediaCloud\Plugin\'
* Fixed random "Rekognition requires Amazon S3 Cloud Storage" error notifications
* Fixed computer vision tags being applied to alt, caption and description
* Added ability to download video with mux player
* Mux javascript and CSS is only included if video encoding is enabled
* Video encoding no longer requires cloud storage to work
* Mux icon displays in media library when cloud storage is turned off
* Fix for Mux secure key generation
* Fix for Mux filmstrip generation failing
* Fixed Migrate to Mux task


= 4.0.11 =

* Fix for deprecated `whitelist_options` filter in WP 5.5.
* Fix for uploads not occuring when using EWWW image optimizer and other image optimizers.
* Media Cloud will now warn you if your cloud storage isn't configured for CORS when performing direct uploads
* Fix for ACL error with Wasabi
* Added warning about Autoptimize compatibility
* You can now track Media Cloud development on its public trello board: https://trello.com/b/O0iNw6GL/media-cloud-development

= 4.0.10 =

* Fix for attachment tasks when running from the command line (thanks @yanmorinokamca)

= 4.0.9 =

* Direct uploads are now much faster
* Fix for "Add New" page for uploading media when direct uploads are enabled
* Fix for front-end direct uploads
* New setting in Direct Uploads that controls if thumbnails are generated in the browser when direct uploads for images are enabled.
* Improved compatibility with Dokan multivendor plugin for WooCommerce
* Fix for memory error when direct uploading extremely large images without Imgix.


= 4.0.8 =

* Fix for BuddyPress compatibility
* Fix for WordPress's crappy image editor not saving edited images to cloud storage.
* Fix for Blubrry integration.
* Fix for duplicated Imgix uploads when Keep WordPress Thumbnails is enabled.
* Fix for small images not uploading, or not uploading when no image sizes are defined.
* Fix for duplicated Imgix uploads when the image being uploaded has been resized because of WordPress's dumb big image size threshold "feature".

= 4.0.5 =

* Fix a bug with Imgix and SVGs where SVGs are being rendered as progressive JPEGs.
* Added new option to Imgix to control SVG rendering.  When this new option is enabled, any image size other than 'full' will be rendered as a PNG.  When turned off, the SVG is delivered as is with no conversion.

= 4.0.4 =

* Fix for Ultimate Member uploads

= 4.0.3 =

* IMPORTANT: This plugin now requires PHP 7.1 or better
* IMPORTANT: The Dynamic Images feature has been removed.  For all four of you that were using it, you will want to migrate to Imgix before updating.
* NEW: Video encoding! via mux.com video encoding service turns your WordPress site into your own private Vimeo.  Encodes uploaded videos into adaptive bitrate videos that play smoothly no matter the bandwidth.
* NEW: Gutenberg and Elementor blocks to play videos encoded by Media Cloud.
* NEW: (PREMIUM) Image optimization is now built-in, no third party plugins needed.  Support for ShortPixel, TinyPNG, Kraken.io and Imagify.  Requires an account with any one of those services.
* NEW: (PREMIUM) You can now direct upload images WITHOUT having to use Imgix.  Does not work with Backblaze.
* NEW: Added DreamHost Cloud Storage as a cloud storage option
* Database usage reduced by 40%
* Background tasks are now limited to two concurrent running tasks.  You can adjust this setting in Media Cloud > Settings > Batch Processing.  Previously any number of tasks could run at once which could cause your site to slow down.
* When saving posts or pages with Elementor, and using the Assets feature of Media Cloud, the asset build number will automatically be updated
* Images processed with computer vision can apply the generated keywords to captions and alt.text
* Fix for non-image uploads sometimes failing
* Fix for PDF uploads
* Fix for uploads on multisite with custom prefixes.
* Fix for wizard when activating network.
* Improved compatibility with front-end uploads
* Tasks that make significant changes to your site now prompt you to remind you to backup your database first
* + 48 other fixes and performance improvements


= 3.3.23 =

* Fix for stateless media import

= 3.3.22 =

* Fix for background tasks
* Fix for WooCommerce integration
* Important: After upgrading, go Media Cloud -> Settings -> Batch Processing.  Scroll to the bottom and click on *Clear Background Tokens*.

= 3.3.21 =

* Fix for missing metadata on Direct Uploads
* Fix for WooCommerce integration

= 3.3.20 =

* Fix for Imgix crop data being lost when upgrading metadata.

= 3.3.19 =

* Fix for pull assets not functioning correctly. You should no longer receive an error message and asset pull will work.  All you need to do to enable asset pull is supply the base URL for your CDN in the settings and make sure all of the Push Settings are disabled.

= 3.3.18 =

* Fix for NOTICE "error" when using DigitalOcean or another S3 compatible service
* Fix for Imgix when an image size has been defined with a width or height of zero.
* Ability to override the upload path for cloud uploads via the `media-cloud/storage/custom-prefix` filter.
* When migrating from another plugin like Offload Media or WP-Stateless, you can now choose to manually migrate any media uploaded with the other plugin.  This is a very fast process.  If you do not do the manual migration, media will be migrated the first time a URL for an attachment is generated.  You should choose to do the manual migration though.
* New option in Cloud Storage settings to turn off the automatic migration of media uploaded with another plugin.
* Fix for Regenerate Thumbnails not using the original image with 5.3's big image feature
* Fix for uploads not being deleted from WordPress in certain circumstances
* Fix for duplicates being uploaded to Cloud Storage when the upload path prefix `@{versioning}` is being used
* Fix for some uploads not being deleted when deleted from the WordPress media library
* Support for direct uploading on the front end when using plugins like WC Frontend Manager for WooCommerce.
* BuddyPress integration for uploading profile and cover images on profiles and groups.

= 3.3.14 =

* Fix support knowledge base links for the inline help pop-ups
* Update forum and support links
* Add more information to the generated system report to help troubleshoot issues

= 3.3.12 =

* Fix for non-image uploads when you have the WordPress setting "" turned off but no upload path set in Cloud Storage Settings.  If you were having problems with videos, PDF's, etc. this should fix it.
* Performance fix for sites with huge post tables that were seeing slow page performance.
* Fix for IPTC parsing that contains binary data
* Fix for a library conflict
* Warning that the built-in Dynamic Images functionality will be deprecated in the next major version
* Warning about library incompatibility with TranslatePress
* Migrating media no longer deletes local files unless explicitly enabled.

= 3.3.11 =

* Added us-east-2 region for Wasabi
* You can now bulk change the privacy for a selection of files in your media library.  To do this, switch to list view in the media library, select the images you want to change, select *Change Privacy to Public* or *Change Privacy to Private* from the Bulk Actions dropdown and then click *Apply*.
* Media Library now displays a lock for uploads that are private.
* For private upload the WordPress admin will use signed URLs to display media, but only in the admin (unless you have use presigned URLs enabled).
* Fix for font issue in latest LearnDash


= 3.3.10 =

* CRITICAL FIX.  Previous version introduced a library that could cause issues on some systems, it has been fixed in this version.  Please update ASAP.
* Added new filter, "media-cloud/storage/override-privacy" that allows you to override privacy per upload.

= 3.3.9 =

* Fix for errors on Task Manager pages caused by a library conflict with other plugins.
* When using the post editor after migrating an existing site to cloud storage, images appeared broken if the original images were deleted from the server.  This is now fixed.

= 3.3.7 =

* Massive improvement to background tasks performance.  Processing times reduced by 50 to 90% in most cases.
* Fixed settings toggle in Google Chrome
* Fix for srcset generation when using wp_get_attachment_image() in your theme


= 3.3.6 =

* The @{type} dynamic path prefix now works in all cases
* EDD Free Downloads now triggers a warning instead of an error.  It is usable with Media Cloud, but if you are using Imgix and offering image downloads, it will not work as you intend.
* Fix for server overload when running tasks
* Support for URL signing when using CloudFront
* Revamped Settings UI
* Updated Freemius SDK
* Queued deletes were on by default, they are now off by default

= 3.3.5 =

* Video and audio short tag is now filtered to insure that the correct URL is always being used, vital for signed URLs.
* Fix for importing Offload Media 1.x metadata

= 3.3.4 =

* Critical fix for Minio, DigitalOcean and other S3 compatible services.

= 3.3.3 =

* Fixes for PHP 5.6 - people, it's time to upgrade to PHP 7.x!

= 3.3.2 =

* Fix for illegal offset error warning
* Fix for importing from cloud storage (Premium)

= 3.3.0 =

* Wizards? Wizards! WIZARDS!
* New setup wizards to quickly setup the basic settings you need to get started with Media Cloud
* Ability to specify using expiring signed URLs for different types of media (images, video, audio, docs).  Allows you to upload images and use them without signing, but have signing enabled for videos, audio, etc.  (Premium)
* Ability to specify different upload privacy settings for different types of media.  Allows you to upload images and have them publicly accessible, but keep video, audio, etc. private. (Premium)
* All core Gutenberg blocks now have image or file URLs rewritten dynamically so that they are always correct.
* Ability to control WordPress's 5.3 new "big image" support, including disabling, setting the threshold and uploading the original unscaled image to cloud storage.
* Fix for importing items from Offload Media when migrating to Media Cloud
* Fix for srcset generation in WordPress 5.3
* Fix for Migrate tool not including non-media files in migration (zip, text, etc).
* New configuration toggle for replacing the WordPress generated srcset with a bettter optimized version (WordPress 5.3+ only)
* Update regions for Amazon Rekognition
* New integration with Foo Gallery (Premium)

= 3.2.7 =

* Compatibility with WordPress 5.3
* Fix for Direct Uploads with WordPress 5.3 (Premium)

= 3.2.6 =

* Updated documentation
* Vastly improved NextGen Galleries integration (Premium)
* New Migrate to NextGen Galleries task to import all of your existing NextGen Galleries to cloud storage (Premium)
* Fix for you insane people have that PHP memory limits specified in gigabytes
* Skip memory limit check when running tasks from the command line
* Fix for EU region with Rekognition

= 3.2.3 =

* Fix for asset push when the CSS rule contains a url with a query string
* Fix to insure the task heartbeat only runs once within the given interval, regardless of the number of admins logged into the admin
* Added @{type} variable for upload paths to include the upload type.  For example, if the upload path setting is set to `upload/@{type}` then when uploading an image the upload directory will be `/upload/image/`, or when uploading a video the upload path would be `/upload/video/`, etc.
* The Cloud Storage settings will show you a preview of your upload path when editing it.
* Fix for invalid presign expiration time which was causing direct uploads to fail on multisite.
* For multisite, the ability to specify different upload directories for each subsite.

= 3.2.2 =

* Fix for not activating when installed via Composer
* Removed deprecated filters and actions

= 3.2.1 =

* Fix for task heartbeat
* Optimize asset upload process
* Fix for font assets not being gzipped when pushed to cloud storage
* Fix for when images are in the root of the upload directory, they appeared broken

= 3.2.0 =

* Activating Media Cloud will now import your WP Offload Media or WP-Stateless settings, making the transition as smooth as possible.
* Improved import for media uploaded with WP Offload Media or WP-Stateless
* New and improved background processing system, completely replaced the old error prone one
* New Task Manager shows you all running background tasks and upcoming scheduled ones
* Vastly improved Elementor integration
* Fixed Smush Pro integration (thanks to Brett Porcelli!)
* Asset push now queues uploads in the background instead of during page loads
* Support for "Bucket Only Policy" with Google Cloud Storage (thanks to Wietse Muizelaar!)
* Fix for environment variable MCLOUD_STORAGE_GOOGLE_CREDENTIALS_FILE (thanks to Wietse Muizelaar!)
* When "Delete Uploaded Files" is enabled, deletes can be queued in the background to be deleted in the future.  Allowing other plugins to process the upload before being removed from the local server.
* New "Clean Uploads" task removes media from the local uploads directory.
* Fix for path handling during migration and imports
* Fix for Vision where items were not being queued in a background task
* Fix for upload paths using @{version} token
* New integrated inline help system
* If Assets are enabled, added entries to WordPress admin bar to update build version and clear asset cache
* The `import` command line task renamed to `migrateToStorage`
* Added new `importFromStorage` command line task
* Added new `updateElementor` command line task
* Ability to hide Task Manager on multisite sub-sites
* Improved error reporting for invalid credentials
* Fixed dreaded white screen of doom when invalid cloud storage credentials are supplied
* Fix for blank settings pages in more restrictive server setups


= 3.1.7 =

* Media Cloud will now upload .webp files generated by EWWW image optimizer to cloud storage
* Displays a warning if you don't have the `mbstring` PHP extension installed
* Added Amazons S3 video tutorial
* Fix for environment variable for setting the view cache
* Fixes for Storage Browser when used in Multisite
* Support for Multisite Global Media plugin
* Fix for hiding Media Cloud completely on multisite networks
* Display warning that no Upload Directory has been set in multisite


= 3.1.6 =

* More detailed release notes are available at: https://mediacloud.press/blog/media-cloud-3-1-6-released/
* Ability to specify which type of media are uploaded to cloud storage or not, for example you can configure Media Cloud to only upload audio to cloud storage.
* Ignored mime types in cloud storage settings now supports wildcards, eg `image/*` to disallow image uploads
* Fix for Human Made S3 Uploads migration
* Display relevant warnings for enabled tools that require another tool that is disabled
* When uploading to cloud storage, file size is now recorded properly in attachment metadata
* Updated documentation
* Vision tool has a new option for forcing term counts to update when using tagging.  WordPress usually only counts "attached" attachments in term counts, but this option circumvents that.
* LearnDash integration fixes font errors and allows remote images to be used in certificates (Pro Version)
* Blubrry Pod Casting integration allows you to select media from the WordPress library when creating a new episode (Pro Version)
* Fix for importer ignoring certain mime types (Pro Version)
* Direct uploads no longer require Imgix or Dynamic Images to be enabled (Pro Version)
* Ability to specify what kind of media is uploaded directy to cloud storage and which is uploaded to WordPress (Pro Version)
* For Direct Uploads, ability to specify the maximum allowed upload size (Pro Version)
* When performing a direct upload, Media Cloud first checks that the upload file name is unique, and if not, will add a unique part to the original file name.  (Pro Version)

= 3.1.5 =

* Added `migrateS3Uploads` command line command to migrate uploads from Human Made S3 Uploads plugin
* Rewritten batch tool UI
* Fix for srcset issues with Imgix.
* Fix for system compatibilities test on certain systems.
* Added ability to regenerate thumbnails even when Imgix or Dynamic Images is enabled, previously only available if either was turned off  (Premium Version).
* Fixes for Backblaze
* Fix for Dynamic Images showing a warning about an empty needle
* Fix for Regenerate Image in the cloud info pop-up in the media grid
* New! Import media from cloud storage into WordPress (Premium Version)
* Ultimate Membership integration (Pro Version)
* Added --skip-existing flag to the command line import command (Premium Version).
* Added options for importing files via the cloud storage browser (Pro Version).

= 3.1.3 =

* When the system compatibility test is run, check to see if the server's clock is correct.  An incorrect clock can cause errors with cloud storage APIs.
* Fix for connect timeout of 0
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

* Fix for migrating very large (greater than 10,000 items) media libraries to cloud storage
* Fix for Gutenberg image blocks
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
