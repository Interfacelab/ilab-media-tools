# Environment Variables
Every setting in Media Cloud can be set using environment variables.  For example, if you are using Root's [Trellis](https://roots.io/trellis) and/or [Bedrock](https://roots.io/bedrock), these variables would be defined in a `.env` file located in your project's root.
&nbsp;

## Enabled Features Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_TOOL_ENABLED_STORAGE | boolean | Cloud storage is enabled.
MCLOUD_TOOL_ENABLED_IMGIX | boolean | Imgix is enabled.
MCLOUD_TOOL_ENABLED_GLIDE | boolean | Dynamic Images is enabled.
MCLOUD_TOOL_ENABLED_MEDIA_UPLOAD | boolean | Direct Upload is enabled.
MCLOUD_TOOL_ENABLED_CROP | boolean | Crop tool is enabled.
MCLOUD_TOOL_ENABLED_VISION | boolean | Vision is enabled.
MCLOUD_TOOL_ENABLED_ASSETS | boolean | Asset feature is enabled.
MCLOUD_TOOL_ENABLED_BROWSER | boolean | Storage browser is enabled.
MCLOUD_TOOL_ENABLED_DEBUGGING | boolean | Debugging is enabled.
MCLOUD_TOOL_ENABLED_IMAGE_SIZES | boolean | Image size editor is enabled.
MCLOUD_VIEW_CACHE | path | File path to store intermediate views that Media Cloud renders when displaying the admin UI.

&nbsp;

## Cloud Storage Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_STORAGE_PROVIDER | string | The cloud storage provider to use.  Valid values: `s3`, `google`, `do`, `minio`, `wasabi`, `other-s3` and `backblaze`.
MCLOUD_STORAGE_S3_ACCESS_KEY | string | The access key for S3 and S3-compatible services.
MCLOUD_STORAGE_S3_SECRET | string | The secret key for S3 and S3-compatible services.
MCLOUD_STORAGE_S3_USE_CREDENTIAL_PROVIDER | boolean | When this is enabled, Media Cloud will load your S3 credentials from the environment, `~/.aws/credentials` or `~/.aws/config`.  When this is enabled, the **Access Key** and **Secret** values specified in these settings will be ignored.  This is an advanced option and ***should only be enabled if you know what you are doing***.
MCLOUD_STORAGE_S3_REGION | string | The region name, valid values are service dependent.
MCLOUD_STORAGE_S3_BUCKET | string | The name of the bucket to use for S3 and S3-compatible services.
MCLOUD_STORAGE_S3_ENDPOINT | host | The end point host for S3-compatible services.  For example, DigitalOcean NYC-3 would be `nyc3.digitaloceanspaces.com`
MCLOUD_STORAGE_S3_USE_PATH_STYLE_ENDPOINT | boolean | Sends request to an S3 path style endpoint, for S3-compatible services only.
MCLOUD_STORAGE_S3_USE_TRANSFER_ACCELERATION | boolean | Enables transfer acceleration for Amazon S3 only.
MCLOUD_STORAGE_GOOGLE_BUCKET | string | The name of the bucket to use for Google Cloud Storage.
MCLOUD_STORAGE_GOOGLE_CREDENTIALS_FILE | path | The file path for the Google json credentials file.
MCLOUD_STORAGE_BACKBLAZE_ACCOUNT_ID | string | Backblaze account ID.
MCLOUD_STORAGE_BACKBLAZE_BUCKET_URL | url | URL for the backblaze bucket.
MCLOUD_STORAGE_BACKBLAZE_KEY | string | The application key to use for Backblaze.
MCLOUD_STORAGE_PRIVACY | string | Privacy ACL for uploads, valid values are `public-read` or `authenticated-read`.
MCLOUD_STORAGE_PREFIX | string | The prefix for file upload paths.
MCLOUD_STORAGE_IGNORED_MIME_TYPES | string | MIME types of files that should not be uploaded to cloud storage.
MCLOUD_STORAGE_CDN_BASE | url | Base URL for CDN.
MCLOUD_STORAGE_DOC_CDN_BASE | url | Base URL for document (non-media) CDN.
MCLOUD_STORAGE_CACHE_CONTROL | string | Default cache control for uploads.
MCLOUD_STORAGE_EXPIRES | number | Cache expiration for uploaded files.
MCLOUD_STORAGE_DELETE_FROM_SERVER | boolean | Delete from cloud storage when deleted from media library.
MCLOUD_STORAGE_DELETE_UPLOADS | boolean | Delete uploads after uploaded to cloud storage.
MCLOUD_STORAGE_DISPLAY_BADGE | boolean | Display cloud icon in media library for uploaded media.
MCLOUD_STORAGE_DISPLAY_MEDIA_LIST | boolean | Add extra column to media library in list view mode.
MCLOUD_STORAGE_UPLOAD_DOCUMENTS | boolean | Allow non-media files to be uploaded to cloud storage.
MCLOUD_STORAGE_USE_PRESIGNED_URLS | boolean | Use pre-signed URLs.
MCLOUD_STORAGE_PRESIGNED_EXPIRATION | number | Number of minutes pre-signed URLs are valid for.

