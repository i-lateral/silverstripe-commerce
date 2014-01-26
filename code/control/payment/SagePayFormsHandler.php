<?php

class SagePayFormsHandler extends CommercePaymentHandler {

    protected function gateway_fields() {
        $fields = new FieldList(
            HiddenField::create('navigate'),
            HiddenField::create('VPSProtocol',null,$this->payment_gateway->ProtocolVersion),
            HiddenField::create('TxType', null, 'PAYMENT'),
            HiddenField::create('Vendor', null, $this->payment_gateway->VendorName),
            HiddenField::create('Crypt', null, $this->gateway_data())
        );

        return $fields;
    }

    /**
     * Generate encrypted string to send to SagePay
     *
     */
    private function gateway_data() {
        $order = Session::get('Order');
        $site = SiteConfig::current_site_config();

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Commerce_Payment_Controller::$url_segment,
            "callback",
            $this->payment_gateway->ID
        );

        $strPost = "VendorTxCode=" . $order->OrderNumber;
        $strPost .= "&Amount=" . $order->Total->Value;
        $strPost .= "&Currency=" . $site->Currency()->GatewayCode;
        $strPost .= "&Description=" . $this->payment_gateway->GatewayMessage;
        $strPost .= "&SuccessURL=" . $callback_url;
        $strPost .= "&FailureURL=" . $callback_url;
        $strPost .= "&CustomerName=" . $order->FirstName . " " . $order->Surname;

        // Email settings:
        $strPost .= "&SendEMail=" . $this->payment_gateway->SendEmail;

        if($order->BillingEmail)
            $strPost .= "&CustomerEMail=" . $order->Email;

        if($this->payment_gateway->EmailRecipient)
            $strPost .= "&VendorEMail=" . $this->payment_gateway->EmailRecipient;

        $strPost .= "&BillingFirstnames=" . $order->BillingFirstnames;
        $strPost .= "&BillingSurname=" . $order->BillingSurname;
        $strPost .= "&BillingAddress1=" . $order->BillingAddress1;
        $strPost .= "&BillingAddress2=" . $order->BillingAddress2;
        $strPost .= "&BillingCity=" . $order->BillingCity;
        $strPost .= "&BillingPostCode=" . $order->BillingPostCode;
        $strPost .= "&BillingCountry=" . $order->BillingCountry;

        if (strlen($order->BillingState) > 0) $strPost .= "&BillingState=" . $order->BillingState;
        if (strlen($order->BillingPhone) > 0) $strPost .= "&BillingPhone=" . $order->BillingPhone;

        // Delivery Details:
        $strPost .= "&DeliveryFirstnames=" . $order->DeliveryFirstnames;
        $strPost .= "&DeliverySurname=" . $order->DeliverySurname;
        $strPost .= "&DeliveryAddress1=" . $order->DeliveryAddress1;
        $strPost .= "&DeliveryAddress2=" . $order->DeliveryAddress2;
        $strPost .= "&DeliveryCity=" . $order->DeliveryCity;
        $strPost .= "&DeliveryPostCode=" . $order->DeliveryPostCode;
        $strPost .= "&DeliveryCountry=" . $order->DeliveryCountry;

        if (strlen($order->DeliveryState) > 0) $strPost .= "&DeliveryState=" . $order->DeliveryState;
        if (strlen($order->DeliveryPhone) > 0) $strPost .= "&DeliveryPhone=" . $order->DeliveryPhone;

        $strPost .= "&AllowGiftAid=0";
        $strPost .= "&Apply3DSecure=0";

        // Encrypt the plaintext string for inclusion in the hidden field
        $encrypted_data = StringEncryptor::create($strPost)
            ->setHash($this->payment_gateway->EncryptedPassword)
            ->setEncryption('MCRYPT')
            ->encrypt()
            ->get();

