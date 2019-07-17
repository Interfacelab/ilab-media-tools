# Imgix
Imgix is a real-time image processing proxy and CDN.  It can resize, optimize, add filters and effects and do other amazing image processing tasks on the fly - and then deliver those processed images to your users through their blazing fast CDN.

I know that sounds like a commercial, but I don't work for Imgix nor am I paid in any way by Imgix.  I am, however, one of their biggest cheerleaders because I think the service is so amazing and so fundamental to a scaleable WordPress stack that I can't imagine not using it on any WordPress site my clients engage me to build.  

> Fun fact!  If it weren't for Imgix, this plugin simply wouldn't exist.  I was rebuilding a site for a client that had over 30,000 images, moving them from a custom rails based CMS to WordPress.  On top of that, the design of the site was going to be changing again in the short term future, so I had to solve a lot of problems around media storage and handling with regards to WordPress.  If the design was going to change in the future, requiring new image sizes to be recut and generated, doing that for 30,000 images was going to be a nightmare!  But, because I knew Imgix could do all of this cropping and resizing on the fly, it was just a matter of writing a plugin to get Imgix integrated.  And now here we are, four years later!

## Configuring
Before you can configure Media Cloud to work with Imgix, you'll first need to setup a source on Imgix.  That's a bit beyond the scope of this documentation, but [Imgix's own documentation](https://docs.imgix.com/setup) is very thorough and easy to get through.

### Imgix Domains
Once you've created a source in Imgix, add the domains you've setup in the source here.  

The reason you can add multiple domains was to support domain sharding which in the good 'ol HTTP/1.1 days helped improve performance on image heavy websites.  But Imgix now uses HTTP/2 which makes domain sharding irrelevant.  We only allow multiple domains to be specified for backwards compatibility.

### Imgix Signing Key
It's ***highly recommended*** that you set up image signing.  You can read more about it [here](https://docs.imgix.com/setup/securing-images).

Once you've enabled Secure URLs in your Imgix source, copy the token from Imgix and paste it in here.

### Use HTTPS
This should always be turned on and I can't fathom why you'd want it turned off.  But in whatever weird edge case you've found yourself tangled up in where you need to serve images from Imgix over http, you can disable https here.

## Image Settings

### Lossy Image Quality
This controls the output quality of lossy file formats such as JPEG, progressive JPEG, WebP and JPEG XR.

### Auto Format
When this option is enabled, Imgix will determine if an image can be served in a better format using automatic content negotiation. For example, it may serve WebP images to Chrome users when this is enabled.

### Auto Compress
Imgix will make a best effort to reduce the size of the image. This might include applying more aggressive image compression.  For example, PNG images might be served as WebP with an alpha channel to Chrome users, but to everyone else they might be served as PNG8 images, if that conversion can happen without much quality loss.

### Enable Alternative Formats
Enabling this option allows you to serve non-standard (non-standard in the WordPress sense) image files through Imgix, such as Photoshop documents, TIFF files and Adobe Illustrator files.

Note that if you do use this feature and decide not to use Imgix in the future, any of these "alternative" images will no longer be viewable on your site.  They will only be viewable as long as Imgix is enabled.

### Keep WordPress Thumbnails
Because Imgix is dynamically generating all thumbnails and all of the various image sizes, having WordPress create those thumbnails is pointless.  If you are only testing Imgix, you may want to keep this option turned on in case you decide not to use Imgix.

Note that if you are using the Direct Upload feature, no thumbnails will be generated because uploads skip WordPress all together and go straight to cloud storage.

### Render PDF Files
When this is enabled, Imgix will render the first page of an uploaded PDF file that you can use like any other image on your site.

Like *Enable Alternative Formats*, if you disable Imgix in the future, however, these PDF previews will appear as broken images on your site.

### Detect Faces
After each upload, Media Cloud will use Imgix's face detection API to locate all of the faces in your image.  This information can then be used with the Focus Crop of the image editor to automatically center cropped images on faces or groups of faces.  

If you find yourself using this face feature a lot, you may want to consider using [Rekognition or Google Cloud Vision](vision/index.md) which will give better results.

## GIF Settings
Imgix has amazing support for animated GIFs, including the ability to resize and crop them on the fly.  However this functionality requires a [premium Imgix account](https://docs.imgix.com/apis/url/format/fm#gif).

### Enable GIFs
When this is disabled, Imgix will convert animated GIFs to static non-animated images.  As mentioned above, to have Imgix deliver animated GIFs requires a premium Imgix account.

### Serve GIFs from Storage
When this option is turned on, all animated GIFs will be served from storage, skipping Imgix.  This will only display the original size GIF as animated, as WordPress will convert any animated GIF to a static image when it generates its thumbnails and other sizes.

### Disallow Animated GIFs for Sizes
When Imgix GIF support is enabled, it might be desirable to display the static version of the image at certain image sizes instead of the animated GIF.  List those image sizes here.