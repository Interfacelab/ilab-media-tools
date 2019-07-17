# Dynamic Images
Dynamic Images provides a lot of the functionality of Imgix such as on the fly resizing, cropping and effects - but it's built into the plugin and doesn't require an external account with a service.

Imgix will always perform better and have better quality, but Dynamic Images fits the bill for sites that have less media or are smaller such as a personal blog or a one pager type site.  Upgrading to Imgix is also a relatively seamless process, as is downgrading from Imgix.

With Dynamic Images you can:

- Crop and size images on the fly.  This is great for theme development or sites with frequent visual updates.
- Add effects to images
- Watermark images

While it's not necessary, Dynamic Images produces the best results when you have the `php-imagick` extension installed.

## Performance Notes
For the best performance with Dynamic Images, you will want to keep the images you upload on your WordPress server by making sure the option *Delete Uploaded Files* is turned off in [Cloud Storage settings](admin:admin.php?page=media-cloud-settings&tab=storage).  

When an image is requested, Media Cloud will perform the necessary manipulations, cache the result and then send the result to the end user.  Subsequent requests for the same image will return the cached version.  Therefore, the first request for an image is much slower than later requests will be.  If you are deleting your uploads from your local server, Media Cloud will have to fetch the image from cloud storage before performing the image manipulations.

## General Settings

### Image Path
This is the base URL path that the plugin will use to intercept image requests.  This can be any arbitrary path but it shouldn't collide with any paths you might be using on your site.

### Signing Key
The signing key will sign any image URL to make it more secure.  Not having a signing key would open you up to image resizing attacks, so it is obviously recommended that you have a signing key.

The default signing key is unique to your plugin installation so you typically don't need to change it.

## Performance Settings

### Cache Master Images
When Dynamic Images renders an image, it will first look for the master image on the local file system.  If it can't be found, it will then copy the master image from cloud storage.  Turning this option on will cache any images fetched from cloud storage to local storage.

Unless you are tight on disk space, there isn't any good reason to turn this option off.

### CDN
This is the base URL for your CDN and when Media Cloud rewrites the URL for any dynamically generated images, it'll use this value to replace the default your site's host name.

For example, you might use CloudFlare to set up a cache at `https://images.yourdomain.com/` that points to your webserver.  You would then set the value of this setting to `https://images.yourdomain.com/` and Media Cloud will rewrite all of your images to use this CDN host.

### Cache TTL
This is the number of minutes to cache the rendered image in the user's browser.  The default value of `525600` equates to 1 year.

## Image Settings

### Lossy Image Quality
This is the default JPEG compression level (1-100) to use when generating resized, cropped or effected images.

### Max. Image Width/Height
This value allows you to clamp the maximim image width and height for any generated images.

### Convert PNG to JPEG
Turning this on will convert all PNG uploads to JPEGs.

### Use Progressive JPEG
When rendering an image and the output is JPEG, turning this on will generate a progressive JPEG file.

### Keep WordPress Thumbnails
Because Dynamic Images can dynamically create new sizes for existing images, having WordPress create thumbnails is potentially pointless, a probable waste of space and definitely slows down uploads. However, if you plan to stop using Dynamic Images, having those thumbnails on S3 or locally will save you having to regenerate thumbnails later. **IMPORTANT:** Thumbnails will not be generated when you perform a direct upload because those uploads are sent directly to S3 without going through your WordPress server.