        // Send back variables to be rendered by the controller
        return '@' . $encrypted_data;
    }


    /*
     * A function of convenience that extracts the value from the
     * "name=value&name2=value2..." reply string
     * Works even if one of the values is a URL containing the & or = signs.
     *
     * @param thisString string to convert
     * @return array of values
     */
    private function get_token($thisString) {
        // List the possible tokens
        $Tokens = array(
            "Status",
            "StatusDetail",
            "VendorTxCode",
            "VPSTxId",
            "TxAuthNo",
            "Amount",
            "AVSCV2",
            "AddressResult",
            "PostCodeResult",
            "CV2Result",
            "GiftAid",
            "3DSecureStatus",
            "CAVV",
            "AddressStatus",
            "CardType",
            "Last4Digits",
            "PayerStatus"
        );

        // Initialise arrays
        $output = array();
        $resultArray = array();

        // Get the next token in the sequence
        for ($i = count($Tokens)-1; $i >= 0 ; $i--){
            // Find the position in the string
            $start = strpos($thisString, $Tokens[$i]);

            // If it's present
            if ($start !== false){
                // Record position and token name
                $resultArray[$i]['start'] = $start;
                $resultArray[$i]['token'] = $Tokens[$i];
            }
        }

        // Sort in order of position
        sort($resultArray);
        // Go through the result array, getting the token values
        for ($i = 0; $i<count($resultArray); $i++){
            // Get the start point of the value
            $valueStart = $resultArray[$i]['start'] + strlen($resultArray[$i]['token']) + 1;
            // Get the length of the value
            if ($i==(count($resultArray)-1)) {
                $output[$resultArray[$i]['token']] = substr($thisString, $valueStart);
            } else {
                $valueLength = $resultArray[$i+1]['start'] - $resultArray[$i]['start'] - strlen($resultArray[$i]['token']) - 2;
                $output[$resultArray[$i]['token']] = substr($thisString, $valueStart, $valueLength);
            }
        }

        // Return the ouput array
        return $output;
    }

    /**
     * Retrieve and process order data from the request
     *
     * @var $data request data
     * @var $success_data initial success vars
     * @var $error_data initial success vars
     */
    public function ProcessCallback($data = null, $success_data, $error_data) {
        $successs_url = Controller::join_links(
            Director::BaseURL(),
            Commerce_Payment_Controller::$url_segment,
            'complete'
        );

        $error_url = Controller::join_links(
            Director::BaseURL(),
            Commerce_Payment_Controller::$url_segment,
            'complete',
            'error'
        );

        $controller = Controller::curr();

        // Check if CallBack data exists and install id matches the saved ID
        if(isset($data) && isset($data['crypt'])) {
            // Clear Sagepay '@' symbol (denotes encrypted data)
            if(substr($data['crypt'],0,1) == "@")
                $data['crypt'] = substr($data['crypt'], 1);

            // Now decode the Crypt field and extract the results
            $crypt_decoded = StringDecryptor::create($data['crypt'])
                ->setHash($this->payment_gateway->EncryptedPassword)
                ->setEncryption('MCRYPT')
                ->decrypt()
                ->get();

            $values = $this->get_token($crypt_decoded);

            $order = Order::get()
                ->filter(array(
                    'OrderNumber' => $values['VendorTxCode'],
                    'Status' => 'incomplete'
                ))->first();

            $order_status = $values['Status'];

            if($order) {
                $order->Status = ($order_status == 'OK' || $order_status == 'AUTHENTICATED') ? 'paid' : 'failed';
                $order->PaymentID = $values['VPSTxId'];
                // Store all the data sent from the gateway in a json
                $order->GatewayData = json_encode($values);
                $order->write();

                if($order_status == 'OK' || $order_status == 'AUTHENTICATED')
                    return $controller->redirect($successs_url);
                else
                    return $controller->redirect($error_url);
            } else
                return $controller->redirect($error_url);
        }

        return $controller->redirect($error_url);
    }
}
