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
