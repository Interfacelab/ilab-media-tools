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