&nbsp;

## Imgix Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_IMGIX_DOMAINS | url | List of imgix domains to use.
MCLOUD_IMGIX_SIGNING_KEY | string | The signing key for securing Imgix URLs.
MCLOUD_IMGIX_USE_HTTPS | boolean | Use HTTPS.
MCLOUD_IMGIX_DEFAULT_QUALITY | number | Default quality for lossy images.
MCLOUD_IMGIX_AUTO_FORMAT | boolean | Allow Imgix to choose the most appropriate image format.
MCLOUD_IMGIX_AUTO_COMPRESS | boolean | Allow Imgix to automatically compress your images.
MCLOUD_IMGIX_ENABLE_ALT_FORMATS | boolean | Enable alternative formats usch as PSD, TIFF and Illustrator.
MCLOUD_IMGIX_GENERATE_THUMBNAILS | boolean | Allow WordPress to generate thumbnails, ignored if Direct Upload enabled.
MCLOUD_IMGIX_DETECT_FACES | boolean | Use Imgix face detection on uploads.  Ignored if Vision detect faces is enabled.
MCLOUD_IMGIX_RENDER_PDF_FILES | boolean | Render the first page of a PDF file as an image.
MCLOUD_IMGIX_ENABLE_GIFS | boolean | Enable animated GIF support, required Imgix premium account.
MCLOUD_IMGIX_SKIP_GIFS | boolean | If enabled, GIFs will be served from cloud storage.
MCLOUD_IMGIX_NO_GIF_SIZES | boolean | List of image sizes that disallow animated GIFs.

&nbsp;

## Dynamic Images Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_GLIDE_IMAGE_PATH | string | The base path for the generated image URLs.
MCLOUD_GLIDE_SIGNING_KEY | string | The signing key used to create secure URLs.
MCLOUD_GLIDE_CACHE_REMOTE | boolean | This option will cache any master images that are fetched from remote storage.
MCLOUD_GLIDE_CDN | url | Base URL for the CDN.
MCLOUD_GLIDE_CACHE_TTL | number | The number of minutes to cache the rendered image in the user's browser.
MCLOUD_GLIDE_DEFAULT_QUALITY | number | The image quality for JPEG compression.
MCLOUD_GLIDE_MAX_SIZE | number | The max width or height for a generated image.
MCLOUD_GLIDE_CONVERT_PNG | boolean | Convert all PNG to JPEGs.
MCLOUD_GLIDE_PROGRESSIVE_JPEG | boolean | Use progressive JPEGs.
MCLOUD_GLIDE_GENERATE_THUMBNAILS | boolean | Allow WordPress to generate thumbnails, ignored if Direct Upload enabled.

&nbsp;

