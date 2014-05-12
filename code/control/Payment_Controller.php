<?php
/**
 * Summary Controller is responsible for displaying all order data before posting
 * to the final payment gateway.
 *
 * @author morven
 * @package commerce
 */

class Payment_Controller extends Commerce_Controller {
    public static $url_segment = "commerce/payment";

    private static $allowed_actions = array(
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
        return Session::get('Commerce.Order');
    }

    public function init() {
        parent::init();

        // Check if payment slug is set and that corresponds to a payment
        if($this->request->param('ID') && $method = CommercePaymentMethod::get()->byID($this->request->param('ID')))
            $this->payment_method = $method;
        // Then check session
        elseif($method = CommercePaymentMethod::get()->byID(Session::get('Commerce.PaymentMethodID')))
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
        if(!ShoppingCart::get()->Items()->exists() || $this->getPaymentHandler() === null)
            return $this->redirect(Director::BaseURL());

        // Work out if an order prefix string has been set in siteconfig
        $config = SiteConfig::current_site_config();
        $order_prefix = ($config->OrderPrefix) ? $config->OrderPrefix . '-' : '';

        // Get billing and delivery details and merge into an array
        $billing_data = Session::get("Commerce.BillingDetailsForm.data");
        $delivery_data = Session::get("Commerce.DeliveryDetailsForm.data");
        $data = array_merge((array)$billing_data, (array)$delivery_data);

        // Get postage data
        $postage = PostageArea::get()->byID(Session::get('Commerce.PostageID'));
        $data['PostageType'] = $postage->Location;
        $data['PostageCost'] = $postage->Cost;
        $data['PostageTax'] = ($config->TaxRate > 0 && $postage->Cost > 0) ? ((float)$postage->Cost / 100) * $config->TaxRate : 0;

        // Set status
        $data['Status'] = 'incomplete';

        // Setup an order based on the data from the shopping cart and load data
        $order = new Order();
        $order->populate($data);

        // If user logged in, track it against an order
        if(Member::currentUserID()) $order->CustomerID = Member::currentUserID();

        // Write so we can setup our foreign keys
        $order->write();

        // Loop through each session cart item and add that item to the order
        foreach(ShoppingCart::get()->Items() as $cart_item) {
            $order_item = new OrderItem();
            $order_item->Title          = $cart_item->Title;
            $order_item->SKU            = $cart_item->SKU;
            $order_item->Price          = $cart_item->Price;
            $order_item->Tax            = $cart_item->Tax;
            $order_item->Customisation  = serialize($cart_item->Customised);
            $order_item->Quantity       = $cart_item->Quantity;
            $order_item->write();

            $order->Items()->add($order_item);
        }

        // Add order to session so our payment handler can process it
        Session::set("Commerce.Order", $order);

        // Perform pre gateway action and return data (if any)
        $data = $this->payment_handler->onBeforeGateway();

        if(is_array($data)) {

            // Setup gateway form
            $form = $this->payment_handler->GatewayForm($data);

            // Finally, save order to database before transport
            $order = $this->getOrder();
            $order->write();

            $vars = array(
                'ClassName'   => "Payment",
                'Title'       => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
                'MetaTitle'   => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
                'GatewayForm' => $form,
                'Order'       => $order
            );

            return $this->renderWith(array('Payment','Page'), $vars);
        } else {
            $error_url = Controller::join_links(
                Director::absoluteBaseURL(),
                Payment_Controller::$url_segment,
                "callback"
            );

            return $this->redirect($error_url);
         }
    }

    /**
     * This method is what is called at the end of the transaction. It takes
     * either post data or get data and then sends it to the relevent payment
     * method for processing.
     */
    public function callback() {
        // See if data has been passed via the request
        if($this->request->postVars())
            $data = $this->request->postVars();
        elseif(count($this->request->getVars()) > 1)
            $data = $this->request->getVars();
        else
            $data = false;

        $handler = $this->getPaymentHandler();

        // If post data exists, process. Otherwise provide error
        if($data && $handler !== null) {
            $callback = $handler->ProcessCallback(
                $data,
                $this->success_data(),
                $this->error_data()
            );
        } else {
            // Redirect to error page
            return $this->redirect(Controller::join_links(
                Director::BaseURL(),
                self::$url_segment,
                'complete',
                'error'
            ));
        }

        return $callback;
    }

    /*
     * Deal with rendering a completion message to the end user
     *
     * @return String
     */
    public function complete() {
        $site = SiteConfig::current_site_config();
        $order = $this->getOrder();

        $id = $this->request->param('ID');

        if($id == "error")
            $return = $this->error_data();
        else
            $return = $this->success_data();

        if($order) {
            $return['CommerceOrderSuccess'] = true;
            $return['Order'] = $order;
        } else {
            $return['CommerceOrderSuccess'] = false;
            $return['Order'] = false;
        }

        // Clear our session data
        if(isset($_SESSION)) {
            ShoppingCart::get()->clear();
            unset($_SESSION['Commerce.Order']);
            unset($_SESSION['Commerce.PostageID']);
            unset($_SESSION['Commerce.PaymentMethod']);
        }

        return $this->customise($return)->renderWith(array("Payment_Response",'Page'));
    }

    /*
     * Pull together data to be used in success templates
     *
     * @return array
     */
    public function success_data() {
        $site = SiteConfig::current_site_config();

        return array(
            'Title' => _t('Commerce.ORDERCOMPLETE','Order Complete'),
            'Content' => ($site->SuccessCopy) ? nl2br(Convert::raw2xml($site->SuccessCopy), true) : false
        );
    }

    /*
     * Pull together data to be used in success templates
     *
     * @return array
     */
    public function error_data() {
        $site = SiteConfig::current_site_config();

        return array(
            'Title'     => _t('Commerce.ORDERFAILED','Order Failed'),
            'Content'   => ($site->FailerCopy) ? nl2br(Convert::raw2xml($site->FailerCopy), true) : false
        );
    }
}
