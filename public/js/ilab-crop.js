/**
 * Created by jong on 7/29/15.
 */

var ILabCrop=(function(){
    var _data={};

    var cancel=function(){
        jQuery('#ilab-crop-wrapper').remove();
    };

    var updatePreviewWidth=function() {
        var width = jQuery('#ilab-crop-preview-title').width();
        jQuery('#ilab-crop-preview').css({
            'height' : (width / _data.aspect_ratio) + 'px',
            'width' : width + 'px'
        });
    };

    var init=function(settings){
        _data=settings;

        jQuery(document).ready(function($){
            if (typeof _data.aspect_ratio !== 'undefined')
            {
                updatePreviewWidth();

                var cropperData;
                if (typeof _data.prev_crop_x !== 'undefined') {
                    cropperData = {
                        x : _data.prev_crop_x,
                        y : _data.prev_crop_y,
                        width : _data.prev_crop_width,
                        height : _data.prev_crop_height
                    };
                    console.log(cropperData);
                } else {
                    cropperData = {};
                }

                jQuery('#ilab-crop-container').css({
                    'max-width' : jQuery('#ilab-crop-wrapper .attachments').width() + 'px',
                    'max-height' : jQuery('#ilab-crop-wrapper .attachments').height() + 'px'
                });

                jQuery('#ilab-cropper').on('built.cropper', function() {
                    updatePreviewWidth();
                }).cropper({
                    aspectRatio : _data.aspect_ratio,
                    minWidth : _data.min_width,
                    minHeight : _data.min_height,
                    modal : true,
                    zoomable: false,
                    mouseWheelZoom: false,
                    dragCrop: false,
                    autoCropArea: 1,
                    movable: false,
                    data : cropperData,
                    preview: '#ilab-crop-preview'
                });

                jQuery(window).resize(function(){
                    updatePreviewWidth();
                    data=jQuery('#ilab-cropper').cropper('getData');
                    jQuery('#ilab-cropper').cropper('reset');
                    jQuery('#ilab-cropper').cropper('setData',data);
                });
            }
        });
    };

    var crop=function(){
        jQuery('#ilab-crop-wrapper .spinner').addClass('is-active');

        var data = jQuery('#ilab-cropper').cropper('getData');
        data['action'] = 'ilab_perform_crop';
        data['post'] = _data.image_id;
        data['size'] = _data.size;
        jQuery.post(ajaxurl, data, function(response) {
            if (response.status=='ok')
                jQuery('#ilab-current-crop-img').attr('src',response.src);

            jQuery('#ilab-crop-wrapper .spinner').removeClass('is-active');
            jQuery(window).resize();
        });
    };

    return {
        cancel: cancel,
        crop: crop,
        init: init
    };
})();

jQuery(document).ready(function($){
    $(document).on('click', 'a.ilab-thickbox', function(e) {
        e.preventDefault();
        var currEl = $(this);
        var partial=currEl.hasClass('ilab-thickbox-partial');
        jQuery.get(currEl.attr('href'), function(data) {
            if (partial) {
                jQuery('#ilab-crop-wrapper .media-modal-content').empty().append(data);
            } else {
                jQuery('body').append(data);
            }
        });

        return false;
    });
});