=== Media Cloud for Amazon S3, Imgix, Google Cloud Storage, DigitalOcean Spaces and more ===
Contributors: mediacloud, interfacelab, freemius
Tags: offload, amazon, s3, imgix, uploads, video, video encoding, google cloud storage, digital ocean spaces, wasabi, media, cdn, rekognition, cloudfront, images, crop, image editing, image editor, optimize, image optimization, media library, offload, offload s3, filepicker, smush, imagify, shortpixel
Requires at least: 4.9
Tested up to: 5.9.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Stable tag: 4.4.0
Requires PHP: 7.4

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

= 4.4.0 - 4/7/2022 =

* **IMPORTANT**: This release has a breaking change if you are using the **Mux/Video Encoding** feature.  If you are using the video.js or hls.js video player, you will need to enable the separate *Video Player* feature to continue using those players.  There will be a notification in WordPress admin warning you about this, but only if it applies to you.
* Upgraded video.js player to latest 7.19.0
* Upgraded hls.js player to latest 1.1.5
* Separated the video player into it's own feature from the video encoding feature.
* Video player now supports playing any uploaded videos, not just Mux encoded videos.
* You can now allow video downloads for logged in users only in *Video Player Settings*
* The Video Player gutenberg block allows you to override the download setting for an individual video.
* Filmstrip generation can now be enabled regardless of video player in use.

= 4.3.11 - 3/8/2022 =

* Added new option to imgix to disable `urlencode()` the filename which may cause issues for certain unicode characters in filenames.  If imgix is working fine for you, you should not turn this on.
* Fixed missing regions in the setup wizard for Wasabi

= 4.3.9 - 3/4/2022 =

* Fixed Imagify API integration.

= 4.3.8 - 3/2/2022 =

* Update to latest AWS SDK.  Fixes critical issue where deleting items from cloud storage would fail due to a bug in the SDK.  If you are using S3 or S3 compatible storage, you need to upgrade to this.
* Fixed a misspelling in the wizard

= 4.3.7 - 3/1/2022 =

* Various fixes for **BuddyBoss** and **BuddyPress**
* **Migrate to Cloud** task will now copy BuddyBoss media that isn't related to profile images or cover photos, meaning any media uploaded to timelines, groups, forums, etc.  Use the Migrate BuddyPress task for cover photos and profile images.
* Fixed a caching issue for BuddyBoss/BuddyPress where changing cover images where would not update the cover photo.
* Full support for BuddyBoss's video and document uploads.
* **Note** if you want to delete uploads from your local server, you will need to turn on both **Delete Uploads** in **Cloud Storage Settings** and **Delete Uploads** in the BuddyPress integration settings.
* Fixed a bug preventing the background delete task for BuddyPress from working.

= 4.3.6 - 3/1/2022 =

* Critical fix for some libraries that were not being imported correctly.  On some systems this would cause a fatal error depending on what other plugins you had installed.

= 4.3.4 - 2/28/2022 =

