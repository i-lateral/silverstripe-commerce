<?php

class WorldPayHandler extends CommercePaymentHandler {

    protected function gateway_fields() {
        $order = Session::get('Commerce.Order');
        $site = SiteConfig::current_site_config();

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Commerce_Payment_Controller::$url_segment,
            "callback",
            $this->payment_gateway->ID
        );

        $fields = new FieldList(
            // Account details
            HiddenField::create('instId', null, $this->payment_gateway->InstallID),
            HiddenField::create('cartId', null, $order->OrderNumber),

            // Amount and Currency details
            HiddenField::create('amount', null, $order->Total->Value),
            HiddenField::create('currency', null, $site->Currency()->GatewayCode),

            // Payee details
            HiddenField::create('name', null, $order->FirstName . " " . $order->Surname),
            HiddenField::create('address1', null, $order->Address1),
            HiddenField::create('address2', null, $order->Address2),
            HiddenField::create('town', null, $order->City),
            HiddenField::create('region', null, $order->State),
            HiddenField::create('postcode', null, $order->PostCode),
            HiddenField::create('country', null, $order->Country),
            HiddenField::create('email', null, $order->Email)
        );

        if($this->payment_gateway->GatewayMessage)
            $fields->add(HiddenField::create('desc', null, $this->payment_gateway->GatewayMessage));

        if(Director::isDev())
            $fields->add(HiddenField::create('testMode', null, '100'));

        return $fields;
    }

    public function ProcessCallback($data = null, $success_data, $error_data) {
        $successs_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Commerce_Payment_Controller::$url_segment,
            'complete'
        );

        $error_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Commerce_Payment_Controller::$url_segment,
            'complete',
            'error'
        );

        $vars = array(
            "SiteConfig" => SiteConfig::current_site_config(),
            "RedirectURL" => $error_url
        );

        // Check if CallBack data exists and install id matches the saved ID
        if(
            isset($data) && // Data and order are set
            (isset($data['instId']) && isset($data['cartId']) && isset($data['transStatus']) && isset($data["callbackPW"])) && // check required
            $this->payment_gateway->InstallID == $data['instId'] && // The current install ID matches the postback ID
            $this->payment_gateway->ResponsePassword == $data["callbackPW"]
        ) {
            $order = Order::get()->filter('OrderNumber',$data['cartId'])->first();
            $order_status = $data['transStatus'];

            if($order) {
                if($order_status == 'Y') {
                    $order->Status = 'paid';
                    $vars = array(
                        "SiteConfig" => SiteConfig::current_site_config(),
                        "RedirectURL" => $success_url
                    );
                } else {
                    $order->Status = 'failed';
                }

                // Store all the data sent from the gateway in a json
                $order->GatewayData = json_encode($data);
                $order->write();
            }
        }

        $this->extend("updateCallBack", $vars);

        return $this->renderWith(array("Payment_WorldPay"), $vars);
    }

}
