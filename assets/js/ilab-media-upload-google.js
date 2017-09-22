var ilabMediaGoogleUploader = function($, item, file) {
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

                var key = response.key;
                var acl = response.acl;

                $.ajax({
                    url: response.url,
                    method: 'POST',
                    headers: {
                        "x-goog-resumable": "start",
                        // "x-goog-acl": acl,
                        "Content-Type": file.type
                    },
                    success: function(response, status, xhr) {
                        var location = xhr.getResponseHeader('location');

                        $.ajax({
                            url: location,
                            method: 'PUT',
                            processData: false,
                            crossDomain: true,
                            data:file,
                            contentType: file.type,
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
                                    "key": key
                                };

                                $.post(ajaxurl, importData, function(importResponse) {
                                    item.itemUploaded((importResponse.status == 'success'), importResponse);
                                });
                            },
                            error: function(response) {
                                item.itemUploadError();
                            }
                        });
                    },
                    error: function(response) {
                        item.itemUploadError();
                    }
                });
            } else {
                item.itemUploadError();
            }
        });

    }
};

ilabMediaUploadItem.prototype.storageUploader = ilabMediaGoogleUploader;