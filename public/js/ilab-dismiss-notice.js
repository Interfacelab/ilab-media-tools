(function ($) {
    //shorthand for ready event.
    $(function () {
        $('div[data-dismissible] button.notice-dismiss').click(function (event) {
            event.preventDefault();
            var $this = $(this);

            var attr_value, option_name, dismissible_length, data;

            option_name = $this.parent().attr('data-dismissible');

            // remove the dismissible length from the attribute value and rejoin the array.
            dismissible_length = $this.parent().attr('data-dismissible-length');

            data = {
                'action': 'ilab_dismiss_admin_notice',
                'option_name': option_name,
                'dismissible_length': dismissible_length,
                'nonce': ilab_dismissible_notice.nonce
            };

            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            $.post(ajaxurl, data);
        });
    })

}(jQuery));
