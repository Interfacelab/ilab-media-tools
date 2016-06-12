
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
