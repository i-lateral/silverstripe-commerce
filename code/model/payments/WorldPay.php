<?php

class WorldPay extends CommercePaymentMethod {

    public $Title = 'WorldPay';
    
    public $Summary = "Pay with credit/debit card securely via WorldPay";

    public static $db = array();
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        return $fields;
    }
}
