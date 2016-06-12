
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
