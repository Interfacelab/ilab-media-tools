/**
 * Created by jong on 7/31/15.
 */

/**
 * Image Editing Module
 * @type {{init, resetAll}}
 */
var ILabImageEdit=(function($){
    var _settings={};
    var _previewTimeout;

    /**
     * Requests a preview to be generated.
     */
    var preview=function(){
        clearTimeout(_previewTimeout);
        _previewTimeout=setTimeout(_preview,500);
    };

    /**
     * Performs the actual request for a preview to be generated
     */
    var _preview=function(){
        var postData={};
        $('.imgix-param').each(function(){
            var param=$(this);
            var paramType=param.data('param-type');
            var val=param.val();

            if ((paramType=='color') || (paramType=='blend-color'))
            {
                var paramAlpha=$('#imgix-param-alpha-'+param.attr('name'));
                var alpha=parseInt((paramAlpha.val()/100.0)*255);
                if (alpha==0)
                    return;

                val=val.substring(1);
                var hexChar = ["0", "1", "2", "3", "4", "5", "6", "7","8", "9", "A", "B", "C", "D", "E", "F"];
                val='#'+hexChar[(alpha >> 4) & 0x0f] + hexChar[alpha & 0x0f]+val;
            }

            if (paramType=='blend-color')
            {
                var paramBlend=$('#imgix-param-blend-'+param.attr('name'));
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

        var data={};
        data['action'] = 'ilab_imgix_preview';
        data['image_id'] = _settings.image_id;
        data['settings']=postData;

        $('#ilab-preview-wait-modal').removeClass('is-hidden');

        $.post(ajaxurl, data, function(response) {
            if (response.status=='ok')
            {
                if (_settings.debug)
                    console.log(response.src);

                $('#ilab-imgix-preview-image').on('load',function(){
                    $('#ilab-preview-wait-modal').addClass('is-hidden');
                });

                $('#ilab-imgix-preview-image').attr('src',response.src);
            }
            else
            {
                $('#ilab-preview-wait-modal').addClass('is-hidden');
            }
        });

    };

    /**
     * Setup the tabs
     * @private
     */
    var _setupTabs=function() {
        var firstTab=false;
        $(".ilab-modal-tab").each(function(){
            var tab=$(this);
            var target=$('#'+tab.data('target'));

            if (!firstTab)
            {
                tab.addClass('active-tab');
                target.removeClass('is-hidden');

                firstTab=true;
            }

            tab.on('click',function(e){
                e.preventDefault();

                $(".ilab-modal-tab").each(function() {
                    var otherTab = $(this);
                    var tabTarget = $('#' + otherTab.data('target'));

                    otherTab.removeClass('active-tab');
                    tabTarget.addClass('is-hidden');
                });

                tab.addClass('active-tab');
                target.removeClass('is-hidden');

                return false;
            });
        });
    };

    /**
     * Initialize the whole thing
     * @param settings
     */
    var init=function(settings) {
        _settings=settings;

        _setupTabs();

        $('.imgix-param-color').wpColorPicker({
           palettes: false,
            change: function(event, ui) {
                preview();
            }
        });

        $('.imgix-param').each(function(){
            var param=$(this);
            var paramIsColor=((param.data('param-type')=='color') || (param.data('param-type')=='blend-color'));
            var paramValueDisplay=$('#imgix-current-value-'+param.attr('name'));

            paramValueDisplay.text(param.val());
            param.on('change',function(e){
                param.hide().show(0);
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

        $('.imgix-param-alpha').on('change',function(){
            preview();
        });

        $('.imgix-param-blend').on('change',function(){
            preview();
        });

        $('.imgix-param-reset').on('click',function(){
            var paramName=$(this).data('param');
            var paramValueDisplay=$('#imgix-current-value-'+paramName);
            var param=$('#imgix-param-'+paramName);
            var paramType=param.data('param-type');

            if (paramType=='slider')
            {
                param.val(param.data('default-value'));
                paramValueDisplay.text(param.data('default-value'));
                param.hide().show(0);
            }
            else if (paramType=='color')
            {
                param.val('#FF0000');
                param.wpColorPicker('color', '#FF0000');
                $('#imgix-param-alpha-'+paramName).val(0);
                $('#imgix-param-alpha-'+paramName).hide().show(0);
            }
            else if (paramType=='blend-color')
            {
                param.val('#FF0000');
                param.wpColorPicker('color', '#FF0000');
                $('#imgix-param-alpha-'+paramName).val(0);
                $('#imgix-param-alpha-'+paramName).hide().show(0);
                $('#imgix-param-blend-'+paramName).val('none');
            }
            else if (paramType=='alignment')
            {
                param.val('bottom,right');
                $('.imgix-alignment-button').each(function(){
                    var selectButton=$(this);
                    selectButton.removeClass('selected-alignment');
                    if (selectButton.data('param-value')=='bottom,right')
                        selectButton.addClass('selected-alignment');
                });
            }
            else if (paramType=='media-chooser')
            {
                $('#imgix-param-'+paramName).val('');
                $image=$('#imgix-media-preview');
                $image.removeAttr('src').replaceWith($image.clone());
            }

            preview();
        });

        $('.imgix-media-button').on('click',function(){
            var selectButton=$(this);
            var param=selectButton.data('param');

            var send_attachment_bkp = wp.media.editor.send.attachment;

            wp.media.editor.send.attachment = function(props, attachment) {

                $('#imgix-param-'+param).val(attachment.id);
                $('#imgix-media-preview').attr('src',attachment.url);

                preview();

                wp.media.editor.send.attachment = send_attachment_bkp;
            }

            wp.media.editor.open();

            return false;
        });

        $('.imgix-alignment-button').on('click',function(){
            var selectButton=$(this);
            var param=selectButton.data('param');
            $('.imgix-alignment-button').each(function(){
                $(this).removeClass('selected-alignment');
            });
            selectButton.addClass('selected-alignment');
            $('#imgix-param-'+param).val(selectButton.data('param-value'));
            preview();
        });
    };

    /**
     * Reset all of the values
     */
    var resetAll=function(){
        $('.imgix-param-reset').click();
    };

    return {
        init: init,
        resetAll:resetAll
    }
})(jQuery);

jQuery(document).ready(function(){

});