## Direct Upload Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_DIRECT_UPLOADS_INTEGRATION | boolean | Integrated with the WordPress media library so that all uploads done through the library are direct uploads.
MCLOUD_DIRECT_UPLOADS_SIMULTANEOUS_UPLOADS | number | The maximum number of simultaneous direct uploads.
MCLOUD_DIRECT_UPLOADS_DETECT_FACES | boolean | Uses browser based javascript machine learning to detect faces in the image.   If Vision is enabled, this setting is ignored in favor of Vision's results.

&nbsp;

## Vision Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_VISION_PROVIDER | string | Which Vision provider to use.  Valid values are: `rekognition` or `google`.
MCLOUD_VISION_DETECT_LABELS | boolean | Detects instances of real-world labels within an image such as flower, tree, table, desk, etc.
MCLOUD_VISION_DETECT_LABELS_TAX | string | The taxonomy to use for applying labels as tags.
MCLOUD_VISION_DETECT_LABELS_CONFIDENCE | number | The minimum confidence required to apply the label as a tag.
MCLOUD_VISION_DETECT_MODERATION_LABELS | boolean | Detects explicit or suggestive adult content in an image.
MCLOUD_VISION_DETECT_MODERATION_LABELS_TAX | string | The taxonomy to use for applying labels as tags.
MCLOUD_VISION_DETECT_MODERATION_LABELS_CONFIDENCE | number | The minimum confidence required to apply the label as a tag.
MCLOUD_VISION_DETECT_CELEBRITY | boolean | Detect celebrities faces in images.
MCLOUD_VISION_DETECT_CELEBRITY_TAX | string | The taxonomy to use for celebrity tagging.
MCLOUD_VISION_DETECT_FACES | boolean | Detect faces in images.
MCLOUD_VISION_ALWAYS_BACKGROUND | boolean | If enabled, all Vision processing occurs in the background, otherwise processing occurs during an upload.
MCLOUD_VISION_IGNORED_TAGS | string | Comma separated list of tags to ignore.

&nbsp;

## Asset Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_ASSETS_STORE_CSS | boolean | If enabled, **Push** mode is used to upload CSS assets to cloud storage.  Disable for **Pull** mode.
MCLOUD_ASSETS_STORE_JS | boolean | If enabled, **Push** mode is used to upload javascript assets to cloud storage.  Disable for **Pull** mode.
MCLOUD_ASSETS_PROCESS_LIVE | boolean | Assets will be uploaded to cloud storage as pages are browsed on the live site, but only if they don't already exist on cloud storage or their version number has changed.
MCLOUD_ASSETS_CACHE_CONTROL | string | Sets the Cache-Control metadata for uploaded assets.
MCLOUD_ASSETS_EXPIRES | number | Sets the Expire metadata for uploaded assets.
MCLOUD_ASSETS_CDN_BASE | url | The base URL for the CDN.
MCLOUD_ASSETS_USE_BUILD_VERSION | boolean | Assets that have been enqueued in WordPress without a specified version will normally be skipped when pushing/pulling assets. When this option is enabled, this tool will use a custom build number for the version.
MCLOUD_ASSETS_LOG_WARNINGS | boolean | When enabled and Media Cloud Debugging is enabled, this will log warnings about files that couldn't be uploaded to the error log.

&nbsp;

## Crop Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_CROP_QUALITY | number | The JPEG compression to use for cropped images.  This will supercede the WordPress default.  Ignored if using Dynamic Images or Imgix.

&nbsp;

## Multisite Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_NETWORK_MODE | boolean | When enabled, Media Cloud settings apply to all sites in the network.  When disabled, each site has its own settings.
MCLOUD_NETWORK_HIDE_BATCH | boolean | When enabled, batch processing tools are hidden from individual sites.
MCLOUD_NETWORK_BROWSER_ALLOW_DELETING | boolean | When enabled, the storage browser allows deleting files from cloud storage.
MCLOUD_NETWORK_BROWSER_ALLOW_UPLOADS| boolean | When enabled, the storage browser allows uploads.
MCLOUD_NETWORK_BROWSER_HIDE | boolean | When enabled, the storage browser is hidden from individual sites in the network.
MCLOUD_NETWORK_BROWSER_LOCK_TO_ROOT | boolean | When enabled, the storage browser is locked to the root directory of the site in the network.

