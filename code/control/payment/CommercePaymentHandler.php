<?php

/**
 * Abstract class that contains methods for processing interactions with a
 * particular payment class
 *
 */
abstract class CommercePaymentHandler extends Controller {

    /**
     * The current payment gateway we are using
     *
     */
    protected $payment_gateway;

    public function getPaymentGateway() {
        return $this->payment_gateway;
    }

    public function setPaymentGateway($gateway) {
        $this->payment_gateway = $gateway;
        return $this;
    }

    /**
     * Method that allows us to perform additional actions that happen before we
     * generate the payment gateway form
     *
     * @return ArrayList
     *
     */
    public function onBeforeGateway() {
        $data = array();

        $this->extend('onBeforeGateway', $data);

        return $data;
    }

    /**
     * Return a form that will be loaded into the Payment template and will post
     * to the payment gateway provider.
     *
     * @return Form
     */
    public function GatewayForm($data) {

        // See if the gateway URL has been set externally, else use the deafult
        $form_action = (isset($data['GatewayURL'])) ? $data['GatewayURL'] : $this->payment_gateway->GatewayURL();

        $form = Form::create(
            $this,
            'CommerceGatewayForm',
            $this->gateway_fields(), // Fields for this gateway
            $this->gateway_actions() // Actions for this gateway
        );

        $form->addExtraClass('forms');
        $form->setFormMethod('POST');
        $form->setFormAction($form_action);

        $this->extend('updateCommerceGatewayForm',$form);

        return $form;
    }

    /**
     * Return a form that will be loaded into the Payment template and will post
     * to the payment gateway provider.
     *
     * @return Form
     */
    protected function gateway_fields() {
        user_error('You have not added a GatewayFields() method on your Payment Handler Class');
    }


    /**
     * Return a form that will be loaded into the Payment template and will post
     * to the payment gateway provider.
     *
     * @return Form
     */
    protected function gateway_actions() {
        $back_url = Controller::join_links(
            BASE_URL,
            Checkout_Controller::$url_segment
        );

        $actions = new FieldList(
            LiteralField::create('BackButton','<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'),
            FormAction::create('Submit', _t('Commerce.CONFIRMPAY','Confirm and Pay'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-green')
        );

        return $actions;
    }


    /**
     * Retrieve and process order data from the request
     *
     * @var $data request data
     * @var $success_data initial success vars
     * @var $error_data initial success vars
     */
    public function ProcessCallback($data = null, $success_data, $error_data) {
        user_error('You have not added a ProcessCallback() method on your Payment Handler Class');
    }
}
