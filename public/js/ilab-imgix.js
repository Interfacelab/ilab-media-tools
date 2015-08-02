/**
 * Created by jong on 7/31/15.
 */
var ILabImageEdit=(function(){
    var _data={};
    var _previewTimeout;

    var preview=function(){
        clearTimeout(_previewTimeout);
        _previewTimeout=setTimeout(doPreview,500);
    };

    var doPreview=function(){
        var postData={};
        jQuery('.imgix-param').each(function(){
            var param=jQuery(this);
            var paramIsColor=((param.data('param-type')=='color') || (param.data('param-type')=='blend-color'));
            val=param.val();

            if (paramIsColor)
            {
                var paramAlpha=jQuery('#imgix-param-alpha-'+param.attr('name'));
                alpha=parseInt((paramAlpha.val()/100.0)*255);
                if (alpha==0)
                    return;

                val=val.substring(1);
                var hexChar = ["0", "1", "2", "3", "4", "5", "6", "7","8", "9", "A", "B", "C", "D", "E", "F"];
                val='#'+hexChar[(alpha >> 4) & 0x0f] + hexChar[alpha & 0x0f]+val;
            }

            if (param.data('param-type')=='blend-color')
            {
                var paramBlend=jQuery('#imgix-param-blend-'+param.attr('name'));
                var paramBlendParam=paramBlend.data('blend-param');
                var blendVal=paramBlend.val();
                if (blendVal!='none')
                {
                    postData[paramBlendParam]=blendVal;
                }
            }

            if (val!=param.data('default-value'))
                postData[param.attr('name')]=val;
        });

        data={};
        data['action'] = 'ilab_imgix_preview';
        data['image_id'] = _data.image_id;
        data['settings']=postData;
        jQuery('#ilab-preview-wait-modal').removeClass('is-hidden');
        jQuery.post(ajaxurl, data, function(response) {
            if (response.status=='ok')
            {
                console.log(response.src);
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

        var firstTab=false;
        jQuery(".ilab-modal-tab").each(function(){
            var thisTab=jQuery(this);
            var target=jQuery('#'+thisTab.data('target'));

            if (!firstTab)
            {
                thisTab.addClass('active-tab');
                target.removeClass('is-hidden');

                firstTab=true;
            }

            thisTab.on('click',function(e){
                e.preventDefault();

                jQuery(".ilab-modal-tab").each(function() {
                    var tab = jQuery(this);
                    var tabTarget = jQuery('#' + tab.data('target'));

                    tab.removeClass('active-tab');
                    tabTarget.addClass('is-hidden');
                });

                thisTab.addClass('active-tab');
                target.removeClass('is-hidden');

                return false;
            });
        });

        jQuery('.imgix-param-color').wpColorPicker({
           palettes: false,
            change: function(event, ui) {
                preview();
            }
        });

        jQuery('.imgix-param').each(function(){
            var param=jQuery(this);
            var paramIsColor=((param.data('param-type')=='color') || (param.data('param-type')=='blend-color'));
            var paramValueDisplay=jQuery('#imgix-current-value-'+param.attr('name'));

            paramValueDisplay.text(param.val());
            param.on('change',function(e){
                preview();
                if (paramValueDisplay)
                    paramValueDisplay.text(param.val());
            });
            param.on('input',function(e){
                if (paramIsColor)
                    return;

                if (paramValueDisplay)
                    paramValueDisplay.text(param.val());
            });
        });

        jQuery('.imgix-param-alpha').each(function(){
            var param=jQuery(this);
            param.on('change',function(e){
                preview();
            });
        });

        jQuery('.imgix-param-blend').each(function(){
            var param=jQuery(this);
            param.on('change',function(e){
                preview();
            });
        });

        jQuery('.imgix-param-reset').on('click',function(){
            paramName=jQuery(this).data('param');
            paramValueDisplay=jQuery('#imgix-current-value-'.paramName);
            param=jQuery('#imgix-param-'+paramName);
            param.val(param.data('default-value'));
            paramValueDisplay.text(param.data('default-value'));
            preview();
        });

        jQuery('.imgix-media-button').on('click',function(){
            var selectButton=jQuery(this);
            var param=selectButton.data('param');

            var send_attachment_bkp = wp.media.editor.send.attachment;

            wp.media.editor.send.attachment = function(props, attachment) {

                jQuery('#imgix-param-'+param).val(attachment.id);
                jQuery('#imgix-media-preview').attr('src',attachment.url);

                preview();

                wp.media.editor.send.attachment = send_attachment_bkp;
            }

            wp.media.editor.open();

            return false;
        });

        jQuery('.imgix-alignment-button').on('click',function(){
            var selectButton=jQuery(this);
            var param=selectButton.data('param');
            jQuery('.imgix-alignment-button').each(function(){
                jQuery(this).removeClass('selected-alignment');
            });
            selectButton.addClass('selected-alignment');
            jQuery('#imgix-param-'+param).val(selectButton.data('param-value'));
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