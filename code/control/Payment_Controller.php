<?php
/**
 * Summary Controller is responsible for displaying all order data before posting
 * to the final payment gateway.
 *
 * @author morven
 */

class Payment_Controller extends Page_Controller {
    public static $url_segment = "payment";
    
    static $allowed_actions = array(
        'index',
        'success',
        'failer',
        'callback'
    );
    
    public function init() {
        parent::init();
        
        // If current order has not been set, re-direct to homepage
        //if(!Session::get('Order')) 
        //    $this->redirect(BASE_URL);

        //if(!Session::get('PaymentMethod'))
        //    $this->redirect(Controller::join_links(BASE_URL , ShoppingCart_Controller::$url_segment));
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
     * This method takes any post data submitted via a payment provider and
     * sends it to the relevent gateway class for processing
     *
     */
    public function callback() {
        if($this->request->postVars()) {
            $callback = $this->getPaymentMethod()->ProcessCallback($this->request->postVars());
      
            if($callback) {
                $vars = array(
                    'Title'     => _t('Commerce.ORDERCOMPLETE','Order Complete')
                );
        
                return $this->renderWith(array('Payment_Response','Page'), $vars);
            } else
                $result = false;
            
            
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
        $order = $this->getOrder();
        
        // Quick Fix: Remove all items on existing order
        foreach($order->Items() as $item) {
            $order->Items()->remove($item);
        }
        
        if($order && $order->OrderNumber == $this->urlParams['ID']) {
            $order->Status = 'paid';
            $order->write();
            
            // Loop through each session cart item and add that item to the order
            foreach(ShoppingCart::get()->Items() as $cart_item) {
                $order_item = new OrderItem();
                $order_item->Title          = $cart_item->Title;
                $order_item->Price          = $cart_item->Price;
                $order_item->Customisation  = serialize($cart_item->Customised);
                $order_item->Quantity       = $cart_item->Quantity;

                $order->Items()->add($order_item);
            }
            
            ShoppingCart::get()->clear();
            
            unset($_SESSION['Order']);
            unset($_SESSION['PostageID']);
            unset($_SESSION['PaymentMethod']);
            
        }
        
        $content = ($site->SuccessCopy) ? $site->SuccessCopy : false;    
        
        $vars = array(
            'Title'     => _t('Commerce.ORDERCOMPLETE','Order Complete'),
            'Content'   => $content
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
        $order = $this->getOrder();
        
        // Quick Fix: Remove all items on existing order
        foreach($order->Items() as $item) {
            $order->Items()->remove($item);
        }
        
        if($order && $order->OrderNumber == $this->urlParams['ID']) {
            $order->Status = 'failed';
            $order->write();
            
            ShoppingCart::get()->clear();
            
            unset($_SESSION['Order']);
            unset($_SESSION['PostageID']);
            unset($_SESSION['PaymentMethod']);
        }
                
        $content = ($site->FailerCopy) ? $site->FailerCopy : false;
        
        $vars = array(
            'Title'     => _t('Commerce.ORDERFAILED','Order Failed'),
            'Content'   => $content
        );
        
        return $this->renderWith(array('Payment_Response','Page'), $vars);
    }
}
