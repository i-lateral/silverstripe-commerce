<?php

/**
 * Controller used to render the checkout process
 *
 */
class Checkout_Controller extends Commerce_Controller {
    public static $url_segment = "commerce/checkout";

    private static $allowed_actions = array(
        "billing",
        "delivery",
        "LoginForm",
        'BillingForm',
        'DeliveryForm'
    );

    public function init() {
        parent::init();

        // If no shopping cart doesn't exist, redirect to base
        if(!ShoppingCart::get()->Items()->exists())
            return $this->redirect(BASE_URL);
    }

    /**
     * If user logged in, redirect to billing info, else show login, register
     * or "checkout as guest" options.
     *
     * @return String
     */
    public function index() {
        if(Member::currentUserID()) {
            $this->redirect($this->Link('billing'));
        } else {
            $this->customise(array(
                'ClassName' => "CheckoutLogin",
                'Title'     => _t('CommerceAccount.SIGNIN',"Sign in"),
                'MetaTitle' => _t('CommerceAccount.SIGNIN',"Sign in"),
                'Content'   => $this->renderWith("Commerce_Checkout_Login"),
                'LoginForm' => $this->LoginForm()
            ));

            $this->extend("onBeforeIndex");

            return $this->renderWith(array(
                'Commerce_checkout',
                'Commerce',
                'Page'
            ));
        }
    }


    /**
     * Catch the default dilling information of the visitor
     *
     * @return array
     */
    public function billing() {
        $form = $this->BillingForm();

        // Pre populate form with member info
        if(Member::currentUserID())
            $form->loadDataFrom(Member::currentUser());

        $this->customise(array(
            'ClassName' => "Checkout",
            'Title'     => _t('Commerce.BILLINGDETAILS',"Billing Details"),
            'MetaTitle' => _t('Commerce.BILLINGDETAILS',"Billing Details"),
            'Form'      => $form
        ));

        $this->extend("onBeforeBilling");

        return $this->renderWith(array(
            'Commerce_checkout',
            'Commerce',
            'Page'
        ));
    }


    /**
     * Use to catch the users delivery details, if different to their billing
     * details
     *
     * @var array
     */
    public function delivery() {
        $this->customise(array(
            'ClassName' => "Checkout",
            'Title'     => _t('Commerce.DELIVERYDETAILS',"Delivery Details"),
            'MetaTitle' => _t('Commerce.DELIVERYDETAILS',"Delivery Details"),
            'Form'      => $this->DeliveryForm()
        ));

        $this->extend("onBeforeDelivery");

        return $this->renderWith(array(
            'Commerce_checkout',
            'Commerce',
            'Page'
        ));
    }

    /**
     * Generate a login form
     *
     * @return UsernameOrEmailLoginForm
     */
    public function LoginForm() {
        $form = MemberLoginForm::create($this, 'LoginForm');
        $form->setAttribute("action", $this->Link("LoginForm"));

        $form
            ->Actions()
            ->dataFieldByName('action_dologin')
            ->addExtraClass("btn");

        return $form;
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
