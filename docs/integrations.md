# Integrations
Various integrations with other WordPress plugins have options that you can configure to customize how Media Cloud works with them.

## WooCommerce and Easy Digital Downloads
### Use Pre-Signed URLs
When this is enabled, Media Cloud will generate signed URLs for downloadable products that will expire and become unusable within a specified time period.  

### Pre-Signed URL Expiration
The number of minutes that a signed URL is valid for.

## Master Slider
Note that with Master Slider, if you are using Imgix or Dynamic Images, the images will be resized and cropped to exact sizes. If you aren't using either feature, than the closest pre-defined image size will be used.

### Resize Image
If this option is enabled, images used in a slider will be resized, if using Imgix or Dynamic Images.  If not using Imgix or Dynamic Images, the closest pre-defined image size will be used.

### Override Image Width
Setting this will allow you to override the slider's default image width.

### Override Image Height
Setting this will allow you to override the slider's default image height.

### Crop Thumbnail
This controls if the thumbnail is cropped or if the thumbnail is scaled to fit.

### Override Thumb Width
Setting this will allow you to override the slider's default thumbnail width.

### Override Thumb Height
Setting this will allow you to override the slider's default thumbnail height.

## Smart Slider 3
### Upload Prefix
When Smart Slider 3 resizes an image, Media Cloud will automatically upload it to cloud storage.  Normal prefixes that you would set in the Cloud Storage settings cannot be used and you are limited to using:

- `@{site-name}` - The current site's name
- `@{site-host}` - The current site's domain/host
- `@{site-id}` - The numeric ID of the site (when using WordPress multisite)

## Next Generation Gallery
### Use URL Cache
Due to the way NGG works, to speed up things Media Cloud will cache the URLs for next gen gallery images. If you are seeing issues where images aren't updating, turn this off.