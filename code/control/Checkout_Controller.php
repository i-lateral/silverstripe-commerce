<?php

/**
 * Controller used to render the checkout process
 *
 */
class Checkout_Controller extends Commerce_Controller {
    public static $url_segment = "commerce/checkout";

    private static $allowed_actions = array(
        "details",
        "LoginForm",
        "CheckoutForm"
    );

    public function init() {
        parent::init();

        // If no shopping cart doesn't exist, redirect to base
        if(!ShoppingCart::get()->Items()->exists())
            return $this->redirect(BASE_URL);
    }

    public function index() {
        if(Member::currentUserID()) {
            $this->redirect($this->Link('details'));
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

    public function details() {
        $form = $this->CheckoutForm();

        // Pre populate form with member info
        if(Member::currentUserID())
            $form->loadDataFrom(Member::currentUser());

        $this->customise(array(
            'ClassName'     => "CheckoutDetails",
            'Title'         => _t('Commerce.CHECKOUTMETA',"Your Details"),
            'MetaTitle'     => _t('Commerce.CHECKOUTMETA',"Your Details"),
            'CheckoutForm'  => $form
        ));

        $this->extend("onBeforeDetails");

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

    public function CheckoutForm() {
        $form = Commerce_CheckoutForm::create($this, 'CheckoutForm')
            ->addExtraClass('forms');

        return $form;
    }
}
