<?php
/**
 * Summary Controller is responsible for displaying all order data before posting
 * to the final payment gateway.
 *
 * @author morven
 * @package commerce
 */

class Payment_Controller extends Page_Controller {
    public static $url_segment = "payment";
    
    static $allowed_actions = array(
        'index',
        'success',
        'failer',
        'error',
        'callback'
    );
    
    public function init() {
        parent::init();
    }
    
    public function index() {
        $vars = array(
            'ClassName' => "Payment",
            'Title'     => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            'MetaTitle' => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
        );
    
        return $this->renderWith(array('Payment','Page'), $vars);
    }
    
    public function getOrder() {
        return Session::get('Order');
    }
    
    public function getPaymentMethod() {
        // First check session
        if($method = CommercePaymentMethod::get()->byID(Session::get('PaymentMethod')))
            return $method;
        // Then check if payment slug is set and that corresponds to a payment
        elseif($this->request->param('ID') && $method = CommercePaymentMethod::get()->filter('CallBackSlug',$this->request->param('ID'))->first())
            return $method;
        else
            return false;
    }

    // Get the existing gateway data from the relevent PaymentMethod object
    public function getGatewayData() {
        $payment_method = $this->getPaymentMethod();
        
        return $this->renderWith('GatewayData_' . $payment_method->ClassName, $payment_method->GatewayData());  
    }
    
    public function GatewayForm() {
        $payment = $this->getPaymentMethod();
        
        $form = new Form($this, $payment->Title . 'Form', $payment->getGatewayFields(), $payment->getGatewayActions());
        $form->setFormMethod('POST');
        $form->setFormAction($payment->GatewayURL());
        
        return $form;
    }
    
    // Get relevent payment gateway URL to use in HTML form
    public function getGatewayURL() {
        return $this->getPaymentMethod()->GatewayURL();
    }
    
    /**
     * Method to clear any existing sessions related to commerce module
     */    
    public function ClearSessionData() {
        ShoppingCart::get()->clear();
        unset($_SESSION['Order']);
        unset($_SESSION['PostageID']);
        unset($_SESSION['PaymentMethod']);
    }
    
    /**
     * This method takes any post data submitted via a payment provider and
     * sends it to the relevent gateway class for processing
     *
     */
    public function callback() {
        if($this->request->postVars()) {
            $callback = $this->getPaymentMethod()->ProcessCallback($this->request->postVars());
      
            if($callback) {
                $this->redirect(Controller::join_links(BASE_URL , self::$url_segment, 'success'));
            } else
                $this->redirect(Controller::join_links(BASE_URL , self::$url_segment, 'error'));
        } else
            $result = false;
        
        if($result == false)
            $this->httpError(500);
    }
    
    /*
     * Method called when payement gateway returns the sucess URL
     *
     * @return array
     */
    public function success() {
        $site = SiteConfig::current_site_config();
        $session_order = $this->getOrder();
        
        if($session_order && $session_order->OrderNumber) {   
            // Quick Fix: Remove all items on existing order
            foreach($session_order->Items() as $item) {
                $session_order->Items()->remove($item);
            }
            
            $order = Order::get()->filter('OrderNumber', $session_order->OrderNumber)->first();
            
            if($order->Status == 'paid') {
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
            }
        }
        
        $this->ClearSessionData();
        
        $vars = array(
            'Title'     => _t('Commerce.ORDERCOMPLETE','Order Complete'),
            'Content'   => ($site->SuccessCopy) ? $site->SuccessCopy : false
        );
        
        return $this->renderWith(array('Payment_Response','Page'), $vars);
    }
    
    /*
     * Method called when payement gateway returns the failer URL
     *
     * @return array
     */
    public function failer() {
        $site = SiteConfig::current_site_config();
        
        if($order = $this->getOrder()) {
            // Quick Fix: Remove all items on existing order
            foreach($order->Items() as $item) {
                $order->Items()->remove($item);
            }
        }
        
        $this->ClearSessionData();
        
        $vars = array(
            'Title'     => _t('Commerce.ORDERFAILED','Order Failed'),
            'Content'   => ($site->FailerCopy) ? $site->FailerCopy : false
        );
        
        return $this->renderWith(array('Payment_Response','Page'), $vars);
    }
    
    /*
     * Represents an error in the in all stages of the payment process
     *
     */
    public function error() {
        $this->ClearSessionData();
        
        $vars = array(
            'Title'     => _t('Commerce.ORDERERROR',"An error occured, Order ID's do not match"),
            'Content' => _t('Commerce.ORDERCONTACT',"Please contact us with more details")
        );
    
        return $this->renderWith(array('Payment_Response','Page'), $vars);
    }
}
