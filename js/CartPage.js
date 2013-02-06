(function($) {
    $(document).ready(function() {        
        // Auto submit form when dropdown is clicked
        $('#CartForm_CartForm_Postage').change(function () { $('#CartForm_CartForm_action_doUpdate').click(); } );
    });
})(jQuery)
