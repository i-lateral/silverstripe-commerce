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

    public static $allowed_actions = array(
        'index',
        'callback',
        'complete'
    );

    protected $payment_handler;

    public function getPaymentHandler() {
        return $this->payment_handler;
    }

    public function setPaymentHandler($handler) {
        $this->payment_handler = $handler;
        return $this;
    }


    protected $payment_method;

    public function getPaymentMethod() {
        return $this->payment_method;
    }

    public function setPaymentMethod($method) {
        $this->payment_method = $method;
        return $this;
    }

    /**
     * Find the current order
     *
     * @return Order
     */
    public function getOrder() {
        return Session::get('Order');
    }

    public function init() {
        parent::init();

        // Check if payment slug is set and that corresponds to a payment
        if($this->request->param('ID') && $method = CommercePaymentMethod::get()->filter('CallBackSlug',$this->request->param('ID'))->first())
            $this->payment_method = $method;
        // Then check session
        elseif($method = CommercePaymentMethod::get()->byID(Session::get('PaymentMethod')))
            $this->payment_method = $method;

        // Setup payment handler
        if($this->payment_method && $this->payment_method !== null) {
            $handler = $this->payment_method->ClassName;
            $handler = $handler::$handler;

            $this->payment_handler = $handler::create();
            $this->payment_handler->setPaymentGateway($this->getPaymentMethod());
        }
    }

    public function index() {
        // If shopping cart doesn't exist, redirect to base
        if(!ShoppingCart::get()->Items()->exists())
            return $this->redirect(Director::BaseURL());

        // Perform pre gateway action and return data (if any)
        $data = $this->payment_handler->onBeforeGateway();

        // Setup gateway form
        $form = $this->payment_handler->GatewayForm($data);

        $vars = array(
            'ClassName'   => "Payment",
            'Title'       => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            'MetaTitle'   => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            'GatewayForm' => $form
        );

        return $this->renderWith(array('Payment','Page'), $vars);
    }

    /**
     * This method is what is called at the end of the transaction. It takes
     * either post data or get data and then sends it to the relevent payment
     * method for processing.
     *
     */
    public function callback() {
        // See if data has been passed via the request
        if($this->request->postVars())
            $data = $this->request->postVars();
        elseif(count($this->request->getVars()) > 1)
            $data = $this->request->getVars();
        else
            $data = false;

        $this->extend('onBeforeCommerceCallback', $data);

        // If post data exists, process. Otherwise provide error
        if($data) {
            $callback = $this->payment_handler->ProcessCallback($data);

            if($callback)
                $return = $this->success();
            else
                $return = $this->error();
        } else
            $return = $this->error();

        $this->extend('onAfterCommerceCallback', $return);

        // Clear our session data
        if(isset($_SESSION)) {
            ShoppingCart::get()->clear();
            unset($_SESSION['Order']);
            unset($_SESSION['PostageID']);
            unset($_SESSION['PaymentMethod']);
        }

        // Render our return values
        return $this->customise($return)->renderWith(array('Payment_Response','Page'));
    }

    /*
     * Method called when payement gateway returns the sucess URL
     *
     * @return array
     */
    public function success() {
        $site = SiteConfig::current_site_config();
        $order = $this->getOrder();

        if($order)
            $commerceOrderSuccess = true;
        else {
            $commerceOrderSuccess = false;
            $order = false;
        }

        return array(
            'CommerceOrderSuccess' => $commerceOrderSuccess,
            'Order' => $order,
            'Title' => _t('Commerce.ORDERCOMPLETE','Order Complete'),
            'Content' => ($site->SuccessCopy) ? nl2br(Convert::raw2xml($site->SuccessCopy), true) : false
        );
    }

    /*
     * Represents an error in the in all stages of the payment process
     *
     */
    public function error() {
        $site = SiteConfig::current_site_config();

        return array(
            'Title'     => _t('Commerce.ORDERFAILED','Order Failed'),
            'Content'   => ($site->FailerCopy) ? nl2br(Convert::raw2xml($site->FailerCopy), true) : false
        );
    }
}
