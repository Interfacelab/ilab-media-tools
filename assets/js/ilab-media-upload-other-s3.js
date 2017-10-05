var ilabMediaOtherS3Uploader = function($, item, file) {
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

                var xhr = new XMLHttpRequest();
                xhr.open('PUT', response.url, true);
                xhr.upload.onprogress = function(e) {
                    item.updateProgress(e.loaded / e.total);
                };

                xhr.onload = function() {
                    var importData = {
                        "action": "ilab_upload_import_cloud_file",
                        "key": response.key
                    };

                    $.post(ajaxurl, importData, function(importResponse) {
                        item.itemUploaded((importResponse.status == 'success'), importResponse);
                    });
                };

                xhr.onerror = function() {
                    item.itemUploadError();
                };

                xhr.send(file);
            } else {
                item.itemUploadError();
            }
        });

    }
};

ilabMediaUploadItem.prototype.storageUploader = ilabMediaOtherS3Uploader;