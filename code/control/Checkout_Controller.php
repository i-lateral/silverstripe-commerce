<?php

/**
 * Controller used to render the checkout process
 *
 */
class Checkout_Controller extends Commerce_Controller {
    public static $url_segment = "commerce/checkout";

    private static $allowed_actions = array(
        "Form"
    );

    public function init() {
        parent::init();

        // If no shopping cart doesn't exist, redirect to base
        if(!ShoppingCart::get()->Items()->exists())
            return $this->redirect(BASE_URL);
    }

    public function index() {
        $this->customise(array(
            'ClassName' => "Checkout",
            'Title'     => _t('Commerce.CHECKOUTMETA',"Your Details"),
            'MetaTitle' => _t('Commerce.CHECKOUTMETA',"Your Details"),
        ));

        return $this->renderWith(array(
            'Commerce_checkout',
            'Commerce',
            'Page'
        ));
    }

    public function Form() {
        $form = Commerce_CheckoutForm::create($this, 'Form')
            ->addExtraClass('forms');

        if(Member::currentUserID()) {
            $member = Member::currentUser();
            $form->loadDataFrom($member);
        }

        return $form;
    }
}
