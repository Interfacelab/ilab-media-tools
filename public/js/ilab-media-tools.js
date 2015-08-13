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

        if (settings.hasOwnProperty('cropped_src') && settings.cropped_src!=null)
        {
            this.modalContainer.find('.ilab-current-crop-img').attr('src',settings.cropped_src);
        }

        if (settings.hasOwnProperty('size_title') && (settings.size_title!=null))
        {
            this.modalContainer.find('.ilabc-crop-size-title').text("Current "+settings.size_title+" ("+settings.min_width+" x "+settings.min_height+")");
        }

        if (typeof settings.aspect_ratio !== 'undefined')
        {
            this.updatePreviewWidth();

            if ((typeof settings.prev_crop_x !== 'undefined') && (settings.prev_crop_x!=null)) {
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

/**
 * Created by jong on 8/8/15.
 */

var ImgixComponents=(function(){
    var byteToHex=function(byte) {
        var hexChar = ["0", "1", "2", "3", "4", "5", "6", "7","8", "9", "A", "B", "C", "D", "E", "F"];
        return hexChar[(byte >> 4) & 0x0f] + hexChar[byte & 0x0f];
    };

    return {
        utilities: {
          byteToHex:byteToHex
      }
    };
})();
(function($){

    $.fn.imgixLabel=function(options){
        var settings= $.extend({},options);

        return this.each(function(){
            var label=$(this);

            var changeTimerId;

            var currentVal=0;

            var textInput=$('<input type="text" class="imgix-label-editor is-hidden" pattern="[0-9-]+">');
            label.parent().append(textInput);

            textInput.on('keydown',function(e){
                if (e.keyCode==27) {
                    textInput.off('blur');
                    textInput.off('input');

                    textInput.addClass('is-hidden');
                    if (settings.hasOwnProperty('changed'))
                        settings.changed(currentVal);

                    label.text(currentVal);
                }
                else if (e.keyCode==13) {
                    textInput.off('blur');
                    textInput.off('input');

                    var val=parseInt(textInput.val());
                    textInput.addClass('is-hidden');
                    if (settings.hasOwnProperty('changed'))
                        settings.changed(val);

                    label.text(val);
                }
                else if (e.keyCode==38) {
                    var val=parseInt(textInput.val());
                    val++;
                    textInput.val(val);
                    if (settings.hasOwnProperty('changed'))
                        settings.changed(val);
                    label.text(val);
                }
                else if (e.keyCode==40) {
                    var val=parseInt(textInput.val());
                    val--;
                    textInput.val(val);
                    if (settings.hasOwnProperty('changed'))
                        settings.changed(val);
                    label.text(val);

                }
                else {
                    if (e.keyCode<57)
                        return true;
                    else if ((e.keyCode>90) && (e.keyCode<105))
                        return true;
                    else if (e.keyCode==109)
                        return true;
                    else if (e.metaKey)
                        return true;

                    e.preventDefault();
                    return false;
                }
            });

            label.on('click',function(e){
                e.preventDefault();

                textInput.on('input',function(){
                    var val=parseInt(textInput.val());
                    if (settings.hasOwnProperty('changed'))
                    {
                        clearTimeout(changeTimerId);
                        changeTimerId = setTimeout(function(){
                            settings.changed(val);
                        }, 500);
                    }

                    label.text(val);
                });

                textInput.on('blur',function(){
                    var val=parseInt(textInput.val());
                    textInput.addClass('is-hidden');
                    if (settings.hasOwnProperty('changed'))
                        settings.changed(val);

                    label.text(val);
                });

                currentVal=(settings.hasOwnProperty('currentValue')) ? settings.currentValue() : 0;
                textInput.val(currentVal);
                textInput.removeClass('is-hidden');
                textInput.select();
                textInput.focus();

                return false;
            });
        });
    };

}(jQuery));


(function($){
    ImgixComponents.ImgixSlider=function(delegate, container)
    {
        this.delegate=delegate;
        this.container=container;
        this.valueLabel=container.find('.imgix-param-title-right > h3');
        this.slider=container.find('.imgix-param');
        this.resetButton=container.find('.imgix-param-reset');

        this.defaultValue=container.data('default-value');
        this.param=container.data('param');

        var sliderRef=this;

        this.container.find('.imgix-param-label').imgixLabel({
            currentValue:function(){
                return sliderRef.slider.val();
            },
            changed:function(newVal){
                if (newVal==sliderRef.slider.val())
                    return;

                sliderRef.slider.val(newVal);
                sliderRef.slider.hide().show(0);
                sliderRef.delegate.preview();
            }
        });

        this.resetButton.on('click',function(){
            sliderRef.reset();
        });

        this.slider.on('input',function(){
            sliderRef.valueLabel.text(sliderRef.slider.val());
        });

        this.slider.on('change',function(){
            sliderRef.valueLabel.text(sliderRef.slider.val());
            sliderRef.delegate.preview();
        });
    };

    ImgixComponents.ImgixSlider.prototype.destroy=function() {
        this.slider.off('input');
        this.slider.off('change');
        this.resetButton.off('click');
    };

    ImgixComponents.ImgixSlider.prototype.reset=function(data) {
        var val;

        if (data && data.hasOwnProperty(this.param))
            val=data[this.param];
        else
            val=this.defaultValue;

        this.valueLabel.text(val);
        this.slider.val(val);
        this.slider.hide().show(0);

        this.delegate.preview();
    };

    ImgixComponents.ImgixSlider.prototype.saveValue=function(data) {
        if (this.slider.val()!=this.defaultValue)
            data[this.param]=this.slider.val();

        return data;
    };

}(jQuery));

(function($){

    ImgixComponents.ImgixColor=function(delegate, container)
    {
        this.delegate=delegate;
        this.container=container;
        this.colorPicker=container.find('.imgix-param-color');
        this.alphaSlider=container.find('.imgix-param-alpha');
        this.type=container.data('param-type');
        this.resetButton=container.find('.imgix-param-reset');
        this.param=container.data('param');
        this.defaultValue=container.data('default-value');

        var colorPickerRef=this;

        if (this.type=='blend-color') {
            this.blendParam=container.data('blend-param');
            this.blendSelect = container.find('.imgix-param-blend');

            var currentBlend=container.data('blend-value');
            this.blendSelect.val(currentBlend);

            this.blendSelect.on('change',function(){
                colorPickerRef.delegate.preview();
            });
        }

        this.colorPicker.wpColorPicker({
            palettes: false,
            change: function(event, ui) {
                colorPickerRef.delegate.preview();
            }
        });

        this.alphaSlider.on('change',function(){
            colorPickerRef.delegate.preview();
        });

        this.resetButton.on('click',function(){
            colorPickerRef.reset();
        });
    };

    ImgixComponents.ImgixColor.prototype.destroy=function() {
        this.alphaSlider.off('change');
        if (this.type=='blend-color') {
            this.blendSelect.off('change');
        }
        this.resetButton.off('click');
    };

    ImgixComponents.ImgixColor.prototype.reset=function(data) {
        var blend='none';
        var val;

        if (data && data.hasOwnProperty(this.blendParam))
        {
            blend=data[this.blendParam];
        }

        if (data && data.hasOwnProperty(this.param))
        {
            val=data[this.param];
        }
        else
            val=this.defaultValue;

        val=val.replace('#','');
        if (val.length==8)
        {
            var alpha=(parseInt('0x'+val.substring(0,2))/255.0)*100.0;
            val=val.substring(2);

            this.alphaSlider.val(Math.round(alpha));
            this.alphaSlider.hide().show(0);
        }

        this.colorPicker.val('#'+val);
        this.colorPicker.wpColorPicker('color', '#'+val);

        if (this.type=='blend-color') {
            this.blendSelect.val(blend);
        }

        this.delegate.preview();
    };

    ImgixComponents.ImgixColor.prototype.saveValue=function(data) {
        if (this.alphaSlider.val()>0) {
            data[this.param] = '#' + ImgixComponents.utilities.byteToHex(Math.round((parseFloat(this.alphaSlider.val()) / 100.0) * 255.0)) + this.colorPicker.val().replace('#', '');

            if (this.type == 'blend-color') {
                if (this.blendSelect.val()!='none') {
                    data[this.blendParam] = this.blendSelect.val();
                }
            }
        }

        return data;
    };

}(jQuery));

(function($){

    ImgixComponents.ImgixAlignment=function(delegate, container)
    {
        this.delegate=delegate;
        this.container=container;
        this.alignmentParam=container.find('.imgix-param');
        this.resetButton=container.find('.imgix-param-reset');
        this.defaultValue=container.data('default-value');
        this.param=container.data('param');

        var alignmentRef=this;

        this.resetButton.on('click',function(){
            alignmentRef.reset();
        });

        container.find('.imgix-alignment-button').on('click',function(){
            var button=$(this);
            alignmentRef.container.find('.imgix-alignment-button').each(function(){
                $(this).removeClass('selected-alignment');
            });

            button.addClass('selected-alignment');
            alignmentRef.alignmentParam.val(button.data('param-value'));
            alignmentRef.delegate.preview();
        });
    };

    ImgixComponents.ImgixAlignment.prototype.destroy=function() {
        this.resetButton.off('click');
        this.container.find('.imgix-alignment-button').off('click');
    };

    ImgixComponents.ImgixAlignment.prototype.reset=function(data) {
        var val;

        if (data && data.hasOwnProperty(this.param))
            val=data[this.param];
        else
            val=this.defaultValue;

        if (val=='')
            val=this.defaultValue;

        this.container.find('.imgix-alignment-button').each(function(){
            var button=$(this);
            if (button.data('param-value')==val)
                button.addClass('selected-alignment');
            else
                button.removeClass('selected-alignment');
        });

        this.alignmentParam.val(val);
        this.delegate.preview();
    };

    ImgixComponents.ImgixAlignment.prototype.saveValue=function(data) {
        if (this.alignmentParam.val()!=this.defaultValue)
            data[this.param]=this.alignmentParam.val();

        return data;
    };
}(jQuery));

(function($){

    ImgixComponents.ImgixMediaChooser=function(delegate, container)
    {
        this.delegate=delegate;
        this.container=container;
        this.preview=container.find('.imgix-media-preview img');
        this.mediaInput=container.find('.imgix-param');
        this.selectButton=container.find('.imgix-media-button');
        this.resetButton=container.find('.imgix-param-reset');

        this.defaultValue=container.data('default-value');
        this.param=container.data('param');

        this.uploader=wp.media({
            title: 'Select Watermark',
            button: {
                text: 'Select Watermark'
            },
            multiple: false
        });

        var mediaRef=this;

        this.resetButton.on('click',function(){
            mediaRef.reset();
        });

        this.uploader.on('select', function() {
            attachment = mediaRef.uploader.state().get('selection').first().toJSON();
            mediaRef.mediaInput.val(attachment.id);
            mediaRef.preview.attr('src',attachment.url);

            mediaRef.delegate.preview();
        });

        this.selectButton.on('click',function(e){
            e.preventDefault();
            mediaRef.uploader.open();
            return false;
        });

    };

    ImgixComponents.ImgixMediaChooser.prototype.destroy=function() {
        this.selectButton.off('click');
        this.uploader.off('select');
        this.resetButton.off('click');
    };

    ImgixComponents.ImgixMediaChooser.prototype.reset=function(data) {
        var val;

        if (data && data.hasOwnProperty(this.param))
        {
            val=data[this.param];
            this.mediaInput.val(val);
        }
        else
            this.mediaInput.val('');

        if (data && data.hasOwnProperty(this.param+'_url'))
        {
            this.preview.attr('src',data[this.param+'_url']);
        }
        else
        {
            this.preview.removeAttr('src').replaceWith(this.preview.clone());
            this.preview=this.container.find('.imgix-media-preview img');
        }

        this.delegate.preview();
    };

    ImgixComponents.ImgixMediaChooser.prototype.saveValue=function(data) {
        var val=this.mediaInput.val();

        if (val && val!='')
            data[this.param]=val;

        return data;
    };

}(jQuery));


(function($){
    ImgixComponents.ImgixPillbox=function(delegate, container)
    {
        this.delegate=delegate;
        this.container=container;
        this.param=container.data('param');
        this.values=container.data('param-values').split(',');
        this.buttons=container.find('.ilabm-pill');
        this.inputs={};

        var pillboxRef=this;

        this.buttons.each(function(){
            var button=$(this);
            var valueName=button.data('param');
            pillboxRef.inputs[valueName]=pillboxRef.container.find("input[name='"+valueName+"']");
            button.on('click',function(e){
                e.preventDefault();

                if (pillboxRef.inputs[valueName].val()==0)
                {
                    pillboxRef.inputs[valueName].val(1);
                    button.addClass('pill-selected');
                }
                else
                {
                    pillboxRef.inputs[valueName].val(0);
                    button.removeClass('pill-selected');
                }

                pillboxRef.delegate.preview();

                return false;
            });
        });
    };

    ImgixComponents.ImgixPillbox.prototype.destroy=function() {
        this.buttons.off('click');
    };

    ImgixComponents.ImgixPillbox.prototype.reset=function(data) {
        this.buttons.each(function(){
           $(this).removeClass('pill-selected');
        });

        var pillboxRef=this;
        Object.keys(this.inputs).forEach(function(key,index){
            pillboxRef.inputs[key].val(0);
        });

        if (data && data.hasOwnProperty(this.param)) {
            var val = data[this.param].split(',');


            val.forEach(function (key, index) {
                pillboxRef.inputs[key].val(1);
                pillboxRef.container.find('imgix-pill-' + key).addClass('pill-selected');
            });
        }

        this.delegate.preview();
    };

    ImgixComponents.ImgixPillbox.prototype.saveValue=function(data) {
        var vals=[];

        var pillboxRef=this;
        Object.keys(this.inputs).forEach(function(key,index){
            if (pillboxRef.inputs[key].val()==1)
                vals.push(key);
        });

        if (vals.length>0)
            data[this.param]=vals.join(',');

        return data;
    };

}(jQuery));

/**
 * Created by jong on 8/9/15.
 */

var ILabImgixPresets=function($,delegate,container) {

    this.delegate=delegate;
    this.container=container.find('.ilabm-bottom-bar');
    this.presetSelect=this.container.find('.imgix-presets');
    this.presetContainer=this.container.find('.imgix-preset-container');
    this.presetDefaultCheckbox=this.container.find('.imgix-preset-make-default');

    var self=this;

    self.presetSelect.on('change',function(){
        if (self.presetSelect.val==0)
        {
            self.delegate.resetAll();
            self.presetDefaultCheckbox.prop('checked',false);
            return;
        }

        var preset=self.delegate.settings.presets[self.presetSelect.val()];
        if (preset.default_for==self.delegate.settings.size)
            self.presetDefaultCheckbox.prop('checked',true);

        self.delegate.bindPreset(preset);
    });

    this.container.find('.imgix-new-preset-button').on('click',function(){
        self.newPreset();
    });

    this.container.find('.imgix-save-preset-button').on('click',function(){
        self.savePreset();
    });

    this.container.find('.imgix-delete-preset-button').on('click',function(){
        self.deletePreset();
    });

    this.init=function() {
        self.presetSelect.find('option').remove();

        if (Object.keys(self.delegate.settings.presets).length==0)
        {
            self.presetContainer.addClass('is-hidden');
        }
        else
        {
            Object.keys(self.delegate.settings.presets).forEach(function(key,index) {
                console.log(key);

                self.presetSelect.append($('<option></option>')
                    .attr("value",'0')
                    .text('None'));

                self.presetSelect.append($('<option></option>')
                    .attr("value",key)
                    .text(self.delegate.settings.presets[key].title));
            });

            self.presetContainer.removeClass('is-hidden');
            self.presetSelect.val(self.delegate.settings.currentPreset);
        }
    };

    this.clearSelected=function(){
        self.presetSelect.val(0);
        self.presetDefaultCheckbox.prop('checked',false);
    };

    this.setCurrentPreset=function(preset, is_default){
        if (is_default)
            self.presetDefaultCheckbox.prop('checked',true);
        else
            self.presetDefaultCheckbox.prop('checked',false);

        self.presetSelect.val(preset);
    };

    this.newPreset=function(){
        var name=prompt("New preset name");
        if (name!=null)
        {
            self.delegate.displayStatus('Saving preset ...');

            var data={};
            data['name']=name;
            if (self.presetDefaultCheckbox.is(':checked'))
                data['make_default']=1;

            self.delegate.postAjax('ilab_imgix_new_preset', data, function(response) {
                self.delegate.hideStatus();
                if (response.status=='ok')
                {
                    self.delegate.settings.currentPreset=response.currentPreset;
                    self.delegate.settings.presets=response.presets;

                    self.init();
                }
            });
        }
    };

    this.savePreset=function(){
        if (self.presetSelect.val()==null)
            return;

        self.delegate.displayStatus('Saving preset ...');

        var data={};
        data['key']=self.presetSelect.val();
        if (self.presetDefaultCheckbox.is(':checked'))
            data['make_default']=1;

        self.delegate.postAjax('ilab_imgix_save_preset', data, function(response) {
            self.delegate.hideStatus();
        });
    };

    this.deletePreset=function(){
        if (self.presetSelect.val()==null)
            return;

        if (!confirm("Are you sure you want to delete this preset?"))
            return;

        self.delegate.displayStatus('Delete preset ...');

        var data={};
        data['key']=self.presetSelect.val();

        self.delegate.postAjax('ilab_imgix_delete_preset', data, function(response) {
            self.delegate.hideStatus();
            if (response.status=='ok')
            {
                self.delegate.settings.currentPreset=response.currentPreset;
                self.delegate.settings.presets=response.presets;

                self.init();

                self.delegate.bindUI(response);
            }
        });
    };

    this.init();
};
(function($){

    $.fn.ilabSidebarTabs=function(options){
        var settings= $.extend({},options);

        var firstTab=false;
        return this.find('.ilabm-sidebar-tab').each(function(){
            var tab=$(this);
            var target=settings.container.find('.'+tab.data('target'));

            if (!firstTab)
            {
                tab.addClass('active-tab');
                target.removeClass('is-hidden');

                firstTab=true;
            }

            tab.on('click',function(e){
                e.preventDefault();

                settings.container.find(".ilabm-sidebar-tab").each(function() {
                    var otherTab = $(this);
                    var tabTarget = settings.container.find('.' + otherTab.data('target'));

                    otherTab.removeClass('active-tab');
                    tabTarget.addClass('is-hidden');
                });

                tab.addClass('active-tab');
                target.removeClass('is-hidden');

                return false;
            });
        });
    };

}(jQuery));

/**
 * Image Editing Module
 */

var ILabImageEdit=function($, settings){
    console.log(settings);

    this.previewTimeout=null;
    this.previewsSuspended=false;
    this.parameters=[];

    var self=this;

    this.settings=settings;

    this.modalContainer=$('#ilabm-container-'+settings.modal_id);
    this.waitModal=this.modalContainer.find('.ilabm-preview-wait-modal');
    this.previewImage=this.modalContainer.find('.imgix-preview-image');

    this.presets=new ILabImgixPresets($,this,this.modalContainer);

    this.modalContainer.find('.imgix-button-reset-all').on('click',function(){
        self.resetAll();
    });
    this.modalContainer.find('.imgix-button-save-adjustments').on('click',function(){
        self.apply();
    });

    this.modalContainer.find('.imgix-parameter').each(function(){
        var container=$(this);
        var type=container.data('param-type');
        if (type=='slider')
            self.parameters.push(new ImgixComponents.ImgixSlider(self,container));
        else if ((type=='color') || (type=='blend-color'))
            self.parameters.push(new ImgixComponents.ImgixColor(self,container));
        else if (type=='pillbox')
            self.parameters.push(new ImgixComponents.ImgixPillbox(self,container));
        else if (type=='media-chooser')
            self.parameters.push(new ImgixComponents.ImgixMediaChooser(self,container));
        else if (type=='alignment')
            self.parameters.push(new ImgixComponents.ImgixAlignment(self,container));
    });

    this.modalContainer.on('click','.imgix-pill',function(){
        var paramName=$(this).data('param');
        var param=self.modalContainer.find('#imgix-param-'+paramName);
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

        self.preview();
    });

    this.modalContainer.find('.ilabm-editor-tabs').ilabTabs({
        currentValue: self.settings.size,
        tabSelected:function(tab){
            ILabModal.loadURL(tab.data('url'),true,function(response){
                console.log(response);
                self.bindUI(response);
            });
        }
    });

    this.modalContainer.find(".ilabm-sidebar-tabs").ilabSidebarTabs({
        delegate: this,
        container: this.modalContainer
    });

    /**
     * Performs the wordpress ajax post
     * @param action
     * @param data
     * @param callback
     * @private
     */
    this.postAjax=function(action,data,callback){
        var postData={};
        self.parameters.forEach(function(value,index){
            postData=value.saveValue(postData);
        });

        console.log(postData);

        data['image_id'] = self.settings.image_id;
        data['action'] = action;
        data['size'] = self.settings.size;
        data['settings']=postData;

        $.post(ajaxurl, data, callback);
    }

    /**
     * Performs the actual request for a preview to be generated
     * @private
     */
    function _preview(){
        self.displayStatus('Building preview ...');

        self.waitModal.removeClass('is-hidden');

        self.postAjax('ilab_imgix_preview',{},function(response) {
            if (response.status=='ok')
            {
                if (self.settings.debug)
                    console.log(response.src);

                self.previewImage.on('load',function(){
                    self.waitModal.addClass('is-hidden');
                    self.hideStatus();
                });

                self.previewImage.attr('src',response.src);
            }
            else
            {
                self.waitModal.addClass('is-hidden');
                self.hideStatus();
            }
        });
    }

    /**
     * Requests a preview to be generated.
     */
    this.preview=function(){
        if (self.previewsSuspended)
            return;

        ILabModal.makeDirty();

        clearTimeout(self.previewTimeout);
        self.previewTimeout=setTimeout(_preview,500);
    };

    /**
     * Binds the UI to the json response when selecting a tab or changing a preset
     * @param data
     */
    this.bindUI=function(data){
        if (data.hasOwnProperty('currentPreset') && (data.currentPreset!=null) && (data.currentPreset!='')) {
            var p=self.settings.presets[data.currentPreset];
            self.presets.setCurrentPreset(data.currentPreset,(p.default_for==data.size));
        }
        else
            self.presets.clearSelected();

        self.previewsSuspended=true;
        self.settings.size=data.size;
        self.settings.settings=data.settings;

        var rebind=function(){
            self.previewImage.off('load',rebind);
            self.parameters.forEach(function(value,index){
                value.reset(data.settings);
            });

            self.previewsSuspended=false;
            ILabModal.makeClean();
        };

        if (data.src)
        {
            self.previewImage.on('load',rebind);
            self.previewImage.attr('src',data.src);
        }
        else
            rebind();
    };

    this.bindPreset=function(preset){
        console.log(preset);
        self.previewsSuspended=true;
        self.settings.settings=preset.settings;

        self.previewImage.off('load');
        self.parameters.forEach(function(value,index){
            value.reset(self.settings.settings);
        });

        self.previewsSuspended=false;
        self.preview();
    };


    this.apply=function(){
        self.displayStatus('Saving adjustments ...');

        self.postAjax('ilab_imgix_save', {}, function(response) {
            self.hideStatus();
            ILabModal.makeClean();
        });
    };

    /**
     * Reset all of the values
     */
    this.resetAll=function(){
        self.parameters.forEach(function(value,index){
            value.reset();
        });
    };

    this.displayStatus=function(message){
        self.modalContainer.find('.ilabm-status-label').text(message);
        self.modalContainer.find('.ilabm-status-container').removeClass('is-hidden');
    };

    this.hideStatus=function(){
        self.modalContainer.find('.ilabm-status-container').addClass('is-hidden');
    };
};


//# sourceMappingURL=ilab-media-tools.js.map