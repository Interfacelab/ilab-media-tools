function clientRectContainsPoint(cr, x, y) {
  return ((x >= cr.left) && (x <= cr.right) && (y>=cr.top) && (y<=cr.bottom));
}

(function($){
    var infoPanelSetup = function() {
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

            $(document).trigger('ilab-regeneration-started');

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
                $(document).trigger('ilab-regeneration-ended');
            });

            return false;
        });
    };

    infoPanelSetup();

    var canPopup = true;
    var canClose = true;

    var loader = $('<div class="ilab-loader-container"><div class="ilab-loader ilab-loader-dark"></div></div>');
    var popup = $('<div id="ilab-media-grid-info-popup" class="hidden" style="left:0px; top:0px;"></div>');
    var popupContent = $('<div class="ilab-media-grid-info-popup-content"></div>');
    var arrowLeft = $('<div class="ilab-media-popup-arrow-left"><div></div></div>');
    var arrowRight = $('<div class="ilab-media-popup-arrow-right"><div></div></div>');

    var activeArrow = null;
    var popupActive = false;

    popup.append(arrowLeft);
    popup.append(popupContent);
    popup.append(arrowRight);

    $('body').append(popup);

    $(document).on('mouseenter', 'img.ilab-s3-logo', function(e){
        if (!canPopup) {
            return;
        }

        var img = $(this);
        var imgEle = this;
        var postId = img.data('post-id');
        var type = img.data('mime-type');

        if (type != 'image') {
            popup.addClass('ilab-popup-document');
        } else {
            popup.removeClass('ilab-popup-document');
        }

        popupActive = false;

        $('li.attachment').each(function(){
            var li = $(this);
            if (li.data('id') == postId) {
                li.removeClass('info-unfocused');
                li.addClass('info-focused');
            } else {
                li.removeClass('info-focused');
                li.addClass('info-unfocused');
            }
        });

        popupContent.text('');
        popupContent.append(loader);

        var bounds = imgEle.getBoundingClientRect();
        var y = document.body.scrollTop + ((bounds.top + (bounds.height / 2)) - (popup.height()  / 2));
        y -= 28;

        var vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        var vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        var dh = (document.body.scrollTop + vh) - 40;//$(document).height() - 40;

        var arrowDelta = 0;
        if (y + popup.height() > dh) {
            arrowDelta = ((y + popup.height()) - dh);
            y = dh - popup.height();
        } else if (y < document.body.scrollTop) {
            arrowDelta = y - document.body.scrollTop;
            y = document.body.scrollTop;
        }
        else if (y  < 0) {
            arrowDelta = y;
            y = 0;
        }

        var left = bounds.left;
        if (left + popup.width() < (vw - 10)) {
            activeArrow = arrowLeft;
            arrowLeft.css({transform: "translateY("+arrowDelta+"px)"});

            popup.removeClass('popup-right');
            popup.addClass('popup-left');
            popup.css({
                left: bounds.left+'px',
                top: y+'px'
            });
        } else {
            activeArrow = arrowRight;
            popup.removeClass('popup-left');
            popup.addClass('popup-right');
            arrowRight.css({transform: "translateY("+arrowDelta+"px)"});
            popup.css({
                left: (bounds.right - popup.width())+'px',
                top: y+'px'
            });
        }

        popup.removeClass('hidden');

        setTimeout(function(){
            var data = {
                "action": "ilab_s3_get_media_info",
                "id": postId
            };

            $.post(ajaxurl, data, function(response, text){
                if (response.length == 0) {
                    return;
                }

                var contents = $(response);
                popupContent.text('');
                popupContent.append(contents);
                infoPanelSetup();

                setTimeout(function(){
                    popupActive = true;
                }, 500);
            }, 'html');
        }, 300);
    });

    $(document).on('mousemove', function(e){
        if (popupActive && canClose) {
            var ar = activeArrow.get(0).getBoundingClientRect();
            ar.top -= 5;
            ar.bottom += 5;
            var pr = popupContent.get(0).getBoundingClientRect();
            var px = e.pageX;
            var py = e.pageY - document.body.scrollTop;
            if (!clientRectContainsPoint(ar, px, py) && !clientRectContainsPoint(pr, px, py)) {
                canPopup = false;
                popupActive = false;

                popup.addClass('hidden');

                $('li.attachment').each(function(){
                    var li = $(this);
                    li.removeClass('info-focused');
                    li.removeClass('info-unfocused');
                });

                setTimeout(function(){
                    canPopup = true;
                }, 300);
            }
        }
    });

    $(document).on('ilab-regeneration-started', function() {
       canClose = false;
    });

    $(document).on('ilab-regeneration-ended', function() {
        canClose = true;
    });
})(jQuery);

