<?php

class SagePayServerHandler extends CommercePaymentHandler {

    /**
     * SagePay server integration requires a "pre submit" authentication,
     * meaning we have to post the order details in the background prior to
     * sending a customer to the payment gateway.
     *
     * We do this using onBeforeGateway API hook and modify the output
     *
     */
    public function onBeforeGateway() {
        $data = parent::onBeforeGateway();

        $order = Session::get('Order');
        $site = SiteConfig::current_site_config();

        $error_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::$url_segment,
            "callback"
        );

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::$url_segment,
            "callback",
            $this->CallBackSlug
        );

        $payload_data = array();

        $payload_data['VPSProtocol'] = $this->payment_gateway->ProtocolVersion;
        $payload_data['TxType'] = 'PAYMENT';
        $payload_data['Vendor'] = $this->payment_gateway->VendorName;

        // Order details
        $payload_data["VendorTxCode"] = $order->OrderNumber;
        $payload_data["Amount"] = $order->getOrderTotal();
        $payload_data["Currency"] = $site->Currency()->GatewayCode;
        $payload_data["Description"] = $this->payment_gateway->GatewayMessage;
        $payload_data["NotificationURL"] = $callback_url;
        $payload_data["SuccessURL"] = $callback_url;
        $payload_data["FailureURL"] = $callback_url;
        $payload_data["CustomerName"] = $order->BillingFirstnames . " " . $order->BillingSurname;
        $payload_data["SendEMail"] = $this->payment_gateway->SendEmail;
        $payload_data["CustomerEMail"] = $order->BillingEmail;
        $payload_data["VendorEMail"] = $this->payment_gateway->EmailRecipient;

        // Billing details
        $payload_data["BillingFirstnames"] = $order->BillingFirstnames;
        $payload_data["BillingSurname"] = $order->BillingSurname;
        $payload_data["BillingAddress1"] = $order->BillingAddress1;
        $payload_data["BillingAddress2"] = $order->BillingAddress2;
        $payload_data["BillingCity"] = $order->BillingCity;
        $payload_data["BillingPostCode"] = $order->BillingPostCode;
        $payload_data["BillingCountry"] = $order->BillingCountry;
        $payload_data["BillingState"] = $order->BillingState;
        $payload_data["BillingPhone"] = $order->BillingPhone;

        // Delivery details
        $payload_data["DeliveryFirstnames"] = $order->DeliveryFirstnames;
        $payload_data["DeliverySurname"] = $order->DeliverySurname;
        $payload_data["DeliveryAddress1"] = $order->DeliveryAddress1;
        $payload_data["DeliveryAddress2"] = $order->DeliveryAddress2;
        $payload_data["DeliveryCity"] = $order->DeliveryCity;
        $payload_data["DeliveryPostCode"] = $order->DeliveryPostCode;
        $payload_data["DeliveryCountry"] = $order->DeliveryCountry;
        $payload_data["DeliveryState"] = $order->DeliveryState;
        $payload_data["DeliveryPhone"] = $order->DeliveryPhone;

        // For charities registered for Gift Aid
        $payload_data["AllowGiftAid"] = 0;

        // 3D secure
        $payload_data["Apply3DSecure"] = 0;

        // Generate a html payload from our settings
        $payload = "";
        $i=0;

        foreach($payload_data as $key=>$value) {
            $payload .= $key . "=" . $value;

            $i++;

            if($i < count($payload_data))
                $payload .= "&";
        }

        // Write our connection and check result
        $parsed_url = parse_url($this->payment_gateway->GatewayURL());

        $host = $parsed_url['host'];
        $path = $parsed_url['path'];
        $port = 443;
        $response = "";

        $request = "POST {$path} HTTP/1.1\r\n";
        $request .= "Host:{$host}\r\n";
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: ".strlen($payload)."\r\n";
        $request .= "Connection: close\r\n\r\n";
        $request .= $payload;

        $socket = fsockopen("ssl://{$host}", $port, $errno, $errstr, 30);

        if(!$socket)
            return $this->redirect($error_url);

        for($written = 0; $written < strlen($request); $written += $fwrite) {
            $fwrite = fwrite($socket, substr($request, $written));
        }

        while(!feof($socket))
            $response .= fgets($socket,1024);

        fclose($socket);

        // Ready to deal with response data
        $response_data = array();

        foreach(explode("\n", $response) as $item) {
            if(!strpos($item, "=") === false) {
                $item_array = explode("=",$item, 2);
                $response_data[$item_array[0]] = $item_array[1];
            }
        }

        // Check our data was recieved ok
        if(strpos($response_data['Status'],'OK') === false) {
            return $this->redirect($error_url);
        } else {
            $order->PaymentID = $response_data['VPSTxId'];
            Session::set('Order',$order);

            // Finally, set the GateWay URL for the form
            $data['GatewayURL'] = $response_data['NextURL'];
        }

        return $data;
    }

    protected function gateway_fields() {
        $fields = new FieldList();

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
