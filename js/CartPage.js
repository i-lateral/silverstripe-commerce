(function($) {
    $(document).ready(function() {
        // Deal with stopping the form from submitting by accident
        $('form#CartForm_CartForm').submit(function() {
            if(!$('#CartForm_CartForm_Postage').val()) {
                alert(ss.i18n._t('COMMERCE.CART.NOPOSTAGE'));
                return false;
            }
        });
        
        // Auto submit form when dropdown is clicked
        $('#CartForm_CartForm_Postage').change(function () { $('#CartForm_CartForm_action_doUpdate').click(); } );
    });
})(jQuery)