# Direct Uploads
Normally, with Media Cloud activated and working, uploads go to WordPress first and then Media Cloud will upload to cloud storage.  When you enable Direct Uploads, those uploads will now skip WordPress completely, and are sent straight to your cloud storage provider.

For most upload types this works great, but when uploading images you will need to be using Imgix or Dynamic Images.  The reason being is that because your WordPress server never handles an upload, no additional image sizes will be generated from your images - a function that WordPress normally handles.  With Imgix and Dynamic Images, those additional image sizes are generated on the fly on an as-needed basis.

For Videos, direct uploads will not generate additional metadata that you may or may not need.  However, if you install FFProbe on your server, Media Cloud will generate this additional metadata after the upload to cloud storage is complete.

## Upload Settings

### Integrate with Media Library
When this option is enabled, performing any uploads through WordPress's media library will perform direct uploads.  If this option is off you will have to use the [Cloud Upload](admin:upload.php?page=media-cloud-upload) page to do direct uploads.

### Direct Upload Images/Video Files/Audio Files/Documents
These next set of options control what get directly uploaded and what gets uploaded to WordPress.

### Number of Simultaneous Uploads
This is the number of simulataneous uploads you'll be able to perform if **Integrate with Media Library** is enabled.  The max value is 8.

### Maximum Upload Size
This sets the maximum allowed upload size for direct uploads.  Set to 0 to use the default PHP setting.

## Direct Upload Image Settings

### Detect Faces
Enabling this will use browser based javascript machine learning to detect faces in your uploads.  Detected faces will be stored as additional metadata for the image. If you are using Imgix or Dynamic Images, you can use this for cropping images centered on a face. 

If you are relying on this functionality, the better option would be to use the Vision tool. It is more accurate with less false positives. If Vision is enabled, this setting is ignored in favor of Vision's results.

## Direct Upload Video Settings

### Use FFProbe for Videos
When this option is enabled, Media Cloud will use a command line tool called FFProbe to fetch the metadata about the video.  FFProbe must be installed on your server and the PHP function `shell_exec` must be enabled.

