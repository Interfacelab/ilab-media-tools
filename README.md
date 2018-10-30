# Media Cloud by ILAB

Media Cloud by ILAB is a suite of tools designed to enhance media handling in WordPress in a number of ways.

##### Upload to S3
Automatically copy media uploads to S3 and hosts your media directly 
from S3 or CloudFront.  Additionally, easily import your existing
media library to Amazon S3 with the push of a button.

##### Uploading to Google Cloud Storage
To upload your media to Google Cloud Storage, select "Google Cloud Storage" as your Storage Provider and then simply insert the JSON credentials object you can create with [Google Cloud Platform](https://console.cloud.google.com/apis/credentials) into the "Credentials" field. If you are supplying your credentials through a .env file, or envionrment variables, the key is: ILAB_CLOUD_GOOGLE_CREDENTIALS. The key ILAB_CLOUD_GOOGLE_CREDENTIALS must be an absolute path to the json file saved on your server (preferably in a non-publically accessible location).
###### To create the JSON credentials object in Google Cloud Platform:
1. Click "Create Credentials" -> Service Account Key. 
2. Select your Service account (you may need to create a new service account if you don't already have one)
3. Ensure JSON is selected
4. Click "Create". If you are using

##### Integrate with Imgix
[Imgix](https://imgix.com) will radically change the way that you build
your WordPress sites and themes.  This plugin is the best integration
available for WordPress.  Upload your images to S3 with our S3 tool
and then host the media with Imgix, providing you with real-time image
processing and automatic format delivery.  Forget ImageMagick, Imgix
is light years ahead in quality and speed.

##### Upload Directly to S3
Directly upload your media and documents to S3, bypassing your WordPress 
server completely.  Note, this feature requires Imgix.

##### Automatically Tag and Categorize with Amazon Rekognition
Use Amazon's latest AI tools to tag and categorize your images when uploading to S3.  With Rekognition, you can automatically detect objects, scenes, and faces in images.

##### Advanced Image Editing
When integrating with [Imgix](https://imgix.com), Media Cloud by ILAB provides the most 
advanced WordPress image editor.  Alter contrast, saturation, vibrancy
and over 30 other image editing operations - in real time right inside
the WordPress admin interface!  Completely non-destructive!

##### Image Cropping
Media Cloud by ILAB ships with the most advanced image cropping tool 
available for WordPress, based on Chen Fengyuan's amazing Cropper 
plugin for jQuery.

**Best of all you get this functionality for free.**

* Upload to S3
* Host media from S3 or any CDN that can connect to S3
* Integrate with Imgix media hosting
* Advanced image cropping tool
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
   
## Developer Notes

If you're using Imgix, you can specify additional parameters by adding a filter for `ilab_imgix_filter_parameters`.  For example, if you wanted to 
add blur to all of your images for some crazy reason:

```php
add_filter('ilab_imgix_filter_parameters',function($params, $size, $id, $meta){
	$params['blur'] = 20;
	return $params;
}, 10, 4);
```

There are additional filters and actions that you can hook into for various purposes:

| Filter | Description | Arguments |
| :----- | :----- | :----- |
| ilab_s3_can_calculate_srcset | Determines if the imgix tool can calculate the srcset for an img tag | None |
| ilab_imgix_enabled | Determines if the imgix is enabled | None |

| Action | Description | Arguments |
| :----- | :----- | :----- |
| ilab_imgix_setup | Called when imgix is setup/initialized | None |

### Sample S3 Policy For WordPress

Below is the minimum AWS IAM policy you can have for the plugin to function:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:DeleteObjectTagging",
                "s3:ListBucketMultipartUploads",
                "s3:DeleteObjectVersion",
                "s3:ListBucket",
                "s3:DeleteObjectVersionTagging",
                "s3:GetBucketAcl",
                "s3:ListMultipartUploadParts",
                "s3:PutObject",
                "s3:GetObjectAcl",
                "s3:GetObject",
                "s3:AbortMultipartUpload",
                "s3:DeleteObject",
                "s3:GetBucketLocation",
                "s3:PutObjectAcl"
            ],
            "Resource": [
                "arn:aws:s3:::YOURBUCKET/*",
                "arn:aws:s3:::YOURBUCKET"
            ]
        },
        {
            "Effect": "Allow",
            "Action": "s3:HeadBucket",
            "Resource": "*"
        }
    ]
}
```

Replace `YOURBUCKET` with the name of the bucket you want to enable access to.

To allow direct uploads to S3 via the "Cloud Upload" feature, you must also configure CORS on your bucket.  This is the 
recommended CORS configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
    <CORSRule>
        <AllowedOrigin>*</AllowedOrigin>
        <AllowedMethod>GET</AllowedMethod>
        <AllowedMethod>PUT</AllowedMethod>
        <AllowedMethod>POST</AllowedMethod>
        <AllowedMethod>HEAD</AllowedMethod>
        <MaxAgeSeconds>3000</MaxAgeSeconds>
        <AllowedHeader>*</AllowedHeader>
    </CORSRule>
</CORSConfiguration>
```


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