* **NOW REQUIRES PHP 7.4**  Installing on PHP < 7.4 will not work and result in errors.
* Fixed an issue that would prevent certain tasks from running
* Sign up to be notified about our new product for WordPress coming in April 2022: [Preflight for WordPress](https://preflight.ju.mp)
* Fixed compatibility with BuddyPress 6.x
* Fixed compatibility with BuddyBoss 1.8.x including video.  **Note:** Mux encoding does not work with BuddyBoss and it's impossible to make it work.  You can have it enabled and Mux will encode videos but the videos that are played on the front end will be the uploaded MP4 source.  It's best to turn Mux off if you are using it with BuddyBoss.
* Added new Wasabi and S3 regions.
* All third party libraries have been updated to the latest versions.
* Fixed MUX gutenberg block registration
* Filmstrip generation with Mux now warns you if GD is not installed
* Media Cloud now makes **EWWW Image Optimizer** run during the upload process which fixes a lot of issues with other plugins like Elementor.  You can disable this in **Cloud Storage Settings** if your uploads have become unbearably slow.
* Fixed compatibility with **EWWW Image Optimizer** bulk optimizer.
* Generated `.webp` file names are stored in S3 metadata.
* `.webp` files are deleted from cloud storage when deleting an upload.
* When queueing deletes, you can now specify the delay in minutes before items in the queue get processed.
* Built-in image optimizer now properly queues deletes if that setting is enabled.
* For the free version, if you are using Elementor with an image optimizer that isn't **EWWW Image Optimizer**, you are going to have problems and that configuration isn't supported.  You should consider a switch to EWWW or upgrading to the premium version.
* For the premium version, if you are using Elementor with an image optimizer that isn't **EWWW Image Optimizer**, make sure to turn on **Queue Deletes** in **Cloud Storage Settings** and **Auto Update Elementor** in **Integration Settings**.
* Fix for images specified in the customizer when using the Astra theme.
* PDF thumbnails generated with the ImageMagick extension are now uploaded to cloud storage properly.
* Fixed an error with PDF uploads and ImageMagick that would prevent the PDF from being uploaded to cloud storage in certain circumstances.
* Fixed the Render PDF functionality of imgix to now properly render PDFs.
* Large PDF uploads would cause a fatal memory error on some systems.  Now, you can now upload PDFs of any size without issues (subject to WordPress and web server limitations).
* PDF upload speed should be greatly improved.  If you don't care about generating PDF preview images, you can improve it even more by turning off **Extract PDF Page Size** in the **Image and PDF Upload Handling** section of **Cloud Storage Settings**.
* Added a new setting **Background Only When Using the Media Library** that, when enabled, limits image optimizations to only run in the background when using the WordPress Media Library pages in the admin, otherwise the image optimizations will run during the upload.  Enabling this could improve compatibility with some plugins.
* Image Optimizations that fail would prevent the upload from being transferred to cloud storage.  That has been fixed.
* Updated the Imagify Image Optimization driver.
* Background optimizations now start a lot a sooner.
* Added **Update URLs** task to search and replace URLs in the WordPress database.
* Added a switch to the **Migrate to Cloud** task that will do a search and replace for migrated URLs during the migration process.
* When files are deleted from the server, everything about the deletion is now logged when debugging is enabled.
* Fixed issues with WordPress 5.8 and 5.9
* Fixed compatibility with PHP 7.4 and 8.0
* Updated latest Freemius SDK

= 4.2.37 - 6/22/2021 =

* Fixed an issue when running tasks that contained no data.
* Fixes for Google Web Stories integration (Premium).

= 4.2.36 - 6/20/2021 =

* Added new options to Rebuild Thumbnails task.  You can now specify which media items get rebuilt; either all of them, only those that are missing sizes or those that are missing specific sizes.
* You can now specify which image sizes get regenerated.  You can specify either all of the image sizes, only the ones missing or a single specific size.
* In **WordPress Admin -> Media Cloud -> Settings** in the **Batch Processing** tab, you can now defer execution of any tasks that you trigger from the bulk action drop-down in the Media Library list view.  This is useful if you need to process a variety of items spread out across multiple pages but don't want to queue a bunch of single tasks to do so.
* The **Fix Cloud Metadata** task will now allow you to specify only to work on items that are missing cloud metadata.
* You can now select which images to process with the **Sync Local** task.
* A new batch action for the **Sync Local** task has added to the Media Library's list view.  It's called **Download to Local Server**
* The Media Cloud task heartbeat will now only be sent one time per user per browser regardless of the number of open tabs or windows.  Previously, if an administrator had 15 tabs open in the same browser, it would send 15 heartbeats.  Now only 1 tab will send a heartbeat and when that tab is closed, another open tab will take over sending the heartbeat.  Note that heartbeats are only sent by administrator level users or users with the `mcloud_heartbeat` permission.
* Fixed a javascript error with the real time debug log viewer.
* Rebuild thumbnails will now correctly rebuild thumbnails when Imgix is enabled.
* Minor UI tweaks.

= 4.2.35 - 6/17/2021 =

* Fixed Replace Image functionality which would fail to work on certain versions of MySQL due to an SQL query being used being not compatible.
* Added a drop down to control how tags are managed when replacing an image.  Controls if they should be merged, replaced or do nothing.  Default is Replace.
* Replacing an image now replaces the title of the attachment.
* Removed option to delete media after migration because too many people were shooting themselves in the foot.  After a successful migration, it's important you check to make sure your media has been migrated successfully and then run a Clean Uploads task after to remove files from your server.
* Removed `--delete-migrated` from `wp mediacloud:storage migrateToCloud` command.
* Fixed a warning for `Logger.php`

= 4.2.34 - 6/17/2021 =

* Added a toggle to make the **Debug Log** display log entries in realtime.
* Added a toggle to **Batch Processing** settings that allows you to disable your theme and any plugins when processing items in the background.  Turning this on will not effect your front-end or WordPress admin, it's only applied when Media Cloud is processing a task in the background.  If you are having issues running the migration or import task, try enabling this option.

= 4.2.33 - 6/15/2021 =

* Fix for video and audio short codes using Classic Editor when using a storage provider with a path style endpoint and pre-signed URLs.
* New warning that you are using a path style endpoint with DigitalOcean when you don't need to.  The only time you'd need to do that is if your bucket contains a period, for example your bucket's name is `my.bucket.is.cool`.
* Fix for php NOTICE warnings for imgix images with malformed metadata.

= 4.2.32 - 6/13/2021 =

* Fix for **Import from Cloud** task where it would show an error that there was nothing to import.
* Fix for tasks not updating the progress UI in certain instances.
* Fix for errors with the `set_time_limit()` function on systems where that function is disabled.
* Fix for error with Smart Slider integration.

= 4.2.31 - 6/8/2021  =

* Fix for Imgix with BuddyPress avatars and cover images. (Premium)
* Made the setting **Replace srcset on image tags** disabled by default.  Will be removed in future versions of Media Cloud.
* Added a warning if you have **Replace srcset on image tags** enabled.
* **Cloud Tools** menu renamed **Cloud Tasks**.
* Fix for custom defined image sizes in the **Image Size Manager** not showing up in the WordPress media selector. (Premium)


= 4.2.30 - 6/7/2021  =

* Complete overhaul of BuddyPress and BuddyBoss integration. (Premium)
* Added a new *Migrate BuddyPress Uploads* task which will migrate existing avatar and cover images to cloud storage.  Previously, Media Cloud would migrate these as they were requested on the front end.  (Premium)
* Added a new WP-ClI command, `wp mediacloud:buddypress migrate` that wraps the *Migrate BuddyPress Uploads* task.  (Premium)
* Renamed Computer Vision WP-CLI command from `wp vision` to `wp mediacloud:vision`.
* Renamed the task manager WP-CLI command from `wp taskmanager` to `wp mediacloud:tasks`.
* Fixed bug for when you have privacy for uploads set to private, but don't have signing enabled, the error message wasn't dismissible.
* Added new setting *Enable Real Time Processing* to BuddyPress integration that controls the real-time uploading of avatar and cover images.  When disabled, you must run the *Migrate BuddyPress Uploads* task manually to upload these things to cloud storage.  (Premium)
* Fixed compatibility with rtMedia for BuddyPress.  (Premium)
* Fixed the `mediacloud:storage replace` command to search all wordpress tables, including custom ones.  (Premium)
* **Note:** if you are using rtMedia with BuddyPress, you will need to run the CLI command `mediacloud:storage replace` after running the *Migrate to Cloud* task.  You will only need to do this once.  (Premium)

= 4.2.29 - 5/25/2021  =

* Fix for `x-amz-bucket-region` notices
* Easy Digital Downloads now download as files instead of opening as images or videos in the browser. (Premium)


= 4.2.28 - 5/14/2021  =

* Fix for NOTICE errors with srcset generation

= 4.2.27 - 5/8/2021 =

* Fix for potential fatal crash with certain integrations

= 4.2.26 - 5/8/2021  =

* Fix for compatibility with Root's Sage theme framework
* HOT FIX: Fix for fatal error if Beaver Builder Pro is installed and Compatibility Manager is enabled.
* Fix for EDD integration with variable product pricing
* Added option to EDD integration that enables downloading the original unscaled image when the download is an image.

= 4.2.23 - 5/5/2021 =

* More fixes for srcset generation.
* Ability to turn off `ixlib` and `wpsize` query parameters for imgix image URLs.  To disable these query parameters, toggle *Remove Extra Query Variables* off in Imgix settings.
* You can now specify the default cropping mode and crop origin for imgix images in the *Imgix Settings*.  This crop mode and origin will be overridden for manually cropped images or images that have had their crop mode set in the *Image Editor*.

= 4.2.22 - 5/3/2021  =

* Fix for srcset generation with Imgix.
* *System Check* has been renamed *System Test*
* Added a plugin/theme check to the *System Test* that pinpoints any *potential* (emphasis on potential) issues with activated plugins or your current theme.
* Added a new *Compatibility Manager* tool that allows you to disable hooks in other plugins or themes that might be causing issues with Media Cloud.  You must enable this tool in Cloud Storage Settings in the Advanced Settings panel.  Once activated, this tool will show you all the hooks that are activated on your WordPress install that might interfere with Media Cloud.  **Note that just because a plugin or theme shows up in the list, this does not mean it's incompatible.  You should only use this tool if directed by Media Cloud support**.
* Cleaned up the *Debug Log* UI
* The *System Test* now allows you to run a single specific test instead of having to run all tests every time.

= 4.2.20 - 4/17/2021  =

* Added SFO3 region to DigitalOcean setup wizard
* Added a new top level menu item to WordPress admin called *Cloud Tools* that contains all of Media Cloud's tools and tasks.  The main Media Cloud menu was getting way too large.  This only affects non-multisite WordPress sites.
* You can turn off the *Cloud Tools* menu, reverting to previous behavior, in *Cloud Storage Settings* in the *Display Settings* section.

= 4.2.18 - 4/15/2021  =

* New feature allows you to upload a new image file to replace an existing one. (Premium Only)
* Added buttons to various media screens to regenerate thumbnails for the media being viewed. (Premium only)
* Added a metadata panel to the attachment edit page that allows you to view and edit the cloud storage metadata for images, as well as attempt to automatically fix any issues.
* Additionally, the metadata panel will "audit" the attachment and show you any potential issues (missing local file, etc).
* Added a **Fix Metadata** task that will attempt to fix any cloud storage metadata issues with items in your media library.
* Added `media-cloud/storage/prefix` filter for adding your own custom tokens to the upload path.  See an example here: https://gist.github.com/jawngee/f01c74f781b4e8cd4a6d40983e626b99
* Added a regex filter to debug logging to skip logging any unwanted messages.
* Fixed placement of Storage Info popup in the Media Library grid mode.
* Fixed a visual feedback bug where Direct Uploads appeared to not have finished uploading even though they had.

= 4.2.11 - 4/9/2021  =

* Fix for Elementor Update task on unicode/utf-8 pages.
* Debug log can now be filtered and searched
* Insure logging is using appropriate logging levels

= 4.2.10 - 4/8/2021  =

* Added test to system check to insure that required database tables are installed.

= 4.2.9 - 3/31/2021  =

* Fix for potential performance issue on the front end for busy sites.
* Fix for audio and video shortcodes for signed video URLs.
* Fix for error when pushing js/css assets to cloud storage.

= 4.2.8 - 3/16/2021  =

* **Critical Fix** - Fixes missing class file for the free version that was accidentally excluded by our build system.
  If you updated to 4.2.7, you must update to 4.2.8, otherwise uploads will fail.  If you are using the premium version,
  this does not affect you.

= 4.2.7 - 3/15/2021  =

* You can specify different privacy levels to different image sizes defined in your theme using the Image Size Manager.
  This is useful if you are selling stock photos and want to make high-res variations private until sale.
* Added a new setting for Imgix, **Serve Private Images**.  When enabled, private images, or image sizes that have had
  their privacy level set to private, will be rendered through imgix.  When disabled, any private images or private
  image sizes will be served from cloud storage using signed URLs, if that's enabled.
* If you change the privacy for an image size, make sure to run the **Update Image Privacy** task that can be found in
  the **Task Manager**.
* Fix for direct uploads when the upload doesn't have a mime type, for example .R3D files.  You may need to add
  these mime types to WordPress to allow uploads though.
* Fix for direct uploads with DigitalOcean
* Added `media-cloud/storage/sign-url` filter to disable pre-signing image URLs in the WordPress admin.  This is very edge
  case, so you should only use this if support directs you to, or you know what you are doing.

= 4.2.6 - 2/15/2021  =

* Fixes for direct uploads for huge image files
* Fix for hyperdb not storing null values
* Fix for support links
* Fixes for migrate task
* Updated to latest Freemius SDK


= 4.2.5 - 12/31/2020  =

* Fixes for migration task
* Fix redeclared function error when using as a composer dependency
* Fix for bug introduced 4.2.2
* Fix for PHP 7.4 type errors
* Fix for incorrect imgix URL generation
* Massive overhaul of Elementor integration.  Media Cloud now will support any Elementor addon and running the
  **Elementor Update Task** is super safe.  Instead of the brute force text search/replace we were doing before, we are
  now analyzing every installed Elementor widget's properties for images to update and then walking your Elementor post
  or page's structure to update accordingly.
* Rewrote the Storage Browser from the ground up.  Much much much faster and can now handle buckets of any size and
  file count.
* **Update Elementor** task now generates a report on what was replaced/updated on your Elementor pages/posts.
* Support for custom sizes in images in Elementor.
* New integration for Google Web Stories.  If you use Google Web Stories, you may need to run the "Update Web Stories"
  task to make sure all of your URLs are correct.  You should only have to run this task once, though you will need to
  run it again anytime you change Media Cloud's config in such a way that it changes the URLs to your images.
* Fix for "dynamically" resized images using Imgix that ignored the image's default parameters set in the image editor.
* Fix for URL replacement where two or more of the same images at different sizes were being replaced by a single size
  when using the classic editor.
* When adding media to a post that links to the original media, we are now replacing those URLs as well.  You can turn
  this off in **Cloud Storage Settings** in the **URL Replacement** category.
* Disable optimizers during migrate task to prevent certain images from being skipped.
* Fix for built-in ShortPixel optimization
* Fixed Unlink From Cloud task not scrubbing all S3 metadata
* Fix for imgix urls being double urlencoded
* Fix for image sizes in gallery blocks in Gutenberg
* PHP 8 compatibility fixes
* Fix for direct uploads going to unicode directory names
* CLI commands have been "namespaced" and some names have changed.  For example, what used to be
  `wp mediacloud migrateToCloud` is now `wp mediacloud:storage migrate`.
* The Update Elementor command line is now `wp mediacloud:elementor update`
* Verify Library task can now be accessed from the menu
* Verify Library task has a new option, **Include Local** that will verify everything in your Media Library, including
  items that are not on cloud storage.  This is a good first step prior to migrating your library for the first time to
  see if you are missing any local files which may cause your migration to fail.
* Added new setting toggle **Replace URLs** in **Cloud Storage Settings** that allows you to turn off Media Cloud's
  realtime URL replacement.  If you've been using Media Cloud since day zero of your WordPress site (you haven't
  migrated an existing site) and haven't changed any settings which would change the URL for your cloud storage since
  then (for example adding a CDN or enabling imgix), you may be able to turn this setting off.   Note that the URL
  replacement overhead is very minimal to begin with so the potential performance gain won't be noticeable in most cases.
