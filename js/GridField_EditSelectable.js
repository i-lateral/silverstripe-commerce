(function($) {
	$.entwine('ss', function($) {	
	    $('.ss-gridfield .changed').entwine({
			onclick: function(e) {
			    if($(this).is(':checked')) {
			        $('.ss-gridfield[data-selectable] .ss-gridfield-items').selectable('destroy');
			    } else {
			        $('.ss-gridfield[data-selectable] .ss-gridfield-items').selectable();
			    }
			}
		});
	});
}(jQuery));
