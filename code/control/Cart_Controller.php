<?php
/**
 * Description of Cart_Controller
 *
 * @author morven
 */
class Cart_Controller extends Page_Controller {
    public static $url_segment = 'cart/$Action/$ID';
    
    public static $url_slug = 'cart';
    
    public static $allowed_actions = array(
        "CartForm"
    );
    
    public function init() {
        parent::init();
        
        // Add Javascript
        Requirements::javascript("commerce/js/CartPage.js");
    }
    
    public function index() {
    	$vars = array(
			'Title' => $this->getTitle(),
			'Content' => Subsite::currentSubsite()->CartCopy
		);
		
        return $this->renderWith(array('Cart','Page'), $vars);
    }
    
    public function CartForm() {
        return new CartForm($this, 'CartForm');
    }
    
    /**
     * Method to get the total number of items in a shopping cart
     * 
     * @return int total items in cart
     */
    public function TotalItems() {
        $cart = Session::get('Cart');
        $total = 0;
        
        if($cart) {
            foreach($cart as $item) {
                $total += $item['Quantity'];
            }
        }
        
        return $total;
    }
    
	public function getTitle() {
        return _t('Commerce.CARTNAME', 'Shopping Cart');
	}
	
    public function getMetaTitle() {
        return _t('Commerce.CARTNAME', 'Shopping Cart');
    }
    
    public function getClassName() {
        return "CartController";
    }
}
