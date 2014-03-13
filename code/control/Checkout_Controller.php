<?php


class Checkout_Controller extends Page_Controller {
    public static $url_segment = "checkout";

    public static $allowed_actions = array(
        'delivery',
        'BillingForm',
        'DeliveryForm'
    );

    /**
     * Overwrite default link behaviour. Get the URL segment from the current
     * controller and then
     *
     * @return String
     */
    public function Link($action = null) {
        return Controller::join_links(
            self::$url_segment,
            $action
        );
    }

    public function init() {
        parent::init();

        // If no shopping cart doesn't exist, redirect to base
        if(!ShoppingCart::get()->Items()->exists())
            return $this->redirect(BASE_URL);
    }

    /**
     * Catch the default dilling information of the visitor
     *
     * @return array
     */
    public function index() {
        return array(
            'ClassName' => "Checkout",
            'Title'     => _t('Commerce.BILLINGDETAILS',"Billing Details"),
            'MetaTitle' => _t('Commerce.BILLINGDETAILS',"Billing Details"),
            'Form'      => $this->BillingForm()
        );
    }

    /**
     * Use to catch the users delivery details, if different to their billing
     * details
     *
     * @var array
     */
    public function delivery() {
        return array(
            'ClassName' => "Checkout",
            'Title'     => _t('Commerce.DELIVERYDETAILS',"Delivery Details"),
            'MetaTitle' => _t('Commerce.DELIVERYDETAILS',"Delivery Details"),
            'Form'      => $this->DeliveryForm()
        );
    }

    public function BillingForm() {
        return BillingDetailsForm::create($this, 'BillingForm')
            ->addExtraClass('forms')
            ->addExtraClass('columnar')
            ->addExtraClass('row');
    }

    public function DeliveryForm() {
        return DeliveryDetailsForm::create($this, 'DeliveryForm')
            ->addExtraClass('forms')
            ->addExtraClass('columnar')
            ->addExtraClass('row');
    }
}
