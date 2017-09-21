var ilabMediaS3Uploader = function($, item, file) {
    this.start = function() {
        var mimeType = file.type;
        if (mimeType == 'application/x-photoshop') {
            mimeType = 'image/psd';
        }

        var data = {
            "action": "ilab_upload_prepare",
            "filename": file.name,
            "type": mimeType
        };

        $.post(ajaxurl, data, function(response){
            if (response.status == 'ready') {
                item.updateStatusText('Uploading ...');

                var data = new FormData();
                _.each(Object.keys(response.formData), function(key){
                    if (key != 'key') {
                        data.append(key, response.formData[key]);
                    }
                });

                if ((response.cacheControl != null) && (response.cacheControl.length > 0)) {
                    data.append('Cache-Control', response.cacheControl);
                }

                if (response.expires != null) {
                    data.append('Expires', response.expires);
                }

                data.append('Content-Type', mimeType);
                data.append('acl',response.acl);
                data.append('key',response.key);
                data.append('file',file);


                $.ajax({
                    url: response.url,
                    method: 'POST',
                    contentType: false,
                    processData: false,
                    data:data,
                    xhr: function() {
                        var xhr = $.ajaxSettings.xhr();
                        xhr.upload.onprogress = function (e) {
                            item.updateProgress(e.loaded / e.total);
                        };
                        return xhr;
                    },
                    success: function(successResponse) {
                        var importData = {
                            "action": "ilab_upload_import_cloud_file",
                            "key": response.key
                        };

                        $.post(ajaxurl, importData, function(importResponse) {
                            item.itemUploaded((importResponse.status == 'success'), importResponse);
                        });
                    },
                    error: function(response) {
                        item.itemUploadError();
                    }
                })
            } else {
                item.itemUploadError();
            }
        });

    }
};

ilabMediaUploadItem.prototype.storageUploader = ilabMediaS3Uploader;