# Media Cloud

Media cloud is a revolutionary plug-in for WordPress that will supercharge the performance of your website and radically transform the way that you work with media in WordPress.

Media Cloud works by moving your images, media and other files from your WordPress server to online cloud storage such as Amazon S3, Google Cloud Storage, DigitalOcean Spaces and many others.  You can then serve that media through a CDN like Amazon Cloud front, Cloudflare, Fastly and others.

Beyond cloud storage, Media Cloud also has deep integration with Imgix, the leading real-time image manipulation and optimization CDN.  Media Cloud is the first plugin for WordPress to bring the full benefit of what Imgix offers - simplifying your development efforts, reducing your site’s page load times and opening up creative options that simply haven’t existed until now.

Media Cloud also provides advanced image editing tools that provide improved cropping options, effects, filters, watermarking and more.

* [Feature List](https://mediacloud.press/comparison/)
* [Documentation](https://mediacloud.press/documentation/)
* [Support](https://talk.mediacloud.press/)

## Installing via Composer

For the free version:

`composer require ilab/ilab-media-tools`

For the pro version, installing via composer won't be available until the end of July.

## Upgrade from 2.x to 3.x
If you are upgrading from 2.x there are a few things to be aware of:

* [Hooks](https://mediacloud.press/documentation/advanced/hooks) and [enviroment variables](https://mediacloud.press/documentation/advanced/environment-variables) have been refactored and almost all of them deprecated.
* You will receive a warning for using deprecated environment variables and hooks.
* The list of supported environment variables can be found [here](https://mediacloud.press/documentation/advanced/environment-variables)
* The list of supported hooks/filters can be found [here](https://mediacloud.press/documentation/advanced/hooks)


## Special Thanks
Special thanks to: @JulienMelissas, @eightam, @metadan, @farcaller, @HexaCubist, @kaisermann, @tapetersen, @LiljebergXYZ, @michaeljberry, @tomasvanrijsse, @ItsNotABug for the contributions!