&nbsp;

## Batch Processing Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_STORAGE_BATCH_VERIFY_SSL | string | Determines if SSL is verified when making the remote connection for the background process.  Valid values are: `default`, `yes`, `no`.
MCLOUD_STORAGE_BATCH_CONNECT_TIMEOUT | number | The number of seconds to wait for a connection to occur.
MCLOUD_STORAGE_BATCH_TIMEOUT | number | The number of seconds to wait for a response before the request times out.
MCLOUD_STORAGE_BATCH_SKIP_DNS | boolean | Skip DNS resolution by making the background request to localhost but setting the HTTP_HOST header.
MCLOUD_STORAGE_BATCH_BACKGROUND_PROCESSING | boolean | Enables background processing.

&nbsp;

## Integrations Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_WOO_COMMERCE_USE_PRESIGNED_URLS | boolean | Enable to generate signed URLs for downloadable products that will expire within a specified time period.
MCLOUD_WOO_COMMERCE_PRESIGNED_EXPIRATION | The number of minutes the signed URL is valid for.
MCLOUD_EDD_USE_PRESIGNED_URLS | boolean | Enable to generate signed URLs for downloadable products that will expire within a specified time period.
MCLOUD_EDD_PRESIGNED_EXPIRATION | number | The number of minutes the signed URL is valid for.
MCLOUD_MASTER_SLIDER_IMAGE_RESIZE | boolean | Determines if the images should be resized or not.
MCLOUD_MASTER_SLIDER_IMAGE_WIDTH | number | Override the slider's specified image width. 
MCLOUD_MASTER_SLIDER_IMAGE_HEIGHT | number | Override the slider's specified image height. 
MCLOUD_MASTER_SLIDER_THUMB_CROP | boolean | Determines if the thumbnail should be cropped or not.
MCLOUD_MASTER_SLIDER_THUMB_WIDTH | number | Override the slider's specified thumb width.
MCLOUD_MASTER_SLIDER_THUMB_HEIGHT | number | Override the slider's specified thumb height.
MCLOUD_SMART_SLIDER_PATH_PREFIX | string | When Smart Slider 3 resizes an image, Media Cloud will upload it to cloud storage. This will prepend a prefix to any file uploaded to cloud storage. For dynamically created prefixes, you can use the following variables: `@{site-name}`, `@{site-host}`, `@{site-id}`.
MCLOUD_NGG_USE_URL_CACHE | boolean | Due to the way NGG works, to speed up things Media Cloud will cache the URLs for next gen gallery images.

&nbsp;

## Debug Environment Variables
Variable | Type | Description
-------- | ---- | -----------
MCLOUD_DEBUG_LOGGING_LEVEL | string | The debugging level.  Valid values are: `none`, `info`, `warning`, `error`.
MCLOUD_DEBUG_MAX_DATABASE_ENTRIES | number | The maximum number of log entries to keep in the database. 

&nbsp;
&nbsp;


## Deprecated Environment Variables
The following environment variables from previous versions of Media Cloud have been deprecated.  When you upgrade to the latest, you will be warned if you are using any deprecated variables.

