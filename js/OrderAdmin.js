(function($) { 
    $(document).ready(function() {
        $('#Order_utility_tagprinter').live('click', function() {
            $(this).attr('href',$(this).attr('href') + '&records=' + getCheckboxes('table.data input.checkbox'));
        });
        
        $('#Order_utility_dispatched').live('click', function(e) {
            url = $(this).attr('href') + '&records=' + getCheckboxes('table.data input.checkbox');
            
            $.ajax({
                url : url,
                success : function() {
                    tinymce_removeAll();
                    if($('#right #ModelAdminPanel form').hasClass('validationerror')) {
                        statusMessage(ss.i18n._t('ModelAdmin.VALIDATIONERROR', 'Validation Error'), 'bad');
                    } else {
                        statusMessage('Delivered', 'good');
                    }

                    Behaviour.apply();
                    if(window.onresize) window.onresize();
                    
                    $("#LeftPane #Form_Order form").submit();
                    
                }
            });
            
            return false;
            
        });
    });
    
    function getCheckboxes(selector) {
        items = '';
        
        if(selector) {
            $(selector).each(function() {
                if($(this).is(':checked'))
                    items += $(this).val() + ',';
            });
            
            return items;
            
        } else
            return "";
    }
})(jQuery);