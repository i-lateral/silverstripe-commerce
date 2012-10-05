<?php
/**
 * Summary Controller is responsible for displaying all order data before posting
 * to the final payment gateway.
 *
 * @author morven
 */

class Payment_Controller extends Page_Controller {
    public static $url_segment = "payment";
    
    static $allowed_actions = array(
        'index',
        'reponse'
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
        return true;
    }


    private function encryptAndEncode($strIn, $type = 'AES') {
        $site = SiteConfig::current_site_config();
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
