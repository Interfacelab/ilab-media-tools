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
