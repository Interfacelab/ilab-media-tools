# Filters


## Imgix/Dynamic Images Filters
###`media-cloud/dynamic-images/enabled`
Returns if the dynamix images (either Imgix or Dynamic Images) is enabled.
```php
$enabled = apply_filters('media-cloud/dynamic-images/enabled', false);
```
&nbsp;

###`media-cloud/imgix/enabled`
Returns if the Imgix, specifically, is enabled.
```php
$enabled = apply_filters('media-cloud/imgix/enabled', false);
```
&nbsp;

###`media-cloud/imgix/alternative-formats/enabled`
Returns if alternative formats are enabled for Imgix.
```php
$enabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);
```
&nbsp;

###`media-cloud/imgix/detect-faces`
Returns if Imgix face detection is enabled.
```php
$enabled = apply_filters('media-cloud/imgix/detect-faces', false);
```
&nbsp;

###`media-cloud/imgix/render-pdf`
Returns if Imgix PDF rendering is enabled.
```php
$enabled = apply_filters('media-cloud/imgix/render-pdf', false);
```
&nbsp;

###`media-cloud/dynamic-images/filter-parameters`
Allows dynamic image parameters (Imgix or Dynamic Images) to be changed.
```php
add_filter('media-cloud/dynamic-images/filter-parameters', function($parameters, $imageSize, $attachmentId, $attachmentMeta) {
    if ($imageSize == 'some-size') {
        $parameters['fit'] = 'crop';
    }

    return $parameters;
}, 1000, 4);
```
&nbsp;

## Cloud Storage Filters
###`media-cloud/storage/after-upload`
This filter allows for filtering of an attachment's metadata after it has been uploaded to cloud storage.
```php
add_filter('media-cloud/storage/after-upload', function($attachmentMeta, $attachmentId) {
    // do something here
    return $attachmentMeta;
}, 1000, 2);
```
&nbsp;

###`media-cloud/storage/can-calculate-srcset`
Determines if Media Cloud should calculate the `srcset` for an image.
```php
add_filter('media-cloud/storage/can-calculate-srcset', function($canCalculateSrcSet) {
    return $canCalculateSrcSet;
});
```
&nbsp;

###`media-cloud/storage/can-filter-content`
Controls if Media Cloud should filter a post's content.
```php
add_filter('media-cloud/storage/can-filter-content', function($canFilterContent) {
    return $canFilterContent;
});
```
&nbsp;

###`media-cloud/storage/ignore-metadata-update`
This filter can be used to temporarily suspend Media Cloud's processing of attachment metadata.
```php
add_filter('media-cloud/storage/ignore-metadata-update', function($shouldIgnore, $attachmentId) {
    return $shouldIgnore;
}, 1000, 2);
```
&nbsp;

###`media-cloud/storage/ignore-existing-s3-data`
Forces Media Cloud to ignore an attachment's existing cloud storage metadata when processing an attachment.
```php
add_filter('media-cloud/storage/ignore-existing-s3-data', function($shouldIgnore, $attachmentId) {
    return $shouldIgnore;
}, 1000, 2);
```
&nbsp;

###`media-cloud/storage/ignore-optimizers`
Forces Media Cloud to ignore the fact that image optimizer plugins are installed and activated.  
```php
add_filter('media-cloud/storage/ignore-optimizers', function($shouldIgnore, $attachmentId) {
    return $shouldIgnore;
}, 1000, 2);
```
&nbsp;

###`media-cloud/storage/process-file-name`
Filters a given filename, removing any storage related parts from the path, eg the bucket name.
```php
$filename = apply_filters('media-cloud/storage/process-file-name', $filename);
```
&nbsp;


###`media-cloud/storage/should-handle-upload`
Controls if Media Cloud should process a WordPress upload.
```php
add_filter('media-cloud/storage/should-handle-upload', function($shouldHandle, $uploadData) {
    return $shouldHandle;
}, 1000, 2);
```
&nbsp;


