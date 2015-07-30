/**
 * Created by jong on 7/29/15.
 */

function ilabCancelCropImage() {
    jQuery('#ilab-crop-wrapper').remove();
}

function ilabCropImage() {
    jQuery('#ilab-crop-wrapper .spinner').addClass('is-active');

    var data = jQuery('#ilab-cropper').cropper('getData');
    data['action'] = 'ilab_perform_crop';
    data['post'] = ilab_image_id;
    data['size'] = ilab_image_size;
    console.log(data);
    jQuery.post(ajaxurl, data, function(response) {
        console.log(response);
        if (response.status=='ok')
            jQuery('#ilab-current-crop-img').attr('src',response.src);

        jQuery('#ilab-crop-wrapper .spinner').removeClass('is-active');
        jQuery(window).resize();

    });
}
function ilabInitCrop()
{
    jQuery(document).ready(function($){
        if (typeof ilab_cropper_aspect_ratio !== 'undefined')
        {
            function adaptCropPreviewWidth() {
                var width = jQuery('#ilab-crop-preview-title').width();
                jQuery('#ilab-crop-preview').css({
                    'height' : (width / ilab_cropper_aspect_ratio) + 'px',
                    'width' : width + 'px'
                });
            }
            adaptCropPreviewWidth();

            var cropperData;
            if (typeof ilab_prev_crop_x !== 'undefined') {
                cropperData = {
                    x : ilab_prev_crop_x,
                    y : ilab_prev_crop_y,
                    width : ilab_prev_crop_width,
                    height : ilab_prev_crop_height
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
                adaptCropPreviewWidth();
            }).cropper({
                aspectRatio : ilab_cropper_aspect_ratio,
                minWidth : ilab_cropper_min_width,
                minHeight : ilab_cropper_min_height,
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
                adaptCropPreviewWidth();
                data=jQuery('#ilab-cropper').cropper('getData');
                console.log(data);
                jQuery('#ilab-cropper').cropper('reset');
                jQuery('#ilab-cropper').cropper('setData',data);
            });
        }
    });
}

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