* When Debugging is enabled, Media Cloud will now log to Query Monitor's log if you have that plugin installed
  and activated.
* Added **Reset Task Data** to the Task Manager to clear out all task data from the database, aka the nuclear option.
* Added Report Viewer to view the reports that the various tasks that Media Cloud runs produces.
* Added new CLI command `wp mediacloud:storage replace` which will search and replace URLs in your database.  When used
  in conjunction with the new **Replace URLs** toggle in **Cloud Storage Settings** you can minimize all of the work
  that Media Cloud is doing on the front end to make sure that URLs are correct.  ***There are a lot of caveats to using
  this command***.  Please read this article for more information:
  https://support.mediacloud.press/articles/advanced-usage/command-line/replace-urls
* Compatibility fixes with HyperDB and LudicrousDB
* Fix for settings sometimes not being saved when using Redis object caching
* The Media Cloud heartbeat now only runs for administrators or users who have the `mcloud_heartbeat` capability

= 4.1.14 - 12/3/2020 =

* Added missing instructions that caused errors on multisite installs.
* Added `privacy` ACL to cloud storage uploads.  Since the first days of Media Cloud, we've been using the `authenticated-read`
  ACL for private cloud storage uploads.  There was a historical reason for that and functionally there is no difference
  between `authenticated-read` and `private` ACLs for nearly all cloud storage providers.  If you are using Scaleway and have
  private uploads enabled, make sure you change the privacy settings from Authenticated Read to Private.  For other
  cloud storage providers you do not need to do this, but we suggest you do.  You will also need to change the privacy on
  any existing private uploads by going to the Media Library, switching to list view, filtering on `Authenticated Read`
  and then doing a bulk action `Change Privacy to Private`.