###`media-cloud/storage/should-override-attached-file`
Media Cloud will typically intercept `get_attached_file()` and return the storage URL if that file is no longer present on the local filesystem.  If the file is on the local filesystem, `get_attached_file()` will return the file path to it.  This filter allows you to override this behavior.
```php
add_filter('media-cloud/storage/should-override-attached-file', function($shouldOverride, $attachment_id) {
    return $shouldOverride;
}, 1000, 2);
```
&nbsp;


###`media-cloud/storage/should-use-custom-prefix`
Allows the use of custom prefixes on uploads to be overridden from whatever the setting currently is.
```php
add_filter('media-cloud/storage/should-use-custom-prefix', function($shouldUseCustomPrefix) {
    return $shouldUseCustomPrefix;
});
```
&nbsp;

###`media-cloud/storage/upload-master`
Controls if the master/main image is uploaded to cloud storage.
```php
add_filter('media-cloud/storage/upload-master', function($shouldUpload) {
    return $shouldUpload;
});
```
&nbsp;


## Vision Filters
###`media-cloud/vision/detect-faces`
Returns if Vision is enabled and set to detect faces.
```php
$detectingFaces = apply_filters('media-cloud/vision/detect-faces', false);
```
&nbsp;

###`media-cloud/vision/process-meta`
Processes an attachment's metadata with Vision.  If Vision is setup to process in background, this processing will then take place later.
```php
$attachmentMeta = apply_filters('media-cloud/vision/process-meta', $attachmentMeta, $attachmentId);
```
&nbsp;

## Direct Upload Filters
###`media-cloud/direct-uploads/max-uploads`
Overrides the setting for the maximum number of uploads.
```php
add_filter('media-cloud/direct-uploads/max-uploads', function($maxUploads) {
    return min(4, $maxUploads);
});
```
&nbsp;


# Actions
###`media-cloud/imgix/setup`
This action is triggered once the Imgix feature is configured and ready to use.  This will not be triggered if the tool is not enabled.
```php
add_action('media-cloud/imgix/setup', function() {
    // Imgix is setup and ready
});
```
&nbsp;

###`media-cloud/direct-uploads/process-batch`
After files have been uploaded with Direct Upload, this action is called with the attachment IDs so that other tools can process the attachments.
```php
add_action('media-cloud/direct-uploads/process-batch', function($postIds) {
    // Do something with the post IDs.
});
```
&nbsp;

###`media-cloud/storage/register-drivers`
Registers any additional or custom cloud storage drivers.
```php
add_action('media-cloud/storage/register-drivers', function() {
    StorageManager::registerDriver('my-storage-driver-key', 'My Storage Driver', MyNamespace\MyStorageDriver::class, [], []);
});
```
&nbsp;

###`media-cloud/storage/uploaded-attachment`
This is fired once an upload has been added.
```php
add_action('media-cloud/storage/uploaded-attachment', function($attachmentId, $fileName, $uploadData) {
    // Do something
}, 1000, 3);
```
&nbsp;

###`media-cloud/tools/register-tools`
Register any custom tool with Media Cloud.
```php
add_action('media-cloud/tools/register-tools', function() {
    ToolsManager::registerTool('my-tool', []);
});
```
&nbsp;

###`media-cloud/vision/register-drivers`
Register any additional vision drivers.  This action is only called with the paid version.
```php
add_action('media-cloud/vision/register-drivers', function() {
    VisionManager::registerDriver('my-vision-driver', 'My Vision Driver', MyNamespace\MyVisionDriver::class, [], []);
});
```
&nbsp;
&nbsp;

# Deprecated Filters and Actions
## Filters

Deprecated Filter | New Filter
----------------- | ----------
ilab_s3_after_upload | media-cloud/storage/after-upload
ilab_s3_should_handle_upload | media-cloud/storage/should-handle-upload
ilab_s3_can_calculate_srcset | media-cloud/storage/can-calculate-srcset
ilab_media_cloud_filter_content | media-cloud/storage/can-filter-content
ilab_storage_should_use_custom_prefix | media-cloud/storage/should-use-custom-prefix
ilab-imgix-filter-parameters | media-cloud/dynamic-images/filter-parameters

&nbsp;
## Actions
Deprecated Action | New Action
----------------- | ----------
ilab_imgix_setup | media-cloud/imgix/setup

&nbsp;
