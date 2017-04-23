(function($){

    $.fn.ilabTabs=function(options){
        var settings= $.extend({},options);
        var sizeCanvas=null;

        return this.each(function(){
            var container=$(this);
            var windowContainer = container;

            var parentContainer = container.parent();
            while(true) {
                if (parentContainer.hasClass('ilabm-container')) {
                    windowContainer = parentContainer;
                    break;
                }

                parentContainer = parentContainer.parent();
                if (!parentContainer) {
                    break;
                }
            }

            var label=container.find('.ilabm-tabs-select-label');
            var select=container.find('.ilabm-tabs-select');
            var tabs=container.find('.ilabm-editor-tab');

            var minWidth=0;
            var tabFont=null,tabMarginLeft=0,tabMarginRight=0,tabPaddingLeft=0,tabPaddingRight=0;

            var getTextWidth=function(text, font) {
                // re-use canvas object for better performance

                var canvas = sizeCanvas || (sizeCanvas = document.createElement("canvas"));
                var context = canvas.getContext("2d");
                context.font = font;
                var metrics = context.measureText(text);
                return metrics.width;
            };

            tabs.each(function(){
                var tab=$(this);
                if (tabFont===null) {
                    tabFont = tab.css('font');
                    tabMarginLeft=parseInt(tab.css('margin-left'));
                    tabMarginRight=parseInt(tab.css('margin-right'));
                    tabPaddingLeft=parseInt(tab.css('padding-left'));
                    tabPaddingRight=parseInt(tab.css('padding-right'));
                }

                tabWidth=getTextWidth(tab.text(),tabFont)+tabMarginLeft+tabMarginRight+tabPaddingLeft+tabPaddingRight+15;
                minWidth+=tabWidth;
            });

            if (label && settings.hasOwnProperty('label'))
                label.text(settings.label);

            tabs.removeClass('active-tab');
            tabs.on('click',function(e){
                e.preventDefault();

                tabs.removeClass('active-tab');
                var tab=$(this);
                tab.addClass('active-tab');

                if (select)
                    select.val(tab.data('value'));

                settings.currentValue=tab.data('value');

                if (settings.hasOwnProperty('tabSelected'))
                    settings.tabSelected(tab);

                return false;
            });

            if (select)
            {
                select.on('change',function(){
                    tabs.removeClass('active-tab');
                    tabs.each(function(){
                        var tab=$(this);
                        if (tab.data('value')==select.val())
                            tab.addClass('active-tab');
                    });
                    var option=select.find(":selected");
                    if (settings.hasOwnProperty('tabSelected'))
                        settings.tabSelected(option);
                });
            }

            if (settings.hasOwnProperty('currentValue'))
            {
                if (select)
                    select.val(settings.currentValue);

                tabs.each(function(){
                   var tab=$(this);
                    if (tab.data('value')==settings.currentValue)
                        tab.addClass('active-tab');
                });
            }

            var checkOverflow=function(){
                if (minWidth > (windowContainer.width() - 350)) {
                    label.show();
                    select.show();
                    tabs.hide();
                }
                else {
                    label.hide();
                    select.hide();
                    tabs.show();
                }
            };

            $(window).on('resize',checkOverflow);
            checkOverflow();
        });
    };

}(jQuery));