Old Environment Variable | New Environment Variable
------------------------ | ------------------------
ILAB_AWS_S3_CACHE_CONTROL | MCLOUD_STORAGE_CACHE_CONTROL
ILAB_AWS_S3_CDN_BASE | MCLOUD_STORAGE_CDN_BASE
ILAB_AWS_S3_DOC_CDN_BASE | MCLOUD_STORAGE_DOC_CDN_BASE
ILAB_AWS_S3_EXPIRES | MCLOUD_STORAGE_EXPIRES
ILAB_CLOUD_GOOGLE_BUCKET | MCLOUD_STORAGE_GOOGLE_BUCKET
ILAB_AWS_S3_BUCKET | MCLOUD_STORAGE_S3_BUCKET
ILAB_CLOUD_BUCKET | MCLOUD_STORAGE_S3_BUCKET
ILAB_CLOUD_STORAGE_PROVIDER | MCLOUD_STORAGE_PROVIDER
ILAB_AWS_S3_ACCESS_KEY | MCLOUD_STORAGE_S3_ACCESS_KEY
ILAB_CLOUD_ACCESS_KEY | MCLOUD_STORAGE_S3_ACCESS_KEY
ILAB_AWS_S3_USE_CREDENTIAL_PROVIDER | MCLOUD_STORAGE_S3_USE_CREDENTIAL_PROVIDER
ILAB_CLOUD_USE_CREDENTIAL_PROVIDER | MCLOUD_STORAGE_S3_USE_CREDENTIAL_PROVIDER
ILAB_AWS_S3_ENDPOINT | MCLOUD_STORAGE_S3_ENDPOINT
ILAB_CLOUD_ENDPOINT | MCLOUD_STORAGE_S3_ENDPOINT
ILAB_AWS_S3_REGION | MCLOUD_STORAGE_S3_REGION
ILAB_CLOUD_REGION | MCLOUD_STORAGE_S3_REGION
ILAB_AWS_S3_ACCESS_SECRET | MCLOUD_STORAGE_S3_SECRET
ILAB_CLOUD_ACCESS_SECRET | MCLOUD_STORAGE_S3_SECRET
ILAB_AWS_S3_ENDPOINT_PATH_STYLE | MCLOUD_STORAGE_S3_USE_PATH_STYLE_ENDPOINT
ILAB_CLOUD_ENDPOINT_PATH_STYLE | MCLOUD_STORAGE_S3_USE_PATH_STYLE_ENDPOINT
ILAB_AWS_S3_TRANSFER_ACCELERATION | MCLOUD_STORAGE_S3_SECRET
ILAB_CLOUD_GOOGLE_CREDENTIALS | MCLOUD_STORAGE_GOOGLE_CREDENTIALS_FILE
ILAB_MEDIA_CLOUD_VIEW_CACHE | MCLOUD_VIEW_CACHE
ILAB_MEDIA_DEBUGGING_ENABLED | MCLOUD_TOOL_ENABLED_DEBUGGING
ILAB_VISION_PROVIDER | MCLOUD_VISION_PROVIDER
ILAB_AWS_REKOGNITION_DETECT_LABELS | MCLOUD_VISION_DETECT_LABELS
ILAB_AWS_REKOGNITION_DETECT_LABELS_TAX | MCLOUD_VISION_DETECT_LABELS_TAX
ILAB_AWS_REKOGNITION_DETECT_LABELS_CONFIDENCE | MCLOUD_VISION_DETECT_LABELS_CONFIDENCE
ILAB_AWS_REKOGNITION_MODERATION_LABELS | MCLOUD_VISION_DETECT_MODERATION_LABELS
ILAB_AWS_REKOGNITION_MODERATION_LABELS_TAX | MCLOUD_VISION_DETECT_MODERATION_LABELS_TAX
ILAB_AWS_REKOGNITION_MODERATION_LABELS_CONFIDENCE | MCLOUD_VISION_DETECT_MODERATION_LABELS_CONFIDENCE
ILAB_AWS_REKOGNITION_DETECT_CELEBRITY | MCLOUD_VISION_DETECT_CELEBRITY
ILAB_AWS_REKOGNITION_DETECT_CELEBRITY_TAX | MCLOUD_VISION_DETECT_CELEBRITY_TAX
ILAB_AWS_REKOGNITION_DETECT_FACES | MCLOUD_VISION_DETECT_FACES
