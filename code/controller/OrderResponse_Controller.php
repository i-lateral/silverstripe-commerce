<?php
/**
 * OrderResponse_Controller is responsible for dealing with the response from
 * the payment gateway.
 *
 * @author morven
 */
class OrderResponse_Controller extends Page_Controller {
    public static $url_segment = 'orderresponse/$Action/$ID';
    
    public static $allowed_actions = array(
        'success',
        'failer'
    );

    public function init() {
        parent::init();
        
        if(!($this->urlParams['Action']) && !($this->urlParams['ID']))
            Director::redirect (BASE_URL);
    }
    
    public function index() {
        Director::redirect (BASE_URL);
    }
    
    public function success() {
        $site = Subsite::currentSubsite();
        $order = $this->getOrder();
        
        if($order && $order->OrderNumber == $this->urlParams['ID']) {
            $order->Status = 'paid';
            $order->write();
            
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
            
            unset($_SESSION['Order']);
            unset($_SESSION['Cart']);
            unset($_SESSION['PostageID']);
            
            $content = $site->SuccessCopy;
        } else
            $content = _t('Commerce.ORDERERROR',"An error occured, Order ID's do not match") . ".<br/><br/>" . _t('Commerce.ORDERCONTACT',"Please contact us with more details") . '.';
            
        return array(
            'Title'     => _t('Commerce.ORDERCOMPLETE','Order Complete'),
            'Content'   => $content
        );
    }
    
    public function failer() {
        $site = Subsite::currentSubsite();
        
        $order = $this->getOrder();
        
        if($order && $order->OrderNumber == $this->urlParams['ID']) {
            $order->Status = 'failed';
            $order->write();
            
            unset($_SESSION['Order']);
            unset($_SESSION['Cart']);
            unset($_SESSION['PostageID']);
            
            $content = $site->FailerCopy;
            
        } else
            $content = _t('Commerce.ORDERERROR',"An error occured, Order ID's do not match") . ".<br/><br/>" . _t('Commerce.ORDERCONTACT',"Please contact us with more details") . '.';
        
        return array(
            'Title'     => _t('Commerce.ORDERFAILED','Order Failed'),
            'Content'   => $content
        );
    }
    
    public function getOrder() {
        return Session::get('Order');
    }
}