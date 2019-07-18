# Media Cloud
For the most basic use case, Media Cloud will automatically copy your WordPress media library to a cloud storage provider such as Amazon S3, Google Cloud Storage, DigitalOcean Spaces and others.  You can also integrate with Imgix to enable on-demand image resizing, real-time non-destructive image manipulations and content delivery through their CDN.  

Not interested in Imgix?  No problem, you can configure Media Cloud to deliver your content through any other CDN such as Amazon Cloud Front, Fastly, Cloudflare and others.  Media Cloud also has a built-in dynamic image sizing and manipulation feature similar to Imgix (though not as full-featured or as fast as Imgix).  

Media Cloud also allows you to automatically tag and moderate your images using machine learning computer vision via  Amazon Rekognition and Google Cloud Vision.  Automatically find faces in images to enable smart-cropping so that your cropped images are always focused!

You can also setup Media Cloud to serve your theme assets such as javascript and css from a CDN in a push or pull mode.

## Quick Start
If you want to jump in quickly, we advise that you do things in roughly this order:

1. Configure your cloud storage provider
2. Configure [Imgix](imgix.md) or [Dynamic Images](dynamic-images.md) (if necessary)
3. Configure [Direct Uploads](direct-uploads.md) (if using Imgix or Dynamic Images)
4. Run the [troubleshooter](admin:admin.php?page=media-tools-troubleshooter) to make sure everything is copacetic

Once the plugin is configured and the basics are working, explore the more advanced features to get the plugin working
the way you need it to.

## Support

You can reach our support forums are available here:

[https://talk.mediacloud.press/](https://talk.mediacloud.press/)

Our FAQs are also available here:

[https://mediacloud.press/faqs](https://mediacloud.press/faqs)
