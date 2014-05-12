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
            
		// Change price dynamicaly if needed
		$('.commerce-form-additem input, .commerce-form-additem select').change(function(){
			// If original price has not been created, create it.
			price = parseFloat($('.commerce-product-summary .price .value').text());
			modify_price = 0;
			quantity = $('.commerce-form-additem input[name=Quantity]').val();
			
			if(!$('.commerce-product-summary .original-price').length > 0) {
				original_price_element = $('<p class="original-price">' + price + '</p>').hide();
				$('.commerce-product-summary').append(original_price_element); 
			}
			
			original_price = parseFloat($('.commerce-product-summary .original-price').text());
			
			// First check all checked inputs that can modify
			$('.commerce-form-additem input:checked, .commerce-form-additem option:selected').each(function() {
				price_string = String($('label[for=' + $(this).attr('id') + ']').text().match(/\(([^\)]+)\)/g));
				// Strip out the currency symbol
				price = price_string.substring(2, price_string.length - 1);

				if(!isNaN(price) && price.length > 0)
					modify_price += parseFloat(price);
			});
			
			// Now check all dropdowns
			$('.commerce-form-additem select').each(function() {
				price_string = String($(this).find('option:selected').text().match(/\(([^\)]+)\)/g));
				// Strip out the currency symbol
				price = price_string.substring(2, price_string.length - 1);

				if(!isNaN(price) && price.length > 0)
					modify_price += parseFloat(price);
			});
			
			$('.commerce-product-summary .price .value').text(parseFloat(parseFloat(original_price + modify_price) * parseFloat(quantity)).toFixed(2));
		});
    });
})(jQuery)
