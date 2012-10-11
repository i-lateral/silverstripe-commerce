<?php


class Checkout_Controller extends Page_Controller {
    public static $url_segment = "checkout";
    
    public static $allowed_actions = array(
        'CheckoutForm'
    );
    
    public function init() {
        parent::init();
    }
    
    public function index() {
        return array(
            'ClassName' => "Checkout",
            'Title'     => _t('Commerce.CHECKOUTMETA',"Your Details"),
            'MetaTitle' => _t('Commerce.CHECKOUTMETA',"Your Details"),
        );
    }
    
    public function CheckoutForm() {
        return new CheckoutForm($this, 'CheckoutForm');
    }
}
