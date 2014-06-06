<?php

class SagePayFormsHandler extends CommercePaymentHandler {

    /**
     * Default Action
     *
     */
    public function index() {
        return $this->
            customise(array(
                'Title'       => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
                'MetaTitle'   => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            ))->renderWith(array(
                "Payment",
                "Commerce",
                "Page"
            ));
    }

    /**
     * Return a form that will be loaded into the Payment template and will post
     * to the payment gateway provider.
     *
     * @return Form
     */
    public function GatewayForm() {
        $back_url = Controller::join_links(
            BASE_URL,
            Checkout_Controller::config()->url_segment,
            "finish"
        );


        $fields = FieldList::create(
            HiddenField::create('navigate'),
            HiddenField::create('VPSProtocol',null,$this->payment_gateway->ProtocolVersion),
            HiddenField::create('TxType', null, 'PAYMENT'),
            HiddenField::create('Vendor', null, $this->payment_gateway->VendorName),
            HiddenField::create('Crypt', null, $this->gateway_data())
        );

        $actions = FieldList::create(
            LiteralField::create('BackButton','<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'),
            FormAction::create('Submit', _t('Commerce.CONFIRMPAY','Confirm and Pay'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-green')
        );

        $form = Form::create($this, 'GatewayForm', $fields, $actions)
            ->addExtraClass('forms')
            ->setFormMethod('POST')
            ->setFormAction($this->payment_gateway->GatewayURL());

        $this->extend('updateGatewayForm',$form);

        return $form;
    }

    /**
     * Generate encrypted string to send to SagePay
     *
     */
    private function gateway_data() {
        $order = $this->order;
        $site = SiteConfig::current_site_config();

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            "callback",
            $this->payment_gateway->ID
        );

        $post = array(
            "VendorTxCode" => $order->OrderNumber,
            "Amount" => $order->Total,
            "Currency" => $site->Currency()->GatewayCode,
            "Description" => $this->payment_gateway->GatewayMessage,
            "SuccessURL" => $callback_url,
            "FailureURL" => $callback_url,
            "CustomerName" => $order->FirstName . " " . $order->Surname,

            // Email settings:
            "SendEMail" => $this->payment_gateway->SendEmail,

            // Billing Details:
            "BillingFirstnames" => $order->FirstName,
            "BillingSurname" => $order->Surname,
            "BillingAddress1" => $order->Address1,
            "BillingAddress2" => $order->Address2,
            "BillingCity" => $order->City,
            "BillingPostCode" => $order->PostCode,
            "BillingCountry" => $order->Country,

            // Delivery Details:
            "DeliveryFirstnames"=> $order->DeliveryFirstnames,
            "DeliverySurname" => $order->DeliverySurname,
            "DeliveryAddress1" => $order->DeliveryAddress1,
            "DeliveryAddress2" => $order->DeliveryAddress2,
            "DeliveryCity" => $order->DeliveryCity,
            "DeliveryPostCode" => $order->DeliveryPostCode,
            "DeliveryCountry" => $order->DeliveryCountry,

            // Additional
            "AllowGiftAid" => 0,
            "Apply3DSecure" => 0
        );

        // Add non required elements
        if($order->Email) $post["CustomerEMail"] = $order->Email;

        if($this->payment_gateway->EmailRecipient) $post["VendorEMail"] = $this->payment_gateway->EmailRecipient;

        if($order->State) $post["BillingState"] = $order->State;
        if($order->PhoneNumber) $post["BillingPhone"] = $order->PhoneNumber;


        if($order->DeliveryState) $post["DeliveryState"] = $order->DeliveryState;
        if($order->DeliveryPhone) $post["DeliveryPhone"] = $order->DeliveryPhone;

        $result = "";

        foreach ($post as $key => $value) {
            $result .= $key . "=" . $value . '&';
        }

        // Encrypt the plaintext string for inclusion in the hidden field
        $encrypted_data = StringEncryptor::create($result)
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
            Payment_Controller::config()->url_segment,
            'complete'
        );

        $error_url = Controller::join_links(
            Director::BaseURL(),
            Payment_Controller::config()->url_segment,
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
