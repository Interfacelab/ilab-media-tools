(function($){
    $('.info-panel-tabs').each(function(){
       var tabs = $(this);
       var activeTab = null;
       var activeTarget = null;

       tabs.find('li').each(function(){
            var tab = $(this);
            var target = $('#'+tab.data('tab-target'));

           if (tab.hasClass('active')) {
               activeTab = tab;
               activeTarget = target;
           }

            tab.on('click', function(e){
                if (activeTab == tab) {
                    return;
                }

                activeTab.removeClass('active');
                activeTarget.css({display: 'none'});

                tab.addClass('active');
                target.css({display: ''});

                activeTab = tab;
                activeTarget = target;
            });
       });
    });
})(jQuery);

(function($){
    var lastPanel = null;
    $('.info-file-info-size').each(function(){
        if (!lastPanel) {
            lastPanel = $(this);
        }
    });

    $('#ilab-other-sizes').on('change', function(e){
        var panel = $('#info-size-'+$(this).val());
        if (panel != lastPanel) {
            lastPanel.css({display:'none'});
            panel.css({display:''});
            lastPanel = panel;
        }
    });
})(jQuery);