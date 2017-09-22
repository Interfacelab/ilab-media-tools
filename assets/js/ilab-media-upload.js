var ilabAttachmentInfo = function($, uploader, attachmentInfo) {
    var self = this;

    this.info = $(uploader.attachmentTemplate(attachmentInfo));
    this.data = attachmentInfo;

    this.attachmentTitle = this.info.find('input[name="attachment-title"]');
    this.attachmentCaption = this.info.find('textarea[name="attachment-caption"]');
    this.attachmentAlt = this.info.find('input[name="attachment-alt"]');
    this.attachmentDescription = this.info.find('textarea[name="attachment-description"]');
    this.attachmentAlign = this.info.find('select[name="attachment-align"]');
    this.attachmentLinkURL = this.info.find('input[name="attachment-link-url"]');
    this.attachmentSize = this.info.find('select[name="attachment-size"]');
    this.attachmentLinkType = this.info.find('select[name="attachment-link-type"]');

    this.saveToken = null;

    var doSave = function() {
        var data = {
            "action": "save-attachment",
            "id": self.data.id,
            "nonce": self.data.nonces.update,
            post_id: window.parent.wp.media.model.settings.post.id,
            "changes[title]": self.attachmentTitle.val(),
            "changes[caption]": self.attachmentCaption.val(),
            "changes[alt]": self.attachmentAlt.val(),
            "changes[description]": self.attachmentDescription.val()
        };

        $.post(ajaxurl, data, function(response){
        });
    };

    this.save = function() {
        clearTimeout(self.saveToken);
        self.saveToken=setTimeout(doSave, 1000);
    };

    var handleChange = function(e) {
        self.save();
    };

    this.attachmentTitle.on('change', handleChange);
    this.attachmentCaption.on('change', handleChange);
    this.attachmentAlt.on('change', handleChange);
    this.attachmentDescription.on('change', handleChange);

    this.attachmentLinkType.on('change', function(){
        var v = self.attachmentLinkType.val();
        if (v == 'none') {
            self.attachmentLinkURL.css({"display": "display"});
            self.attachmentLinkURL.val('');
        } else if (v == 'file') {
            self.attachmentLinkURL.prop("readonly", "readonly");
            self.attachmentLinkURL.css({"display": ""});
            self.attachmentLinkURL.val(self.data.url);
        } else if (v == 'post') {
            self.attachmentLinkURL.prop("readonly", "readonly");
            self.attachmentLinkURL.css({"display": ""});
            self.attachmentLinkURL.val(self.data.link);
        } else if (v == 'custom') {
            self.attachmentLinkURL.prop("readonly", null);
            self.attachmentLinkURL.css({"display": ""});
            self.attachmentLinkURL.val("");
        }
    });

    this.insert = function() {
        var data = {
            "action": "send-attachment-to-editor",
            "attachment[id]": self.data.id,
            "nonce": window.parent.wp.media.view.settings.nonce.sendToEditor,
            post_id: window.parent.wp.media.model.settings.post.id,
            "attachment[post_content]": self.attachmentDescription.val(),
            "attachment[post_excerpt]": self.attachmentCaption.val(),
            "attachment[image_alt]": self.attachmentAlt.val(),
            "attachment[image-size]": self.attachmentSize.val(),
            "attachment[align]": self.attachmentAlign.val(),
            "attachment[url]": self.attachmentLinkURL.val(),
            "html": ""
        };

        $.post(ajaxurl, data, function(response){
            if (response.hasOwnProperty('data')) {
                window.parent.send_to_editor(response.data);
            }
        });
    };

    this.attachmentLinkURL.css({"display": "none"});

    uploader.attachmentContainer.empty();
    uploader.attachmentContainer.append(this.info);
};


var ilabMediaUploadItem = function($, uploader, file) {
    var self = this;

    this.cell = $(uploader.uploadItemTemplate());
    this.background = this.cell.find('.ilab-upload-item-background');
    this.status = this.cell.find('.ilab-upload-status');
    this.progress = this.cell.find('.ilab-upload-progress');
    this.progressTrack = this.cell.find('.ilab-upload-progress-track');

    this.status.text('Waiting ...');
    this.progressTrack.css({width: '0%'});
    this.cell.css({'opacity': 0});
    this.cell.addClass('no-mouse');
    
    this.state = 'waiting';
    this.postId = null;

    this.loader = this.cell.find('.ilab-loader-container');


    if ((file.type.indexOf('image/')==0) && (file.size < (15 * 1024 * 1024))) {
        this.background.css({opacity: 0.33, 'background-image': 'url('+URL.createObjectURL(file)+')'});
    } else {
        if (file.type.indexOf('image/')==0) {
            this.cell.addClass('ilab-upload-cell-image');
        } else if (file.type.indexOf('video/')==0) {
            this.cell.addClass('ilab-upload-cell-video');
        } else {
            if (file.type == 'application/x-photoshop') {
                this.cell.addClass('ilab-upload-cell-image');
            } else {
                this.cell.addClass('ilab-upload-cell-doc');
            }
        }
    }

    this.deselect = function() {
        this.cell.removeClass('ilab-upload-selected');
    };

    this.updateProgress = function(amount) {
        this.progressTrack.css({'width': (Math.floor(amount * 100) + '%')});
    };

    this.itemUploaded = function(success, importResponse) {
        if (success) {
            this.progress.css({'display': 'none'});
            this.status.css({'display': 'none'});
            this.background.css({'opacity':''});

            this.state = 'ready';
            this.postId = importResponse.data.id;
            if (importResponse.data.thumb) {
                this.loader.css({"opacity": 1});
                var image = new Image();
                image.onload=function() {
                    this.background.css({'background-image': 'url('+importResponse.data.thumb+')'});
                    this.loader.css({"opacity": 0});
                }.bind(this);

                image.src = importResponse.data.thumb;
            }

            this.cell.removeClass('no-mouse');
        } else {
            this.progress.css({'display': 'none'});
            this.status.text("Error.");
            this.cell.addClass('upload-error');
        }

        uploader.uploadFinished(this);
    };

    this.itemUploadError = function() {
        self.progress.css({'display': 'none'});
        self.status.text('Error uploading.');

        uploader.uploadFinished(self);
    };

    this.updateStatusText = function(text) {
        this.status.text('Uploading ...');
    };

    this.startUpload = function() {
        var uploader = new this.storageUploader($, this, file);
        uploader.start();
    };



    uploader.uploadTarget.append(this.cell);
    setTimeout(function(){
        self.cell.css({'opacity': ''});
    }, 1000/30);

    this.cell.on('click',function(e){
        if (self.state == 'ready') {
            if (uploader.settings.insertMode) {
                self.cell.addClass('ilab-upload-selected');
                uploader.uploadSelected(self);
            } else {
                var win = window.open('post.php?post='+self.postId+'&action=edit', '_blank');
                win.focus();
            }
        }

        e.preventDefault();
        return false;
    });

};

