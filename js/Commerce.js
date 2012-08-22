(function($) {
    $(document).ready(function() {
        $('.siteselect ul')
            .hide()
            .closest('li')
            .addClass('siteselect-js')
            .hover(function() {
                $('.siteselect ul').fadeIn('fast');
            }, function() {
                $('.siteselect ul').fadeOut('fast');
            });
    });
})(jQuery)