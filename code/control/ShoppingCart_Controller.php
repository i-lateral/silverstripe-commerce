<?php
/**
 * Description of ShoppingCart_Controller
 *
 * @author morven
 */
class ShoppingCart_Controller extends Page_Controller {
    public static $url_segment = 'cart';
    
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
		
        return $this->renderWith(array('ShoppingCart','Page'), $vars);
    }
    
    public function Link($action = null) {
        return Controller::join_links(Director::baseURL(), self::$url_slug);
    }
    
    /**
     * Remove a product from ShoppingCart Via its ID.
     *
     * @param ID product ID
     */
    public function remove() {
        $key = $this->request->param('ID');
        
        if(!empty($key)) {
            $cart = ShoppingCart::get();
            $cart->remove($key);
            $cart->save();
        }
    
        return $this->redirectBack();
    }
    
    public function CartForm() {
        return CartForm::create($this, 'CartForm')
			->addExtraClass('forms')
			->addExtraClass('commerce-cart-form');
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
