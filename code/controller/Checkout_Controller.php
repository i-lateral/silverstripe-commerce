<?php


class Checkout_Controller extends Page_Controller {
    public static $url_segment = "checkout";
    
    public static $allowed_actions = array(
        'CheckoutForm'
    );
    
    public function init() {
        parent::init();
        
        // If cart is empty, re-direct to homepage
        if(!Session::get('Cart')) 
            Director::redirect(BASE_URL);
        
        // Overwrite custom validation
        Validator::set_javascript_validation_handler('none');
        Requirements::javascript("http://ajax.microsoft.com/ajax/jquery.validate/1.8/jquery.validate.min.js");
        
        Requirements::customScript('
            (function($) {
                $(document).ready(function() {
                    jQuery.validator.messages.required = "";
                    
                    $("#CheckoutForm_CheckoutForm").validate({
                        invalidHandler: function(e, validator) {
                            var errors = validator.numberOfInvalids();
                            if (errors) {
                                $("p.message").html("' . _t('Commerce.ERRORMESSAGE',"Please complete all the required fields below") . '");
                                $("p.message").show();
                            } else {
                                $("p.message").hide();
                            }
                        },
                        submitHandler: function(form) {
                            $("p.message").hide();
                            form.submit();
                        },
                        rules: {
                            BillingFirstnames: "required",
                            BillingSurname: "required",
                            BillingAddress1: "required",
                            BillingCity: "required",
                            BillingPostCode: "required",
                            BillingCountry: "required",
                            BillingEmail: {
                                required: true,
                                email: true
                            }
                        }
                    });
                });
            })(jQuery);
        ');
        
    }
    
    public function index() {
        return array();
    }
    
    public function CheckoutForm() {
        return new CheckoutForm($this, 'CheckoutForm');
    }
    
    public function getMetaTitle() {
        return _t('Commerce.CHECKOUTMETA',"Your Details");
    }
    
    public function getClassName() {
        return "CheckoutController";
    }
}
