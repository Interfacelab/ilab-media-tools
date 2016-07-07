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

        console.log(data);

        if ((data !== undefined) && data.hasOwnProperty(this.blendParam))
        {
            blend=data[this.blendParam];
        }

        if ((data !== undefined) && data.hasOwnProperty(this.param))
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
        } else {
            this.alphaSlider.val(0);
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
