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
        $postage_map->unshift(0, _t('Commerce.PLEASESELECT','Please Select'));
        
        $postage_value = Session::get('PostageID');
        
        // Find all current payment methods
        $payment_methods = SiteConfig::current_site_config()->PaymentMethods()->map('ID','Label');
        
        // Find the default payment method
        $payment_value = SiteConfig::current_site_config()->PaymentMethods()->filter('Default',1)->first()->ID;
        
        $fields = new FieldList(
            // Postage
            HeaderField::create('PostageHeading', _t('Commerce.POSTAGE', 'Postage'), 2),
            DropdownField::create('Postage', _t('Commerce.CARTLOCATION', 'Please choose location to post to'), $postage_map, $postage_value),
            
            // Payment Gateways
            HeaderField::create('PaymentHeading', _t('Commerce.PAYMENT', 'Payments'), 2),
            OptionsetField::create('PaymentMethod', _t('Commerce.PAYMENTSELECTION', 'Please choose how you would like to pay'), $payment_methods, $payment_value)
        );
        
        $actions = new FieldList(
            FormAction::create('doEmpty', _t('Commerce.CARTEMPTY','Empty Cart'))->addExtraClass('commerce-button'),
            FormAction::create('doUpdate', _t('Commerce.CARTUPDATE','Update Cart'))->addExtraClass('commerce-button'),
            FormAction::create('doCheckout', _t('Commerce.CARTPROCEED','Proceed to Checkout'))->addExtraClass('commerce-button')
        );
        
        parent::__construct($controller, $name, $fields, $actions);
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
        
        return $this->controller->redirectBack();
    }
    
    /**
     * Get all items in the cart session and convert to a DataObjectSet, in
     * order to render properly in the templates.
     * 
     * @return DataObjectSet 
     */
    public function getCartItems() {
        $controller = Controller::curr();
        $return = "";
        
        foreach($this->cart->Items() as $item) {
            $vars = array(
                'Key'           => $item->Key,
                'ProductID'     => $item->ID,
                'Title'         => $item->Title,
                'Description'   => ($item->Description) ? $item->Description : false,
                'Customised'    => $item->Customised,
                'Weight'        => $item->Weight,
                'CurrencySymbol'=> SiteConfig::current_site_config()->Currency()->HTMLNotation,
                'Price'         => money_format('%i',$item->Price),
                'Image'         => ($item->ImageID) ? Image::get()->byID($item->ImageID) : false,
                'Quantity'      => $item->Quantity
            );
            
            $return .= $controller->renderwith(array('ShoppingCartItem'), $vars);
        }
        
        return $return;
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
