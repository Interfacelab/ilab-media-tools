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

