/**
 * Created by jong on 7/29/15.
 */

var ILabCrop=function($,settings){
    this.settings=settings;
    this.modalContainer=$('#ilabm-container-'+settings.modal_id);
    this.cropper=this.modalContainer.find('.ilabc-cropper');
    this.cropperData={};
    this.modal_id=settings.modal_id;

    var cropRef=this;
    var resizeTimerId;
    var isResizing=false;

    this.modalContainer.find('.ilabm-editor-tabs').ilabTabs({
        currentValue: this.settings.size,
        tabSelected:function(tab){
            ILabModal.loadURL(tab.data('url'),true,function(response){
                console.log(response);
                cropRef.bindUI(response);
            });
        }
    });

    $(window).resize(function() {
        if (!isResizing)
        {
            data=cropRef.cropper.cropper('getData');
            cropRef.settings.prev_crop_x=data.x;
            cropRef.settings.prev_crop_y=data.y;
            cropRef.settings.prev_crop_width=data.width;
            cropRef.settings.prev_crop_height=data.height;
        }

        isResizing=true;
        cropRef.updatePreviewWidth();
        clearTimeout(resizeTimerId);
        resizeTimerId = setTimeout(cropRef._resized, 250);
    });


    this.modalContainer.find('.ilabc-button-crop').on('click',function(e){
        e.preventDefault();
        cropRef.crop();
        return false;
    });

    this._resized=function(){
        cropRef.bindUI(cropRef.settings);
        isResizing=false;
    };

    this.updatePreviewWidth=function() {
        var width =  this.modalContainer.find('.ilab-crop-preview-title').width();
        this.modalContainer.find('.ilab-crop-preview').css({
            'height' : (width / cropRef.settings.aspect_ratio) + 'px',
            'width' : width + 'px'
        });
    };

    this.bindUI=function(settings){
        this.settings=settings;

        this.cropper.cropper('destroy');
        this.cropper.off('built.cropper');

        if (settings.hasOwnProperty('cropped_src') && settings.cropped_src !== null)
        {
            this.modalContainer.find('.ilab-current-crop-img').attr('src',settings.cropped_src);
        }

        if (settings.hasOwnProperty('size_title') && (settings.size_title !== null))
        {
            this.modalContainer.find('.ilabc-crop-size-title').text("Current "+settings.size_title+" ("+settings.min_width+" x "+settings.min_height+")");
        }

        if (typeof settings.aspect_ratio !== 'undefined')
        {
            this.updatePreviewWidth();

            if ((typeof settings.prev_crop_x !== 'undefined') && (settings.prev_crop_x !== null)) {
                this.cropperData = {
                    x : settings.prev_crop_x,
                    y : settings.prev_crop_y,
                    width : settings.prev_crop_width,
                    height : settings.prev_crop_height
                };
                console.log(this.cropperData);
            }

            this.cropper.on('built.cropper',function(){
                cropRef.updatePreviewWidth();
            }).on('crop.cropper',function(e){
                //console.log(e.x, e.y, e.width, e.height);
            }).cropper({
                viewMode: 1,
                aspectRatio : settings.aspect_ratio,
                minWidth : settings.min_width,
                minHeight : settings.min_height,
                modal : true,
                zoomable: false,
                mouseWheelZoom: false,
                dragCrop: false,
                autoCropArea: 1,
                movable: false,
                data : this.cropperData,
                checkImageOrigin: false,
                checkCrossOrigin: false,
                responsive: true,
                preview: '#ilabm-container-'+this.modal_id+' .ilab-crop-preview'
            });
        }
    };

    this.crop=function(){
        var cropRef=this;

        this.displayStatus('Saving crop ...');

        var data = this.cropper.cropper('getData');
        data['action'] = 'ilab_perform_crop';
        data['post'] = this.settings.image_id;
        data['size'] = this.settings.size;
        jQuery.post(ajaxurl, data, function(response) {
            console.log(response);
            if (response.status=='ok') {
                cropRef.modalContainer.find('.ilab-current-crop-img').one('load',function(){
                   cropRef.hideStatus();
                });
                cropRef.modalContainer.find('.ilab-current-crop-img').attr('src', response.src);
            }
            else
                cropRef.hideStatus();
        });
    };

    this.displayStatus=function(message){
        cropRef.modalContainer.find('.ilabm-status-label').text(message);
        cropRef.modalContainer.find('.ilabm-status-container').removeClass('is-hidden');
    };

    this.hideStatus=function(){
        cropRef.modalContainer.find('.ilabm-status-container').addClass('is-hidden');
    };

    this.bindUI(settings);
};
