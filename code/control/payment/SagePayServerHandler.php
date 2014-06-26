<?php

class SagePayServerHandler extends CommercePaymentHandler {

    /**
     * Default action
     */
    public function index() {

        $order = $this->order;
        $site = SiteConfig::current_site_config();

        // First send our intial data to sagepay to get our payment ID
        // and URL
        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            "callback",
            $this->payment_gateway->ID
        );

        $back_url = Controller::join_links(
            BASE_URL,
            Checkout_Controller::config()->url_segment,
            "finish"
        );

        $payload_data = array();

        $payload_data['VPSProtocol'] = $this->payment_gateway->ProtocolVersion;
        $payload_data['TxType'] = 'PAYMENT';
        $payload_data['Vendor'] = $this->payment_gateway->VendorName;

        // Order details
        $payload_data["VendorTxCode"] = $order->OrderNumber;
        $payload_data["Amount"] = $order->Total;
        $payload_data["Currency"] = $site->Currency()->GatewayCode;
        $payload_data["Description"] = $this->payment_gateway->GatewayMessage;
        $payload_data["NotificationURL"] = $callback_url;
        $payload_data["SuccessURL"] = $callback_url;
        $payload_data["FailureURL"] = $callback_url;
        $payload_data["CustomerName"] = $order->FirstName . " " . $order->Surname;
        $payload_data["SendEMail"] = $this->payment_gateway->SendEmail;
        $payload_data["CustomerEMail"] = $order->Email;
        $payload_data["VendorEMail"] = $this->payment_gateway->EmailRecipient;

        // Billing details
        $payload_data["BillingFirstnames"] = $order->FirstName;
        $payload_data["BillingSurname"] = $order->Surname;
        $payload_data["BillingAddress1"] = $order->Address1;
        $payload_data["BillingAddress2"] = $order->Address2;
        $payload_data["BillingCity"] = $order->City;
        $payload_data["BillingPostCode"] = $order->PostCode;
        $payload_data["BillingCountry"] = $order->Country;
        $payload_data["BillingState"] = $order->State;
        $payload_data["BillingPhone"] = $order->PhoneNumber;

        // Delivery details
        $payload_data["DeliveryFirstnames"] = $order->DeliveryFirstnames;
        $payload_data["DeliverySurname"] = $order->DeliverySurname;
        $payload_data["DeliveryAddress1"] = $order->DeliveryAddress1;
        $payload_data["DeliveryAddress2"] = $order->DeliveryAddress2;
        $payload_data["DeliveryCity"] = $order->DeliveryCity;
        $payload_data["DeliveryPostCode"] = $order->DeliveryPostCode;
        $payload_data["DeliveryCountry"] = $order->DeliveryCountry;
        $payload_data["DeliveryState"] = $order->DeliveryState;
        $payload_data["DeliveryPhone"] = $order->PhoneNumber;

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
            return null;

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
            $form = null;
        } else {
            $order->PaymentID = $response_data['VPSTxId'];
            $order->write();

            Session::set('Commerce.Order',$order);

            // now setup our form
            $actions = FieldList::create(
                LiteralField::create('BackButton','<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.Back','Back') . '</a>'),
                FormAction::create('Submit', _t('Commerce.ConfirmPay','Confirm and Pay'))
                    ->addExtraClass('btn')
                    ->addExtraClass('btn-green')
            );

            $form = Form::create($this, 'Form', FieldList::create(), $actions)
                ->addExtraClass('forms')
                ->setFormMethod('POST');

            $this->extend('updateForm',$form);
        }

        return array(
            'Title'       => _t('Commerce.CheckoutSummary',"Summary"),
            'MetaTitle'   => _t('Commerce.CheckoutSummary',"Summary"),
            "Form" => $form
        );
    }

    /**
     * Retrieve and process order data from the request
     */
    public function callback() {
        $vars = array();
        $data = $this->request->postVars();

        $success_url = Controller::join_links(
            BASE_URL,
            Payment_Controller::config()->url_segment,
            'complete'
        );

        $error_url = Controller::join_links(
            BASE_URL,
            Payment_Controller::config()->url_segment,
            'complete',
            'error'
        );

        // Check if CallBack data exists and install id matches the saved ID
        if(isset($data) && isset($data['VendorTxCode']) && isset($data['Status'])) {
            $order = Order::get()
                ->filter(array(
                    'OrderNumber' => $data['VendorTxCode'],
                    'Status' => 'incomplete'
                ))->first();

            $order_status = $data['Status'];

            if($order && trim($order->PaymentID) == trim($data['VPSTxId'])) {
                $order->Status = ($order_status == 'OK' || $order_status == 'AUTHENTICATED') ? 'paid' : 'failed';
                // Store all the data sent from the gateway in a json
                $order->GatewayData = json_encode($data);
                $order->write();

                if($order_status == 'OK' || $order_status == 'AUTHENTICATED') {
                    $vars['Status'] = "OK";
                    $vars['StatusDetail'] =  _t('Commerce.OrderComplete',"Order Complete");
                    $vars['RedirectURL'] = $success_url;
                }
            } else {
                $vars['Status'] = "INVALID";
                $vars['StatusDetail'] =  _t('Commerce.OrderError',"An error occured, Order ID's do not match");
                $vars['RedirectURL'] = $error_url;
            }
        } else {
            $vars['Status'] = "ERROR";
            $vars['StatusDetail'] =  _t('Commerce.OrderError',"An error occured, Order ID's do not match");
            $vars['RedirectURL'] = $error_url;
        }

        return $this->renderWith(array("Payment_SagePayServer"), $vars);
    }
}
