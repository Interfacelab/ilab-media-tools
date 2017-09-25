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

    $('.ilab-info-regenerate-thumbnails').on('click', function(e){
        var button = $(this);

        if (button.data('imgix-enabled')) {
            if (!confirm('You are currently using Imgix, which makes this function rather unnecessary.  Are you sure you want to continue?')) {
                return false;
            }
        }

        e.preventDefault();

        button.css({display: 'none'});
        $('#ilab-info-regenerate-status').css({display: ''});

        $.post(ajaxurl, {
            "action": "ilab_media_cloud_regenerate_file",
            "post_id": button.data('post-id')
        }, function(response){
            button.css({display: ''});
            $('#ilab-info-regenerate-status').css({display: 'none'});
            if (response.status != 'success') {
                alert(response.message);
            }
        });

        return false;
    });
})(jQuery);

