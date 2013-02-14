<?php

class SagePay extends CommercePaymentMethod {
    public $Title = 'SagePay';

    public static $db = array(
        'SendEmail'         => "Enum('0,1,2','1')",
        'EmailRecipient'    => 'Varchar(100)',
        'VendorName'        => 'Varchar(100)',
        'EncryptedPassword' => 'Varchar(100)'
    );
    
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        if($this->ID) {
            // Payment Gateway Options
            $email_options = array(
                "Don't",
                'Send to customer and vendor',
                'Send only to vendor'
            );
            
            $fields->addFieldToTab('Root.Main', TextField::create('VendorName', 'Vendor name'));
            $fields->addFieldToTab('Root.Main', PasswordField::create('EncryptedPassword', 'Password'));
            
            $fields->addFieldToTab('Root.Main', OptionsetField::create('SendEmail', 'How would you like SagePay to send emails?', $email_options));
		    $fields->addFieldToTab('Root.Main', EmailField::create('EmailRecipient','Email address of user to recieve email'));
        }
        
        return $fields;
    }
    
    
    public function onBeforeWrite() {
        parent::onBeforeWrite();     
        
        $this->CallBackSlug = (!$this->CallBackSlug) ? 'sagepay' : $this->CallBackSlug;
    
        if(!$this->Summary)
            $this->Summary = "Pay with credit/debit card securely via SagePay";
            
        if(!$this->GatewayMessage)
            $this->GatewayMessage = "Thank you for your order from: " . SiteConfig::current_site_config()->Title;
    }
    
    
    public function getGatewayFields() {
        $fields = new FieldList(
            HiddenField::create('navigate'),
            HiddenField::create('VPSProtocol',null,'2.23'),
            HiddenField::create('TxType', null, 'PAYMENT'),
            HiddenField::create('Vendor', null, $this->VendorName),
            HiddenField::create('Crypt', null, $this->GatewayData())
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
            // Now decode the Crypt field and extract the results
            $crypt_decoded = $this->decodeAndDecrypt($data['crypt']);            
            $values = $this->getToken($crypt_decoded);
            
            $order = Order::get()->filter('OrderNumber',$values['VendorTxCode'])->first();
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
    
    
    public function GatewayData() {
        $order = Session::get('Order');
        $site = SiteConfig::current_site_config();
        $strPost = "";
        
        // Now to build the Form crypt field.  For more details see the Form Protocol 2.23 
        $strPost .= "VendorTxCode=" . $order->OrderNumber; /** As generated above **/

        $strPost .= "&Amount=" . $order->getOrderTotal(); // Formatted to 2 decimal places with leading digit
        $strPost .= "&Currency=" . $site->Currency()->GatewayCode;
        // Up to 100 chars of free format description
        $strPost .= "&Description=" . $this->GatewayMessage;

        /* The SuccessURL is the page to which Form returns the customer if the transaction is successful 
        ** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
        $strPost .= "&SuccessURL=" . Director::absoluteBaseURL() . Payment_Controller::$url_segment . "/callback/sagepay";

        /* The FailureURL is the page to which Form returns the customer if the transaction is unsuccessful
        ** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
        $strPost .= "&FailureURL=" . Director::absoluteBaseURL() . Payment_Controller::$url_segment . "/callback/sagepay";

        // This is an Optional setting. Here we are just using the Billing names given.
        $strPost .= "&CustomerName=" . $order->BillingFirstnames . " " . $order->BillingSurname;

        // Email settings:
        $strPost=$strPost . "&SendEMail=" . $this->SendEmail;

        if($order->BillingEmail)
            $strPost .= "&CustomerEMail=" . $order->BillingEmail;  // This is an Optional setting

        if($this->EmailRecipient)
            $strPost .= "&VendorEMail=" . $this->EmailRecipient;  // This is an Optional setting

        // You can specify any custom message to send to your customers in their confirmation e-mail here
        // The field can contain HTML if you wish, and be different for each order.  This field is optional
        //$strPost .= "&eMailMessage=Thank you for your order from {$site->Title}.<br/> For your records, your order number is:<br/>" . $order->OrderNumber;

        // Billing Details:
        $strPost .= "&BillingFirstnames=" . $order->BillingFirstnames;
        $strPost .= "&BillingSurname=" . $order->BillingSurname;
        $strPost .= "&BillingAddress1=" . $order->BillingAddress1;
        if (strlen($order->BillingAddress2) > 0) $strPost .= "&BillingAddress2=" . $order->BillingAddress2;
        $strPost .= "&BillingCity=" . $order->BillingCity;
        $strPost .= "&BillingPostCode=" . $order->BillingPostCode;
        $strPost .= "&BillingCountry=" . $order->BillingCountry;
        if (strlen($order->BillingState) > 0) $strPost .= "&BillingState=" . $order->BillingState;
        if (strlen($order->BillingPhone) > 0) $strPost .= "&BillingPhone=" . $order->BillingPhone;

        // Delivery Details:
        $strPost .= "&DeliveryFirstnames=" . $order->DeliveryFirstnames;
        $strPost .= "&DeliverySurname=" . $order->DeliverySurname;
        $strPost .= "&DeliveryAddress1=" . $order->DeliveryAddress1;
        if (strlen($order->DeliveryAddress2) > 0) $order->Post .= "&DeliveryAddress2=" . $order->DeliveryAddress2;
        $strPost .= "&DeliveryCity=" . $order->DeliveryCity;
        $strPost .= "&DeliveryPostCode=" . $order->DeliveryPostCode;
        $strPost .= "&DeliveryCountry=" . $order->DeliveryCountry;
        if (strlen($order->DeliveryState) > 0) $strPost .= "&DeliveryState=" . $order->DeliveryState;
        if (strlen($order->DeliveryPhone) > 0) $strPost .= "&DeliveryPhone=" . $order->DeliveryPhone;


        //$strPost .= "&Basket=" . $strBasket; // As created above 

        // For charities registered for Gift Aid, set to 1 to display the Gift Aid check box on the payment pages
        $strPost .= "&AllowGiftAid=0";

        /* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default 
        ** It can be changed dynamically, per transaction, if you wish.  See the Form Protocol document */
        $strPost .= "&Apply3DSecure=0";

        // Encrypt the plaintext string for inclusion in the hidden field
        $encrypted_data = $this->encryptAndEncode($strPost);
        
        // Send back variables to be rendered by the controller
        return $encrypted_data;
    }
    
    private function encryptAndEncode($strIn, $type = 'AES') {	
        if($type=="XOR") {
            //** XOR encryption with Base64 encoding **
            return base64Encode(simpleXor($strIn,$this->EncryptedPassword));
        } else {
            //** AES encryption, CBC blocking with PKCS5 padding then HEX encoding - DEFAULT **
            //** use initialization vector (IV) set from $strEncryptionPassword
            $strIV = $this->EncryptedPassword;
            //** add PKCS5 padding to the text to be encypted
            $strIn = $this->addPKCS5Padding($strIn);

            //** perform encryption with PHP's MCRYPT module
            $strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->EncryptedPassword, $strIn, MCRYPT_MODE_CBC, $strIV);

            //** perform hex encoding and return
            return "@" . bin2hex($strCrypt);
        }
    }
    
    private function decodeAndDecrypt($strIn) {
	    if (substr($strIn,0,1)=="@") {
		    //** HEX decoding then AES decryption, CBC blocking with PKCS5 padding - DEFAULT **
		    //** use initialization vector (IV) set from $strEncryptionPassword
        	$strIV = $this->EncryptedPassword;
        	//** remove the first char which is @ to flag this is AES encrypted
        	$strIn = substr($strIn,1); 
        	
        	//** HEX decoding
        	$strIn = pack('H*', $strIn);
        	
        	//** perform decryption with PHP's MCRYPT module
		    return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->EncryptedPassword, $strIn, MCRYPT_MODE_CBC, $strIV); 
	    } else {
		    //** Base 64 decoding plus XOR decryption **
		    return simpleXor(base64Decode($strIn),$strEncryptionPassword);
	    }
    }
    
    //** PHP's mcrypt does not have built in PKCS5 Padding, so we use this
    private function addPKCS5Padding($input) {
       $blocksize = 16;
       $padding = "";

       // Pad input to an even block size boundary
       $padlength = $blocksize - (strlen($input) % $blocksize);
       for($i = 1; $i <= $padlength; $i++) {
          $padding .= chr($padlength);
       }

       return $input . $padding;
    }
    
    /* The getToken function.                                                                                         **
    ** NOTE: A function of convenience that extracts the value from the "name=value&name2=value2..." reply string **
    ** Works even if one of the values is a URL containing the & or = signs.                                      	  */
    private function getToken($thisString) {
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
}
