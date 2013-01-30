<?php
/**
 * Description of CheckoutForm
 *
 * @author morven
 */
class CheckoutForm extends Form {
    public function __construct($controller, $name) {
        // If cart is empty, re-direct to homepage
        if(!ShoppingCart::get()->Items()) 
            $this->redirect(BASE_URL);
        
        // Overwrite custom validation
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
    
        $billing_fields = FieldGroup::create(
                HeaderField::create('BillingHeader', _t('Commerce.BILLINGDETAILS','Billing Details'), 2),
                TextField::create('BillingFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)') . '*'),
                TextField::create('BillingSurname',_t('Commerce.SURNAME','Surname') . '*'),
                EmailField::create('BillingEmail',_t('Commerce.EMAIL','Email') . '*'),
                TextField::create('BillingPhone',_t('Commerce.PHONE','Phone Number')),
                TextField::create('BillingAddress1',_t('Commerce.ADDRESS1','Address Line 1') . '*'),
                TextField::create('BillingAddress2',_t('Commerce.ADDRESS2','Address Line 2')),
                TextField::create('BillingCity',_t('Commerce.CITY','City') . '*'),
                TextField::create('BillingPostCode',_t('Commerce.POSTCODE','Post Code') . '*'),
                CountryDropdownField::create('BillingCountry',_t('Commerce.COUNTRY','Country') . '*',null,'GB')->addExtraClass('btn')
            )->addExtraClass('billing_fields');
            
        $delivery_fields = FieldGroup::create(
                HeaderField::create('DeliveryHeader', _t('Commerce.DELIVERYDETAILS','Delivery Details') . '(' . _t('Commerce.IFDIFFERENT','if different') . ')', 2),
                TextField::create('DeliveryFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)')),
                TextField::create('DeliverySurname',_t('Commerce.SURNAME','Surname')),
                TextField::create('DeliveryAddress1',_t('Commerce.ADDRESS1','Address Line 1')),
                TextField::create('DeliveryAddress2',_t('Commerce.ADDRESS2','Address Line 2')),
                TextField::create('DeliveryCity',_t('Commerce.CITY','City')),
                TextField::create('DeliveryPostCode',_t('Commerce.POSTCODE','Post Code')),
                CountryDropdownField::create('DeliveryCountry',_t('Commerce.COUNTRY','Country'),null,'GB')->addExtraClass('btn')
            )
            ->addExtraClass('delivery_fields');
            
        $fields= FieldList::create(
            $billing_fields,
            $delivery_fields
        );
        
        $actions = FieldList::create(
            LiteralField::create('BackButton','<a href="' . BASE_URL . '/' . ShoppingCart_Controller::$url_segment . '" class="btn commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'),
            FormAction::create('doPost', _t('Commerce.PAYMENTDETAILS','Enter Payment Details'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next')
                ->addExtraClass('highlight')
        );
        
        $validator = new RequiredFields(
            'BillingFirstnames',
            'BillingSurname',
            'BillingAddress1',
            'BillingCity',
            'BillingPostCode',
            'BillingCountry',
            'BillingEmail'
        );
        
        parent::__construct($controller, $name, $fields, $actions, $validator);
    }
    
    public function doPost($data, $form) {
        $order = $this->save_data_to_order($form);
        
        Session::set('Order',$order);
        
        $this->controller->redirect(BASE_URL . '/' . Payment_Controller::$url_segment);
    }
    
    /**
     * Method that is responsible for saving subbmited checkout data into an
     * order object
     * 
     * @param type $form Form submitted
     * @return Order Object
     */
    private function save_data_to_order($form) {
        // Work out if an order prefix string has been set in siteconfig
        $config = SiteConfig::current_site_config();
        $order_prefix = ($config->OrderPrefix) ? $config->OrderPrefix . '-' : '';
        
        // Check if delivery details are set. If not, set to billing details.
        $formData = $form->getData();
        
		$delivery_address = '';
        
        foreach($formData as $key => $value) {
        	if($key == 'DeliveryCountry' && !$delivery_address)
				$formData[$key] = $formData[str_replace('Delivery', 'Billing', $key)];
			
            if(strstr($key, 'Delivery')) {
            	if(!$value)
            		$formData[$key] = $formData[str_replace('Delivery', 'Billing', $key)];
				else
					$delivery_address .= $value;
            }
        }
        
        $form->loadDataFrom($formData);
        
        // Save form data into an order object
        $order = new Order();
        $form->saveInto($order);
        $order->OrderNumber = $order_prefix . uniqid();
        $order->Status      = 'incomplete';
        $order->PostageID   = Session::get('PostageID');
        $order->write();
            
        // Loop through each session cart item and add that item to the order
        foreach(ShoppingCart::get()->Items() as $cart_item) {
            $order_item = new OrderItem();
            $order_item->Title          = $cart_item->Title;
            $order_item->Price          = $cart_item->Price;
            $order_item->Customisation  = serialize($cart_item->Customised);
            $order_item->Quantity       = $cart_item->Quantity;
            $order_item->write();

            $order->Items()->add($order_item);
        }
        
        return $order;
    }
}
