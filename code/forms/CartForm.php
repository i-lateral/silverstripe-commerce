<?php
/**
 * Description of CartForm
 *
 * @author morven
 */
class CartForm extends Form {
    protected $cart;

    public function __construct($controller, $name) {
        // Set shopping cart
        $this->cart = ShoppingCart::get();
    
        $postage_map = (SiteConfig::current_site_config()->PostageAreas()) ? SiteConfig::current_site_config()->PostageAreas()->map('ID','Location') : '';
        $postage_value = Session::get('PostageID');
        
        // Find all current payment methods
        $payment_methods = SiteConfig::current_site_config()->PaymentMethods()->map('ID','Label');
        
        // Find the default payment method
        $payment_value = SiteConfig::current_site_config()->PaymentMethods()->filter('Default',1)->first()->ID;
        
        $fields = new FieldList(
            // Postage
            HeaderField::create('PostageHeading', _t('Commerce.POSTAGE', 'Postage'), 2),
            DropdownField::create('Postage', _t('Commerce.CARTLOCATION', 'Please choose location to post to'), $postage_map)
				->addExtraClass('btn'),
            
            // Payment Gateways
            HeaderField::create('PaymentHeading', _t('Commerce.PAYMENT', 'Payment'), 2),
            OptionsetField::create('PaymentMethod', _t('Commerce.PAYMENTSELECTION', 'Please choose how you would like to pay'), $payment_methods, $payment_value)
        );
        
        $actions = new FieldList(
            FormAction::create('doEmpty', _t('Commerce.CARTEMPTY','Empty Cart'))->addExtraClass('btn'),
            FormAction::create('doUpdate', _t('Commerce.CARTUPDATE','Update Cart'))->addExtraClass('btn'),
            FormAction::create('doCheckout', _t('Commerce.CARTPROCEED','Proceed to Checkout'))->addExtraClass('btn')->addExtraClass('highlight')
        );
        
        $validator = new RequiredFields(
            'Postage',
            'PaymentMethod'
        );
        
        parent::__construct($controller, $name, $fields, $actions, $validator);
        
        // Fix to get corect postage location to load from session
        $fields->dataFieldByName('Postage')
			->setValue($postage_value)
			->setEmptyString(_t('Commerce.PLEASESELECT','Please Select'));
			
		// If postage is in session, overwrite default error message
		if($postage_value) $fields->dataFieldByName('Postage')->setError(null,null);
    }
    
    public function forTemplate() {
        return $this->renderWith(array(
            $this->class,
            'Form'
        ));
    }
    
    public function getCart() {
        return $this->cart;
    }
    
    /**
     * Get the currency for the current sub site
     * 
     * @return string 
     */
    public function getCurrencySymbol() {
        return (SiteConfig::current_site_config()->Currency()) ? SiteConfig::current_site_config()->Currency()->HTMLNotation : false;
    }
    
    public function Link($action = null) {
        return Controller::join_links(Director::baseURL(), ShoppingCart_Controller::$url_slug);
    }
    
    /**
     * Action that will check each item in the existing cart, and update the
     * quantity if required.
     * 
     * If the quantity is set to 0, then the item is removed from the cart.
     * 
     * @param type $data
     * @param type $form 
     */
    public function doUpdate($data, $form) {
        foreach($this->cart->Items() as $cart_item) {
            foreach($data as $key => $value) {
                $sliced_key = explode("_", $key);
                if($sliced_key[0] == "Quantity") {
                    if(isset($cart_item) && ($cart_item->Key == $sliced_key[1])) {
                        if($value > 0) {
                            $this->cart->update($cart_item->Key,$value);
                        } else
                            $this->cart->remove($cart_item->Key);
                    }
                }
            }
        }
        
        $this->cart->save();
        
        // If set, update Postage
        if($data['Postage'])
            Session::set('PostageID', $data['Postage']);
        
        $this->controller->redirectBack();
    }
    
    public function doCheckout($data, $form) {
        Session::set('PaymentMethod', $data['PaymentMethod']);
    
        $this->controller->redirect(BASE_URL . '/' . Checkout_Controller::$url_segment);
    }
    
    public function doEmpty() {
        $this->cart->clear();
        
		Session::clear('PostageID');
        unset($_SESSION['PostageID']);
        
        return $this->controller->redirectBack();
    }
    
    public function getItems() {
		$items = new ArrayList();
		
		foreach($this->cart->Items() as $item) {
			// Create a list for customisations, with some casting added
			$customised_list = new ArrayList();
			
			foreach($item->Customised as $customised) {
				$customised_list->add(new ArrayData(array(
					'Title' => DBField::create_field('Varchar', $customised->Title),
					'Value' => nl2br(Convert::raw2xml($customised->Value), true),
					'ClassName' => Convert::raw2url($customised->Title)
				)));
			}
			
			$items->add(new ArrayData(array(
				'Key' => $item->Key,
				'Title' => DBField::create_field('Varchar', $item->Title),
				'Description' => nl2br(Convert::raw2xml($item->Description), true),
				'Customised' => $customised_list,
				'Price' => DBField::create_field('Decimal', $item->Price),
				'Quantity' => DBField::create_field('Int', $item->Quantity),
				'Image' => Image::get()->byID($item->ImageID),
			)));
		}
		
        return $items;
    }
    
    /**
     * Generate a total cost from all the items in the cart session.
     * 
     * @return Int
     */
    public function getCartTotal() {
        $total = $this->cart->TotalPrice();
        
        
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            $total += PostageArea::get()->byID(Session::get('PostageID'))->Cost;
        
        return money_format('%i',$total);
        
    }
    
    public function getPostageCost() {
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            return money_format('%i',DataObject::get_by_id('PostageArea', Session::get('PostageID'))->Cost);
        else
            return false;
    }
}
