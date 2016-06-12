/**
 * Created by jong on 8/1/15.
 */

var ILabModal=(function(){
    var _dirty=false;
    var _data={};

    var cancel=function(){
        jQuery('.ilabm-backdrop').remove();
    };

    var makeDirty=function(){
        _dirty=true;
    };

    var isDirty=function(){
        return _dirty;
    };

    var makeClean=function(){
        _dirty=false;
    };

    var loadURL=function(url,partial,partialCallback){
        if (_dirty)
        {
            if (!confirm('You\'ve made changes, continuing will lose them.\n\nContinue?'))
                return false;
        }

        _dirty=false;

        jQuery.get(url, function(data) {
            if (partial) {
                partialCallback(data);
                //jQuery('#ilabm-container').remove();
                //jQuery('body').append(data);
                //jQuery('#ilabm-window-area').unbind().html('').append(data);
            } else {
                jQuery('body').append(data);
            }
        });
    };

    return {
        cancel: cancel,
        makeDirty:makeDirty,
        isDirty:isDirty,
        makeClean:makeClean,
        loadURL:loadURL
    };
})();

jQuery(document).ready(function($){
    $(document).on('click', 'a.ilab-thickbox', function(e) {
        e.preventDefault();
        var currEl = $(this);
        var partial=currEl.hasClass('ilab-thickbox-partial');

        ILabModal.loadURL(currEl.attr('href'),partial,null);

        return false;
    });
});