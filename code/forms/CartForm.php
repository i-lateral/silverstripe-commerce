<?php
/**
 * Description of CartForm
 *
 * @author morven
 */
class CartForm extends Form {
    public function __construct($controller, $name) {
        $postage_map = (Subsite::currentSubsite()->PostageAreas()) ? Subsite::currentSubsite()->PostageAreas()->map('ID','Location',_t('Commerce.PLEASESELECT','Please Select')) : '';
        $postage_value = Session::get('PostageID');
        
        $fields = new FieldSet(
            new DropdownField('Postage', _t('Commerce.CARTLOCATION', 'Please choose location to post to'), $postage_map, $postage_value)
        );
        $actions = new FieldSet(
            new FormAction('doEmpty', 'Empty Cart'),
            new FormAction('doUpdate', 'Update Cart'),
            new FormAction('doCheckout', 'Proceed to Checkout')
        );
        
        parent::__construct($controller, $name, $fields, $actions);
    }
    
    public function forTemplate() {
        return $this->renderWith(array(
            $this->class,
            'Form'
        ));
    }
    
    /**
     * Get the currency for the current sub site
     * 
     * @return string 
     */
    public function getCurrencySymbol() {
        return (Subsite::currentSubsite()) ? Subsite::currentSubsite()->Currency()->HTMLNotation : false;
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
        // Fist update cart contents
        $old_cart = Session::get('Cart');
        $new_cart = array();
        unset($_SESSION['Cart']);
        
        foreach($old_cart as $cart_item) {
            foreach($data as $key => $value) {
                $sliced_key = explode("_", $key);
                if($sliced_key[0] == "Quantity") {
                    if(isset($cart_item) && ($cart_item['ID'] == $sliced_key[1])) {
                        if($value > 0) {
                            $cart_item['Quantity'] = $value;
                            $cart_item['Total'] = $cart_item['Price'] * $value;
                        } else
                            unset($cart_item);
                    }
                }
            }
            
            if(isset($cart_item))
                $new_cart[] = $cart_item;
        }
        
        Session::set("Cart",$new_cart);
        
        // If set, update Postage
        if($data['Postage'])
            Session::set('PostageID', $data['Postage']);
        
        Director::redirectBack();
    }
    
    public function doCheckout($data, $form) {
        Director::redirect(BASE_URL . '/' . Checkout_Controller::$url_segment);
    }
    
    public function doEmpty() {
        unset($_SESSION['Cart']);
        Director::redirectBack();
    }
    
    /**
     * Get all items in the cart session and convert to a DataObjectSet, in
     * order to render properly in the templates.
     * 
     * @return DataObjectSet 
     */
    public function getCart() { 
        $cart = Session::get('Cart');
        $items = new DataObjectSet();
        
        if(is_array($cart)) {
            foreach($cart as $item) {
                if($silencer = DataObject::get_one('TagColour', "Title = '{$item['Colour']}'" ))
                    $item['Silencer'] = $silencer;
                
                $items->push(new ArrayData($item));
            }

            return $items;
        } else
            return false;
    }
    
    /**
     * Generate a total cost from all the items in the cart session.
     * 
     * @return Int 
     */
    public function getCartTotal() {
        $cart = Session::get('Cart');
        $total = 0;
        
        foreach($cart as $item) {
            $total += $item['Total'];
        }
        
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            $total += DataObject::get_by_id('PostageArea', Session::get('PostageID'))->Cost;
        
        return money_format('%i',$total);
        
    }
    
    public function getPostageCost() {
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            return money_format('%i',DataObject::get_by_id('PostageArea', Session::get('PostageID'))->Cost);
        else
            return false;
    }
}