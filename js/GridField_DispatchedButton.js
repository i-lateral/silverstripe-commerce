(function($) {
	$.entwine('ss', function($) {

		$('.ss-gridfield .action.gridfield-button-dispatch').entwine({
		    UUID: null,
			onmatch: function() {
				this._super();
				this.setUUID(new Date().getTime());
			},
			onunmatch: function() {
				this._super();
			},
			onclick: function(e) {
			    var filterState='show'; //filterstate should equal current state.
				
		        if(this.hasClass('ss-gridfield-button-close') || !(this.closest('.ss-gridfield').hasClass('show-filter'))){
			        filterState='hidden';
		        }
			
			    if(!confirm(ss.i18n._t('COMMERCE.DispatchConfirmMessage', 'Are you sure you wisth to mark these items dispatched?'))) {
					e.preventDefault();
					return false;
				} else {
				    // get selected ID's
				    var ids = $.map(this.getGridField().find('.ss-gridfield-item.ui-selected'), function(el) {return $(el).data('id');});			        
			        if(!ids && !ids.length) ids = false;
		            		            
	                // Add id's to the ajax call
	                var data = [
	                    { name: 'IDS', value: ids},
	                    { name: this.attr('name'), value: this.val(), filter: filterState }
                    ];
                    
                    // Reload Grid Field
	                this.getGridField().reload({data: data});
		            
			        e.preventDefault();
				}
			}
		});
	});
}(jQuery));
