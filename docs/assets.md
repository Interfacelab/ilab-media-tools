# Assets
Enabling the Assets feature will allow you to serve your theme's javascript and CSS from cloud storage or a CDN.

This features works in one of two modes: *Push* or *Pull*.  In *Push* mode, Media Cloud will upload your assets to cloud storage.  In *Pull* mode, your asset URLs will be rewritten to use a CDN host that will fetch those assets from your server automatically.

For best performance we recommend *Pull* mode.  *Push* mode requires Media Cloud to upload these assets and it will only upload them the first time they are used in your front-end.  This upload will only happen once, of course, so subsequent requests will be fast as they will come directly from cloud storage.

## Push Settings
The default operating mode for the Assets feature is *Pull* mode.  If you want to enable *Push* make sure that the **Push CSS Assets** and **Push Javascript Assets** options are enabled.

### Push CSS Assets
When this is enabled, theme and WordPress related CSS files will be copied to cloud storage and served from there.

### Push Javascript Assets
When this is enabled, theme and WordPress related javascript files will be copied to cloud storage and served from there.

### Live Push Processing
When enabled, assets will be uploaded to cloud storage as pages are browsed on your page, but only if they don't already exist or their version number has changed.

Initial uploads have a performance impact, however, most theme and WordPress assets will be uploaded on the first time a handful of pages are browsed.  After the assets have been uploaded there is negligible performance impact.

You can turn this option off if you are satisfied that all of your assets have been uploaded to cloud storage, but there really isn't a very compelling reason to disable it.

### Cache Control
This sets the browser caching rules for uploaded assets.

### Content Expiration
This sets the content expiration for uploaded assets in minutes.

## CDN Settings
For *Pull* mode you must specify a CDN URL.  For *Push*, not specifying a CDN will use the cloud storage URL.

### CDN Base URL
This is the base URL for your CDN for serving assets, including the scheme (meaning the http/https part).

## Versioning Settings
### Use Build Version
Assets that have been enqueued in WordPress without a specified version will normally be skipped when pushing/pulling assets. When this option is enabled, this tool will use a custom build number for the version. You can update this version by clicking on **Update Build Version** button at the bottom of the assets settings page.

## Debug Settings
### Log Warnings
When set to true and Media Cloud Debugging is enabled, this will log warnings about files that couldn't be uploaded to the error log.