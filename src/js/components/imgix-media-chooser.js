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
