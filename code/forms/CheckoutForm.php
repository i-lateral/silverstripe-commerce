<?php
/**
 * Description of CheckoutForm
 *
 * @author morven
 */
class CheckoutForm extends Form {
    public function __construct($controller, $name) {
        $fields= new FieldSet(
            new FieldGroup(
                new HeaderField('BillingHeader', _t('Commerce.BILLINGDETAILS','Billing Details'), 2),
                new TextField('BillingFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)') . '*'),
                new TextField('BillingSurname',_t('Commerce.SURNAME','Surname') . '*'),
                new EmailField('BillingEmail',_t('Commerce.EMAIL','Email') . '*'),
                new TextField('BillingPhone',_t('Commerce.PHONE','Phone Number')),
                new TextField('BillingAddress1',_t('Commerce.ADDRESS1','Address Line 1') . '*'),
                new TextField('BillingAddress2',_t('Commerce.ADDRESS2','Address Line 2')),
                new TextField('BillingCity',_t('Commerce.CITY','City') . '*'),
                new TextField('BillingPostCode',_t('Commerce.POSTCODE','Post Code') . '*'),
                new CountryDropdownField('BillingCountry',_t('Commerce.COUNTRY','Country') . '*',null,'GB')
            ),
            new FieldGroup(
                new HeaderField('DeliveryHeader', _t('Commerce.DELIVERYDETAILS','Delivery Details') . '(' . _t('Commerce.IFDIFFERENT','if different') . ')', 2),
                new TextField('DeliveryFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)')),
                new TextField('DeliverySurname',_t('Commerce.SURNAME','Surname')),
                new TextField('DeliveryAddress1',_t('Commerce.ADDRESS1','Address Line 1')),
                new TextField('DeliveryAddress2',_t('Commerce.ADDRESS2','Address Line 2')),
                new TextField('DeliveryCity',_t('Commerce.CITY','City')),
                new TextField('DeliveryPostCode',_t('Commerce.POSTCODE','Post Code')),
                new CountryDropdownField('DeliveryCountry',_t('Commerce.COUNTRY','Country'),null,'GB')
            )
        );
        
        $actions = new FieldSet(
            new FormAction('doPost', _t('Commerce.PAYMENTDETAILS','Enter Payment Details'))
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
        
        Director::redirect(BASE_URL . '/summary/');
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
        $site_config = SiteConfig::current_site_config();
        $site = Subsite::currentSubsite();
        $order_prefix = ($site->OrderPrefix) ? $site->OrderPrefix . '-' : '';
        
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
            
        // Loop through each session cart item and add that item to the order
        foreach(Session::get('Cart') as $cart_item) {
            $order_item = new OrderItem();
            $order_item->Type       = $cart_item['Type'];
            $order_item->Quantity   = $cart_item['Quantity'];
            $order_item->Price      = $cart_item['Price'];
            $order_item->Colour     = $cart_item['Colour'];

            // If tags data exists, add to item
            $order_item->TagOne = ($cart_item['TagOne']) ? $cart_item['TagOne'] : '';
            $order_item->TagTwo = ($cart_item['TagTwo']) ? $cart_item['TagTwo'] : '';

            $order->Items()->add($order_item);
        }
        
        return $order;
    }
}