* Added the ability to specify a custom region.  Mostly useful for S3 compatible cloud storage providers, but also useful
  when Amazon released a new region and their is a lag time for us updating our list.
* Fixed changing ACL on the attachment edit screen to update the ACL for any generated image sizes.
* Fix for direct uploading non-image files when using the "Add New" page in WordPress admin.
* Added a new debugging mode that will generate a CSV log file of URL replacements on a given page.  Enable it by going to
  **Media Cloud -> Debug Settings** and toggle on **Debug Content Filtering**.  Visit a page that you are having issues
  with and a log file will be generated in your `WP-CONTENT/mcloud-reports` directory.  DO NOT LEAVE THIS RUNNING.  Turn
  it on, load your page, turn it off.  Note that this CSV file only logs URL replacements that happen in the content of
  a post, it will not log URL replacements that happen elsewhere in your page that are the result of calling functions
  like `wp_get_image_src()` in your templates.
* Fixed URL replacements for video and audio shortcodes (videos inserted with classic editor or via the [video] shortcode).
* Last day to use BLACKFRIDAY2020 for 33% off annual licenses, ends December 3rd!

= 4.1.9 - 11/30/2020 =

* Fix for when using the ShortPixel plugin and have Media Cloud configured **NOT** to upload PDFs to cloud storage.
* Fix for unlinking non-image files.
* Unlink task now generates a report which you can find in your `WP_CONTENT/mcloud-reports` directory.
* You still have 3 days to use BLACKFRIDAY2020 for 33% off annual licenses, good until December 2nd.  Get some!

