//=require components/ilab-imgix-slider.js


/**
 * Image Editing Module
 * @type {{init, resetAll}}
 */
var ILabImageEdit=(function($){
    var _settings={};
    var _previewTimeout;
    var _previewsSuspended;

    /**
     * Requests a preview to be generated.
     */
    var preview=function(){
        if (_previewsSuspended)
            return;

        ILabModal.makeDirty();

        clearTimeout(_previewTimeout);
        _previewTimeout=setTimeout(_preview,500);
    };

    /**
     * Performs the wordpress ajax post
     * @param action
     * @param data
     * @param callback
     * @private
     */
    var _postAjax=function(action,data,callback){
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

        data['image_id'] = _settings.image_id;
        data['action'] = action;
        data['size'] = _settings.size;
        data['settings']=postData;

        $.post(ajaxurl, data, callback);
    };

    /**
     * Performs the actual request for a preview to be generated
     * @private
     */
    var _preview=function(){
        displayStatus('Building preview ...');

        $('#ilab-preview-wait-modal').removeClass('is-hidden');

        _postAjax('ilab_imgix_preview',{},function(response) {
            hideStatus();
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
        _previewsSuspended=false;
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

               var uploader=wp.media({
                title: 'Select Watermark',
                button: {
                    text: 'Select Watermark'
                },
                multiple: false
            });


            uploader.on('select', function() {
                attachment = uploader.state().get('selection').first().toJSON();
                $('#imgix-param-'+param).val(attachment.id);
                $('#imgix-media-preview').attr('src',attachment.url);

                preview();
            });

            uploader.open();

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

        $('.ilab-imgix-pill').on('click',function(){
            paramName=$(this).data('param');
            param=$('#imgix-param-'+paramName);
            if (param.val()==1)
            {
                param.val(0);
                $(this).removeClass('pill-selected');
            }
            else
            {
                param.val(1);
                $(this).addClass('pill-selected');
            }

            preview();
        });

        $(document).on('change','.imgix-image-size-select',function(){
            var sizeSelect=$(this);
            ILabModal.loadURL(sizeSelect.val(),true,function(response){
                bindUI(response);
            });
        });


        $(document).on('click', '.ilab-modal-editor-tab', function(e) {
            e.preventDefault();

            var currEl = $(this);
            ILabModal.loadURL(currEl.data('url'),true,function(response){
                bindUI(response);
            });

            return false;
        });

        initPresets();
    };

    /**
     * Binds the UI to the json response when selecting a tab or changing a preset
     * @param data
     */
    var bindUI=function(data){
        _previewsSuspended=true;
        resetAll();
        _settings.size=data.size;
        _settings.settings=data.settings;

        var rebind=function(){
            $('#ilab-imgix-preview-image').off('load',rebind);
            Object.keys(data.params).forEach(function(key,index){
                var paramObj=data.params[key];
                var param=$('#imgix-param-'+key);

                if (data.settings[key])
                {
                    var val=data.settings[key];

                    if (!val || (val==''))
                        return;

                    if (paramObj.type=='slider')
                    {
                        $('#imgix-current-value-'+key).text(val);
                        param.val(val);
                    }
                    else if ((paramObj.type=='color') ||  (paramObj.type=='blend-color'))
                    {
                        val=val.replace('#','');
                        if (val.length==8)
                        {
                            var alpha=(parseInt('0x'+val.substring(0,2))/255.0)*100.0;
                            val=val.substring(2);

                            param.val('#'+val);
                            param.wpColorPicker('color', '#'+val);
                            $('#imgix-param-alpha-'+key).val(Math.round(alpha));
                            $('#imgix-param-alpha-'+key).hide().show(0);
                        }
                        else
                        {
                            param.val('#'+val);
                            param.wpColorPicker('color', '#'+val);
                        }

                        if (paramObj.type=='blend-color')
                            $('#imgix-param-blend-'+key).val(data.settings[paramObj['blend-param']]);
                    }
                    else if (paramObj.type=='media-chooser')
                    {
                        param.val(val);

                        if (data.settings.media_url && (data.settings.media_url!=''))
                            $('#imgix-media-preview').attr('src',data.settings.media_url);
                    }
                    else if (paramObj.type=='alignment')
                    {
                        param.val(val);
                        $('.imgix-alignment-button').each(function(){
                            var selectButton=$(this);
                            selectButton.removeClass('selected-alignment');
                            if (selectButton.data('param-value')==val)
                                selectButton.addClass('selected-alignment');
                        });
                    }

                }
            });

            _previewsSuspended=false;
            ILabModal.makeClean();
        };

        if (data.src)
        {
            $('#ilab-imgix-preview-image').on('load',rebind);
            $('#ilab-imgix-preview-image').attr('src',data.src);
        }
        else
            rebind();
    };

    var initPresets=function(){
        $('#imgix-presets').find('option').remove();

        if (Object.keys(_settings.presets).length==0)
        {
            $('#imgix-preset-container').addClass('is-hidden');
        }
        else
        {
            Object.keys(_settings.presets).forEach(function(key,index) {
                console.log(key);
                $('#imgix-presets').append($('<option></option>')
                    .attr("value",key)
                    .text(_settings.presets[key].title));
            });

            $('#imgix-preset-container').removeClass('is-hidden');
            $('#imgix-presets').val(_settings.currentPreset);
        }


        $('#imgix-presets').on('change',function(){
            ILabModal.loadURL(_settings.presets[$('#imgix-presets').val()].url,true);
        });
    };

    var apply=function(){
        displayStatus('Saving adjustments ...');

        _postAjax('ilab_imgix_save', {}, function(response) {
            hideStatus();
            ILabModal.makeClean();

            jQuery('.ilab-modal-sidebar-actions .spinner').removeClass('is-active');
        });

    };

    /**
     * Reset all of the values
     */
    var resetAll=function(){
        $('.imgix-param-reset').click();
    };

    var newPreset=function(){
        var name=prompt("New preset name");
        if (name!=null)
        {
            displayStatus('Saving preset ...');

            var data={};
            data['name']=name;
            if ($('#imgix-preset-make-default').is(':checked'))
                data['make_default']=1;


            _postAjax('ilab_imgix_new_preset', data, function(response) {
                hideStatus();
                if (response.status=='ok')
                {
                    _settings.currentPreset=response.currentPreset;
                    _settings.presets=response.presets;

                    initPresets();
                }
            });
        }
    };

    var savePreset=function(){
        if ($('#imgix-presets').val()==null)
            return;

        displayStatus('Saving preset ...');

        var data={};
        data['key']=$('#imgix-presets').val();
        if ($('#imgix-preset-make-default').is(':checked'))
            data['make_default']=1;

        _postAjax('ilab_imgix_save_preset', data, function(response) {
            hideStatus();
        });
    };

    var deletePreset=function(){
        if ($('#imgix-presets').val()==null)
            return;

        if (!confirm("Are you sure you want to delete this preset?"))
            return;

        displayStatus('Delete preset ...');

        var data={};
        data['key']=$('#imgix-presets').val();

        _postAjax('ilab_imgix_delete_preset', data, function(response) {
            hideStatus();
            if (response.status=='ok')
            {
                _settings.currentPreset=response.currentPreset;
                _settings.presets=response.presets;

                initPresets();
            }
        });
    };

    var displayStatus=function(message){
        $('#imgix-status-label').text(message);
        $('#imgix-status-container').removeClass('is-hidden');
    };

    var hideStatus=function(){
        $('#imgix-status-container').addClass('is-hidden');
    };

    return {
        apply:apply,
        init: init,
        resetAll:resetAll,
        newPreset:newPreset,
        savePreset:savePreset,
        deletePreset:deletePreset,
        displayStatus:displayStatus,
        hideStatus:hideStatus
    }
})(jQuery);