var ilabMediaUploader = function($, settings) {
    var self = this;

    this.insertButton = $('#ilab-insert-button');
    this.settings = settings;
    this.uploadTarget = $('#ilab-video-upload-target');
    this.attachmentContainer = $('#ilab-attachment-info');
    this.uploadDirections = this.uploadTarget.find('.ilab-upload-directions');
    this.uploadItemTemplate = wp.template('ilab-upload-cell');
    this.attachmentTemplate = wp.template('ilab-attachment-info');
    this.hiddenFileInput = $('<input type="file" style="visibility:hidden" multiple="multiple">');

    this.waitingQueue = [];
    this.uploadingQueue = [];

    this.watchToken = null;

    this.currentSelection = null;
    this.attachmentInfo = null;

    this.watchQueue = function() {
        if ((self.uploadingQueue.length < 5) && (self.waitingQueue.length>0)) {
            var currentQ = 5 - self.uploadingQueue.length;
            for(var i=0; i<currentQ; i++) {
                if (self.waitingQueue.length > 0) {
                    var up = self.waitingQueue.shift();

                    self.uploadingQueue.push(up);
                    up.startUpload();
                }
            }
        }

        self.watchToken = setTimeout(self.watchQueue, 500);
    };

    this.uploadSelected = function(upload) {
        if (self.currentSelection == upload) {
            return;
        }

        self.insertButton.prop('disabled', true);
        if (self.currentSelection) {
            self.currentSelection.deselect();
        }

        self.currentSelection = upload;

        var data = {
            "action": "ilab_upload_attachment_info",
            "postId": upload.postId
        };

        $.post(ajaxurl, data, function(response){
            $('body').addClass('ilab-item-selected');
            self.attachmentInfo = new ilabAttachmentInfo($, self, response);
            self.insertButton.prop('disabled', false);
        });
    };

    this.uploadFinished = function(upload) {
        var idx = self.uploadingQueue.indexOf(upload);
        if (idx > -1) {
            self.uploadingQueue.splice(idx, 1);
        }

        clearTimeout(self.watchToken);
        self.watchQueue();
    };

    this.addFile = function(file) {
        if (file.type=='') {
            return false;
        }

        var mimeType = file.type;


        if (mimeType == 'application/x-photoshop') {
            mimeType = 'image/psd';
        }

        if (settings.allowedMimes.indexOf(mimeType) == -1) {
            return false;
        }

        var mimeTypeParts = mimeType.split('/');
        var type = mimeTypeParts[0];
        var subType = mimeTypeParts[1];

        if (type == 'image') {
            if (!settings.imgixEnabled) {
                return false;
            }

            if (['jpeg','gif','png'].indexOf(subType) == -1) {
                if (!settings.extrasEnabled) {
                    return false;
                }

                if (['psd','tiff','bmp'].indexOf(subType) == -1) {
                    return false;
                }
            }
        } else if (type == 'video') {
            if (!settings.videoEnabled) {
                return false;
            }
        } else {
            if (!settings.docsEnabled) {
                return false;
            }
        }

        self.waitingQueue.push(new ilabMediaUploadItem($, self, file));
    };

    this.uploadTarget.on('dragenter dragover', function(e){
        self.uploadTarget.addClass('drag-inside');
        e.stopPropagation();
        e.preventDefault();
    });

    this.uploadTarget.on('dragleave drageexit', function(e){
        self.uploadTarget.removeClass('drag-inside');
        e.stopPropagation();
        e.preventDefault();
    });

    this.uploadTarget.on('drop', function(e){
        self.uploadTarget.removeClass('drag-inside');
        self.uploadDirections.css({display: 'none'});
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        _.each(files, self.addFile);
    });

    this.uploadTarget.on('click', function(e){
        self.hiddenFileInput.click();
    });

    this.hiddenFileInput.on('change', function(e){
        self.uploadDirections.css({display: 'none'});
        for(var i=0; i<this.files.length; i++) {
            self.addFile(this.files[i]);
        }
    });

    $('#ilab-open-editor').on('click',function(e){
        wp.media({frame:'select'}).open();

        e.preventDefault();
        return false;
    });

    this.insertButton.on('click', function(e){
        if (self.attachmentInfo) {
            self.attachmentInfo.insert();
        }

        e.preventDefault();
        return false;
    });

    if (settings.insertMode) {
        $('body').addClass('ilab-upload-insert-mode');
        // $('body').addClass('ilab-item-selected');
    }

    this.watchToken = setTimeout(this.watchQueue, 500);
};