= 4.1.8 - 11/26/2020 =

* And we're back.  Thanks to everyone for the well wishes, truly appreciated!
* Updated Freemius SDK to latest
* Added `mcloud-cloud/storage/should-handle-image-upload` filter to override image uploading logic when performing uploads
  with certain other plugins.
* Fix for `open_basedir restriction` error messages.
* Fix for integrated help documentation.
* Added option to turn off brute force image URL replacement for image tags missing the `wp-image-{NUMBER}` css class.
  If you are seeing a lot of database calls on a page with a lot of images, try adding this snippet to your theme's functions.php
  to insure that Media Cloud has enough "metadata" to do it's job:  https://gist.github.com/jawngee/36c104f8a8b8ea7e7f6b0f0b837affa5
* Launched new support and helpdesk site, https://support.mediacloud.press/
* Added two new people to our support staff, welcome to Quynh and welcome back to Charles!
* Use BLACKFRIDAY2020 for 33% off annual licenses, good until December 2nd.

= 4.1.6 - 9/23/2020 =

* When Debugging is enabled, a log file will be generated next to the CSV report.  This log file includes all the logging
  that would normally be in the Debug Log, but limited to the time period the task was running.  If you are running into
  issues with a task, make sure to turn on Debugging, re-run the task and then attach both the CSV and the log file
  to a support ticket https://support.mediacloud.press/submit-issue/
