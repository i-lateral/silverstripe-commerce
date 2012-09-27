<?php
/**
 * Description of Cart_Controller
 *
 * @author morven
 */
class Cart_Controller extends Page_Controller {
    public static $url_segment = 'cart/$Action/$ID/$Quantity';
    
    public static $url_slug = 'cart';
    
    public static $allowed_actions = array(
        'add',
        'remove',
        'empty',
        'clear',
        'update',
        "CartForm"
    );
    
    public function init() {
        parent::init();
        
        // Add Javascript
        Requirements::javascript("commerce/js/CartPage.js");
    }
    
    public function index() {
        $cart_copy = (SiteConfig::current_site_config()->CartCopy) ? SiteConfig::current_site_config()->CartCopy : '';
    
    	$vars = array(
			'Title' => $this->getTitle(),
			'Content' => $cart_copy
		);
		
        return $this->renderWith(array('Cart','Page'), $vars);
    }
    
    public function Link($action = null) {
        return Controller::join_links(Director::baseURL(), self::$url_slug);
    }
    
    /**
     * Remove a product from ShoppingCart Via its ID.
     *
     * @param ID product ID
     */
    public function remove($url_params) {
        $all_params = $url_params->allParams();
        
        if(!empty($all_params['ID'])) {
            $product = Product::get()->byID($all_params['ID']);
            $cart = ShoppingCart::get();
            $cart->remove($product);
            $cart->save();
        }
    
        return $this->redirectBack();
    }
    
    public function CartForm() {
        return new CartForm($this, 'CartForm');
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
