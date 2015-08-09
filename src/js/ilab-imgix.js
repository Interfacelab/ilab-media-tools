/**
 * Image Editing Module
 * @type {{init, resetAll}}
 */
var ILabImageEdit=(function($){
    var _settings={};
    var _previewTimeout;
    var _previewsSuspended;
    var _parameters=[];

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
        _parameters.forEach(function(value,index){
            postData=value.saveValue(postData);
        });

        console.log(postData);

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

        var self=this;

        $('.ilab-imgix-parameter').each(function(){
            var container=$(this);
            var type=container.data('param-type');
            if (type=='slider')
                _parameters.push(new ImgixComponents.ImgixSlider(self,container));
            else if ((type=='color') || (type=='blend-color'))
                _parameters.push(new ImgixComponents.ImgixColor(self,container));
            else if (type=='media-chooser')
                _parameters.push(new ImgixComponents.ImgixMediaChooser(self,container));
            else if (type=='alignment')
                _parameters.push(new ImgixComponents.ImgixAlignment(self,container));
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

        $('.ilab-modal-editor-tabs').ilabTabs({
            currentValue: _settings.size,
            tabSelected:function(tab){
                ILabModal.loadURL(tab.data('url'),true,function(response){
                    bindUI(response);
                });
            }
        });

        //$(document).on('change','.imgix-image-size-select',function(){
        //    var sizeSelect=$(this);
        //    ILabModal.loadURL(sizeSelect.val(),true,function(response){
        //        bindUI(response);
        //    });
        //});
        //
        //
        //$(document).on('click', '.ilab-modal-editor-tab', function(e) {
        //    e.preventDefault();
        //
        //    var currEl = $(this);
        //    ILabModal.loadURL(currEl.data('url'),true,function(response){
        //        bindUI(response);
        //    });
        //
        //    return false;
        //});

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
            _parameters.forEach(function(value,index){
               value.reset(data.settings);
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
        });

    };

    /**
     * Reset all of the values
     */
    var resetAll=function(){
        _parameters.forEach(function(value,index){
            value.reset();
        });
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
        hideStatus:hideStatus,
        preview:preview
    }
})(jQuery);