* The `report` command line command has been renamed to `verify`
* You can run the Verify Library task from the WordPress admin by going to Media Cloud -> Task Manager.  In the
  **Available Tasks** click on **Verify Library**.  When it is done running, a report will be in your
  `WP_CONTENT/mcloud-reports` directory
* Added a Sync Local task that copies down media from cloud storage to your local server.  You can run it from the
  WordPress admin by going to Media Cloud -> Task Manager.  In the **Available Tasks** click on **Sync Local**.
* You can also run the Sync Local task from the command line via `wp mediacloud syncLocal report-filename.csv`
* Fixed a bug with direct uploads where the cloud storage provider wasn't being saved in the cloud metadata.  If you
  run the Verify Library task, Media Cloud will fix the issue with any existing direct uploads in your library.
* Added paging to the `syncLocal` and `verify` command line commands, ex: `wp mediacloud verify verify.csv --limit=100 --page=1`
* Fixed Sync Local, Verify Library and Regenerate Thumbnails to work with Imgix enabled.

= 4.1.4 - 9/22/2020 =

* Fix for Regenerate Thumbnails command, it will first attempt to download the original image, if that can't be found then
  it will use the "scaled" image that WordPress 5.5 generates.
* Added a new command, `wp mediacloud report report-name.csv` that iterates through all of the items in your media library and reports
  on their cloud storage status.
