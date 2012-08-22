<?php
/**
 * Summary Controller is responsible for displaying all order data before posting
 * to the final payment gateway.
 *
 * @author morven
 */

class Summary_Controller extends Page_Controller {
    public static $url_segment = "summary";
    
    static $allowed_actions = array(
        'index'
    );
    
    public function init() {
        parent::init();
        
        // If current order has not been set, re-direct to homepage
        if(!Session::get('Order')) 
            Director::redirect(BASE_URL);
    }
    
    public function index() {
        return $this->renderWith(array('Summary','Page'));
    }
    
    public function getOrder() {
        return Session::get('Order');
    }
    
    public function getCryptData() {
        $order = $this->getOrder();
        $site = Subsite::currentSubsite();
        $strPost = "";
        
        // Now to build the Form crypt field.  For more details see the Form Protocol 2.23 
        $strPost .= "VendorTxCode=" . $order->OrderNumber; /** As generated above **/

        $strPost .= "&Amount=" . $order->getOrderTotal(); // Formatted to 2 decimal places with leading digit
        $strPost .= "&Currency=" . $site->Currency()->GatewayCode;
        // Up to 100 chars of free format description
        $strPost .= "&Description=" . $site->GatewayMessage;

        /* The SuccessURL is the page to which Form returns the customer if the transaction is successful 
        ** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
        $strPost .= "&SuccessURL=" . Director::absoluteBaseURL() . "orderresponse/success/" . $order->OrderNumber;

        /* The FailureURL is the page to which Form returns the customer if the transaction is unsuccessful
        ** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
        $strPost .= "&FailureURL=" . Director::absoluteBaseURL() . "orderresponse/failer/" . $order->OrderNumber;

        // This is an Optional setting. Here we are just using the Billing names given.
        $strPost .= "&CustomerName=" . $order->BillingFirstnames . " " . $order->BillingSurname;

        // Email settings:
        $strPost=$strPost . "&SendEMail=" . $site->SagePaySendEmail;

        if($order->BillingEmail)
            $strPost .= "&CustomerEMail=" . $order->BillingEmail;  // This is an Optional setting

        if($site->SagePayEmail)
            $strPost .= "&VendorEMail=" . $site->SagePayEmail;  // This is an Optional setting

        // You can specify any custom message to send to your customers in their confirmation e-mail here
        // The field can contain HTML if you wish, and be different for each order.  This field is optional
        $strPost .= "&eMailMessage=Thank you for your order from {$site->Title}.<br/> For your records, your order number is:<br/>" . $order->OrderNumber;

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
        
        return $encrypted_data;
    } 


    private function encryptAndEncode($strIn, $type = 'AES') {
        $site = Subsite::currentSubsite();
	$encyption_password = $site->SagePayPass;
	
	if ($type=="XOR") {
            //** XOR encryption with Base64 encoding **
            return base64Encode(simpleXor($strIn,$encyption_password));
        }
	else {
            //** AES encryption, CBC blocking with PKCS5 padding then HEX encoding - DEFAULT **
            //** use initialization vector (IV) set from $strEncryptionPassword
            $strIV = $encyption_password;
            //** add PKCS5 padding to the text to be encypted
            $strIn = $this->addPKCS5Padding($strIn);

            //** perform encryption with PHP's MCRYPT module
            $strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encyption_password, $strIn, MCRYPT_MODE_CBC, $strIV);

            //** perform hex encoding and return
            return "@" . bin2hex($strCrypt);
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
}
