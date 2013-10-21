<?php

class SagePayServerHandler extends CommercePaymentHandler {
    public function onBeforeGateway() {
        $data = parent::onBeforeGateway();

        return $data;
    }

    protected function gateway_fields() {
        $order = Session::get('Order');
        $site = SiteConfig::current_site_config();

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::$url_segment,
            "callback",
            $this->CallBackSlug
        );

        $fields = new FieldList(
            HiddenField::create('navigate'),
            HiddenField::create('VPSProtocol')->setValue($this->ProtocolVersion),
            HiddenField::create('TxType')->setValue('PAYMENT'),
            HiddenField::create('Vendor')->setValue($this->VendorName),

            // Order details
            HiddenField::create("VendorTxCode")->setValue($order->OrderNumber),
            HiddenField::create("Amount")->setValue($order->getOrderTotal()), // Formatted to 2 decimal places with leading digit
            HiddenField::create("Currency")->setValue($site->Currency()->GatewayCode),
            HiddenField::create("Description")->setValue($this->GatewayMessage),
            HiddenField::create("NotificationURL")->setValue($callback_url),
            HiddenField::create("SuccessURL")->setValue($callback_url),
            HiddenField::create("FailureURL")->setValue($callback_url),
            HiddenField::create("CustomerName")->setValue($order->BillingFirstnames . " " . $order->BillingSurname),
            HiddenField::create("SendEMail")->setValue($this->SendEmail),
            HiddenField::create("CustomerEMail")->setValue($order->BillingEmail),
            HiddenField::create("VendorEMail")->setValue($this->EmailRecipient),

            // Billing details
            HiddenField::create("BillingFirstnames")->setValue($order->BillingFirstnames),
            HiddenField::create("BillingSurname")->setValue($order->BillingSurname),
            HiddenField::create("BillingAddress1")->setValue($order->BillingAddress1),
            HiddenField::create("BillingAddress2")->setValue($order->BillingAddress2),
            HiddenField::create("BillingCity")->setValue($order->BillingCity),
            HiddenField::create("BillingPostCode")->setValue($order->BillingPostCode),
            HiddenField::create("BillingCountry")->setValue($order->BillingCountry),
            HiddenField::create("BillingState")->setValue($order->BillingState),
            HiddenField::create("BillingPhone")->setValue($order->BillingPhone),

            // Delivery details
            HiddenField::create("DeliveryFirstnames")->setValue($order->DeliveryFirstnames),
            HiddenField::create("DeliverySurname")->setValue($order->DeliverySurname),
            HiddenField::create("DeliveryAddress1")->setValue($order->DeliveryAddress1),
            HiddenField::create("DeliveryAddress2")->setValue($order->DeliveryAddress2),
            HiddenField::create("DeliveryCity")->setValue($order->DeliveryCity),
            HiddenField::create("DeliveryPostCode")->setValue($order->DeliveryPostCode),
            HiddenField::create("DeliveryCountry")->setValue($order->DeliveryCountry),
            HiddenField::create("DeliveryState")->setValue($order->DeliveryState),
            HiddenField::create("DeliveryPhone")->setValue($order->DeliveryPhone),

            // For charities registered for Gift Aid
            HiddenField::create("AllowGiftAid")->setValue(0),

            // 3D secure
            HiddenField::create("Apply3DSecure")->setValue(0)
        );

        return $fields;
    }

    /**
     * Try and retrieve order data from the request
     *
     */
    public function ProcessCallback($data = null) {
        // Check if CallBack data exists and install id matches the saved ID
        if(isset($data) && isset($data['crypt'])) {
            // Clear Sagepay '@' symbol (denotes encrypted data)
            if(substr($data['crypt'],0,1) == "@")
                $data['crypt'] = substr($data['crypt'], 1);

            // Now decode the Crypt field and extract the results
            $crypt_decoded = StringDecryptor::create($data['crypt'])
                                                ->setHash($this->EncryptedPassword)
                                                ->setEncryption('MCRYPT')
                                                ->decrypt()
                                                ->get();

            $values = $this->getToken($crypt_decoded);

            $order = Order::get()
                        ->filter(array(
                            'OrderNumber' => $values['VendorTxCode'],
                            'Status' => 'incomplete'
                        ))->first();

            $order_status = $values['Status'];

            if($order) {
                $order->Status = ($order_status == 'OK' || $order_status == 'AUTHENTICATED') ? 'paid' : 'failed';
                $order->write();

                if($order_status == 'OK' || $order_status == 'AUTHENTICATED')
                    return true;
                else
                    return false;
            }
        }

        return false;
    }
}
