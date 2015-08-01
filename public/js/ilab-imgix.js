/**
 * Created by jong on 7/31/15.
 */
var ILabImageEdit=(function(){
    var _data={};

    var preview=function(){
        var postData={};
        jQuery('.imgix-param').each(function(){
            var param=jQuery(this);
            val=param.val();

            if (val!=param.data('default-value'))
                postData[param.attr('name')]=param.val();
        });

        data={};
        data['action'] = 'ilab_imgix_preview';
        data['image_id'] = _data.image_id;
        data['settings']=postData;
        jQuery('#ilab-preview-wait-modal').removeClass('is-hidden');
        jQuery.post(ajaxurl, data, function(response) {
            if (response.status=='ok')
            {
                jQuery('#ilab-imgix-preview-image').on('load',function(){
                    jQuery('#ilab-preview-wait-modal').addClass('is-hidden');
                });

                jQuery('#ilab-imgix-preview-image').attr('src',response.src);
            }
            else
            {
                jQuery('#ilab-preview-wait-modal').addClass('is-hidden');
            }
        });

    };

    var resetAll=function(){

    };

    var init=function(settings) {
        _data=settings;

        jQuery('.imgix-param').each(function(){
            jQuery(this).on('change',function(e){
                preview();
            });
        });

        jQuery('.imgix-param-reset').on('click',function(){
            paramName=jQuery(this).data('param');
            param=jQuery('#imgix-param-'+paramName);
            param.val(param.data('default-value'));
            preview();
        });
    };

    return {
        init: init,
        resetAll:resetAll
    }
})();

jQuery(document).ready(function(){

});