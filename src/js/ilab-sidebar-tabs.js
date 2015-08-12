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
