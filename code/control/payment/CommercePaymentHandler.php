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
     * @var CommercePaymentMethod
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
     * The current order we are dealing with
     *
     * @var Order
     */
    protected $order;

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function getPaymentInfo() {
        return $this->payment_gateway->PaymentInfo;
    }

    /**
     * The index action is called by the payment controller before order
     * is processed by relevent payment gateway.
     *
     * This action should return a rendered response that will then be
     * directly reterned by the payment controller.
     */
    abstract public function index();


    /**
     * Retrieve and process callback info from the payment gateway.
     *
     * This action is called directly from the payment controller and
     * should return either a rendered template or a response (such as a
     * redirect).
     */
    abstract public function callback();
}
