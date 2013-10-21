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
        $data = new ArrayList();

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
        $actions = new FieldList(
            LiteralField::create('BackButton','<a href="' . BASE_URL . '/' . Checkout_Controller::$url_segment . '" class="btn commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'),
            FormAction::create('Submit', _t('Commerce.CONFIRMPAY','Confirm and Pay'))->addExtraClass('btn')->addExtraClass('highlight')->addExtraClass('commerce-action-next')
        );

        return $actions;
    }


    /**
     * Process the call back from the payment provider
     *
     * @param order the order stored in the session
     * @param data post data from the form
     */
    public function ProcessCallback($data = null) {
        user_error('You have not added a ProcessCallback() method on your Payment Handler Class');
    }
}
