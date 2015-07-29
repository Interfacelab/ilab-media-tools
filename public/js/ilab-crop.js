/**
 * Created by jong on 7/29/15.
 */


jQuery(document).ready(function($){
    $(document).on('click', 'a.ilab-thickbox', function(e) {
        e.preventDefault();
        var currEl = $(this);
        var partial=currEl.hasClass('ilab-thickbox-partial');
        jQuery.get(currEl.attr('href'), function(data) {
            if (partial) {
                jQuery('#ilab-crop-wrapper .media-modal-content').empty().append(data);
            } else {
                jQuery('body').append(data);
            }
        });

        return false;
    });
});