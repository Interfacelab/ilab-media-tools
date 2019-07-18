# Cloud Storage
There are a lot of cloud storage providers to choose from and we support nearly all of them.  You've probably already picked one, but if you haven't, there are a few things to consider when shopping around:

- Features
- Speed
- Price

Choosing a provider is also a *commitment* because once you've selected one and have used it for a length of time, switching to another one is going to be difficult and time consuming.  So while you might be prioritizing price over the other features and speed, I'd encourage you to prioritize features first.

We've tested and used nearly every cloud storage provider under the sun and our top choices are the obvious ones: Amazon S3, Google Cloud Storage, DigitalOcean Spaces and, in certain cases, Minio.  These providers offer a nice balance of features, performance and price.

## Setup
Follow these guides to get your storage provider setup:

- [Amazon S3](cloud-storage/setup/amazon-s3.md)
- [Google Cloud Storage](cloud-storage/setup/google-cloud-storage.md)
- [DigitalOcean Spaces](cloud-storage/setup/do-spaces.md)
- [Wasabi](cloud-storage/setup/wasabi.md)
- [Backblaze](cloud-storage/setup/backblaze.md)

## Pre-Signed URLs
Pre-signed URLs will generate secure signed URLs for your uploaded media that expire after a specified amount of time.  We don't generally recommend that you use these for general images that you use on the site, but instead relegate their use to WooCommerce or Easy Digital Downloads for digital downloads.  However, if you were doing an image licensing type site, pre-signed URLs might be a useful deterrent - but be aware that any downstream caching of your site, either via caching plugins or something like CloudFlare, will cache these URLs and this might cause your site to show what appears to be broken images.

## Upload Handling
When configuring your cloud storage you have a variety of options to control how uploads are handled by WordPress and Media Cloud.

Note that not all options are available for every storage provider.  For example, Backblaze doesn't allow you to set the privacy of your uploads.

### Upload Privacy
By default, Media Cloud will upload your media to your storage provider and set the access of that upload to be publically readable.  If this were set to private, your media would not be viewable and appear to be broken.

However, if you are using Imgix or a CDN that reads directly from your cloud storage (Amazon CloudFront in the case of Amazon S3), you can set the privacy to be authenticated-read which would mean the public would not be able to access it.

### Cache Control
Setting the Cache Control and Content Expiration will control how this item is cached at the cloud storage provider level and beyond.  For example, setting these will control how the end user's browser will cache the media when they view it.  It will also inform most CDNs that are reading the media how to cache it on their end.

### Upload File Prefix
This allows you to control the directory structure for your uploads.  The default WordPress method is using the structure `year/month` which isn't granular enough for some use cases.

Media Cloud allows you to create dynamic upload file prefixes using tokens.  For example, setting the upload file prefix to `@{date:Y/m/d}` would yield upload directories that included the full date: `2019/12/26`.  The following prefixes are available:

- `@{date:format}` - Uses the current date to create the prefix.  You can use any format specifiers available to the PHP [date()](https://www.php.net/manual/en/function.date.php) function.
- `@{site-name}` - The current site's name
- `@{site-host}` - The current site's domain/host
- `@{site-id}` - The numeric ID of the site (when using WordPress multisite)
- `@{versioning}` - Basically the timestamp of the upload.  This is provided for Offload Media compatibility.
- `@{user-name}` - The name of the current user
- `@{unique-id}` - A unique identifier, note that this is unique for every upload.
- `@{unique-path}` - A unique identifier split into two character paths.  For example: `/26/68/09/c7/c0/62/62/d4/`

### Upload Non-image Files
When this option is enabled, non-image files such as Word Documents, PDF Files, Zip Files, etc. will be uploaded to cloud storage.

If you are using Imgix and want to render PDF file thumbnails, you will need this option enabled.

### Ignored MIME Types
If you don't want certain file types to be uploaded to cloud storage, list their mime types here.  

### Delete Uploaded Files
This option will delete the file from your local storage after Media Cloud has transferred it to your Cloud Storage provider.  

If you are using Dynamic Images, it's recommended that this option be turned *off* as Dynamic Images would have to download the image from cloud storage if it can't find a local copy.

### Delete From Storage
When this option is enabled, Media Cloud will delete the file from your cloud storage provider when the file is deleted from the media library.


## CDN Settings
If you aren't using Imgix, it's recommended that you use some sort of CDN to deliver your media to your end users.  

If you are using Imgix or Dynamic Images, this setting will be ignored.

### CDN Base URL
This is the base URL for your CDN and when Media Cloud rewrites the URL for images, it'll use this value to replace the default cloud storage host.

### Document CDN Base URL
If you want to use a different CDN for non-image files, specify the CDN base URL here.

## Display Settings
These options control how Media Cloud integerates with the WordPress UI.

### Display Cloud Icon
Turning this on will display a cloud icon on media in the media library.  This cloud icon indicates that the media has been uploaded, and is being served from, cloud storage.  Mousing over the cloud icon will show you a pop-up with more information about the media.

### Media List Integration
This option controls integration with the media library when viewing the media items in the list mode.  Turning it on will add a column to the list that links to the media on cloud storage and will also add additional actions to the Bulk Actions drop down.