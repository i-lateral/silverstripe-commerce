(function($) { 
    $(document).ready(function() { 
     
        $('#right input:submit').unbind('click').live('click', function(){
            var form = $('#right form');
            var formAction = form.attr('action') + '?' + $(this).fieldSerialize();
            if(typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
             
            $.ajax({
                url : formAction,
                data : form.formToArray(),
                dataType : "json",
                success : function(json) {
                    tinymce_removeAll();
         
                    $('#right #ModelAdminPanel').html(json.html);
                    if($('#right #ModelAdminPanel form').hasClass('validationerror')) {
                        statusMessage(ss.i18n._t('ModelAdmin.VALIDATIONERROR', 'Validation Error'), 'bad');
                    } else {
                        statusMessage(json.message, 'good');
                    }
         
                    Behaviour.apply();
                    if(window.onresize) window.onresize();
                }
            });
            return false;
        });
             
    })
})(jQuery);