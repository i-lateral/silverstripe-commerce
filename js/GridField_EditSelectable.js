(function($) {
	$.entwine('ss', function($) {
		$('.ss-gridfield[data-selectable] .ss-gridfield-items').entwine({
			onclick: function(e) {
			    this._super(e);
			}
		});
	});
}(jQuery));