* The Migrate To Cloud, Import From Cloud, Clean Uploads and Regenerate Thumbnails tasks now generate CSV reports when
  they run so you can see more details about what they did.  The reports are located in your
  `WP_CONTENT/mcloud-reports` directory.
* You can toggle task reporting on or off in the Settings -> Batch Processing settings or through the
  `MCLOUD_TASKS_GENERATE_REPORTS` environment variable.  The default value is ON.
* The Migrate To Cloud task has a new toggle *Generate Verification Report* which causes the task to verify that the
  media migrated successfully.   This will generate a report in the aforementioned reports directory.
* The `migrateToCloud` wp-cli command now accepts a `--verify` flag to force verification.

= 4.1.2 - 9/21/2020 =

* Fix for WooCommerce integration with files that have malformed metadata

= 4.1.1 - 9/20/2020 =

* Fix for compatibility with Amp plugin and any other plugin using symfony polyfills.
* Fix for edge case issue where the S3 library was closing a resource stream causing a fatal error.
* Added hooks `media-cloud/tools/added-tools` and `media-cloud/tools/added-tools` for inserting other tools in other plugins into the media cloud menu.
* Fix for Mux database tables failing installation on constricted MySQL systems.
* Only check for Mux database tables if Mux is enabled.
* Fix for front end uploads with some form plugins.


= 4.1.0 - 8/28/2020 =

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


= 4.0.11 - 8/21/2020 =

* Fix for deprecated `whitelist_options` filter in WP 5.5.
* Fix for uploads not occuring when using EWWW image optimizer and other image optimizers.
* Media Cloud will now warn you if your cloud storage isn't configured for CORS when performing direct uploads
* Fix for ACL error with Wasabi
* Added warning about Autoptimize compatibility
* You can now track Media Cloud development on its public trello board: https://trello.com/b/O0iNw6GL/media-cloud-development

= 4.0.10 - 8/20/2020 =

* Fix for attachment tasks when running from the command line (thanks @yanmorinokamca)

= 4.0.9 - 8/12/2020 =

* Direct uploads are now much faster
* Fix for "Add New" page for uploading media when direct uploads are enabled
* Fix for front-end direct uploads
* New setting in Direct Uploads that controls if thumbnails are generated in the browser when direct uploads for images are enabled.
* Improved compatibility with Dokan multivendor plugin for WooCommerce
* Fix for memory error when direct uploading extremely large images without Imgix.


= 4.0.8 - 7/23/2020 =

* Fix for BuddyPress compatibility
* Fix for WordPress's crappy image editor not saving edited images to cloud storage.
* Fix for Blubrry integration.
* Fix for duplicated Imgix uploads when Keep WordPress Thumbnails is enabled.
* Fix for small images not uploading, or not uploading when no image sizes are defined.
* Fix for duplicated Imgix uploads when the image being uploaded has been resized because of WordPress's dumb big image size threshold "feature".

= 4.0.5 - 7/20/2020 =

* Fix a bug with Imgix and SVGs where SVGs are being rendered as progressive JPEGs.
* Added new option to Imgix to control SVG rendering.  When this new option is enabled, any image size other than 'full' will be rendered as a PNG.  When turned off, the SVG is delivered as is with no conversion.

= 4.0.4 - 7/19/2020 =

* Fix for Ultimate Member uploads

= 4.0.3 - 7/18/2020 =

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
