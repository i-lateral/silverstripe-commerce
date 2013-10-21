<?php

class SagePay extends CommercePaymentMethod {

    public static $hidden = true;

    public $Title = 'SagePay';

    public static $db = array(
        'SendEmail'         => "Enum('0,1,2','1')",
        'EmailRecipient'    => 'Varchar(100)',
        'VendorName'        => 'Varchar(100)',
        'ProtocolVersion'   => "Enum('2.23,3','3')",
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
            $fields->addFieldToTab('Root.Main', DropdownField::create('ProtocolVersion', 'Version of forms protocol to use?', singleton('SagePay')->dbObject('ProtocolVersion')->enumValues()));
            $fields->addFieldToTab('Root.Main', PasswordField::create('EncryptedPassword', 'Password'));

            $fields->addFieldToTab('Root.Main', OptionsetField::create('SendEmail', 'How would you like SagePay to send emails?', $email_options));
            $fields->addFieldToTab('Root.Main', EmailField::create('EmailRecipient','Email address of user to recieve email'));
        }

        return $fields;
    }


    public function onBeforeWrite() {
        parent::onBeforeWrite();

        $this->CallBackSlug = (!$this->CallBackSlug) ? 'sagepay' : Convert::raw2url($this->CallBackSlug);

        if(!$this->Summary)
            $this->Summary = "Pay with credit/debit card securely via SagePay";

        if(!$this->GatewayMessage)
            $this->GatewayMessage = "Thank you for your order from: " . SiteConfig::current_site_config()->Title;
    }

}

class SagePayForms extends SagePay {

    public static $hidden = false;

    public static $handler = "SagePayFormsHandler";

    public $Title = 'SagePay Forms Integration';
}

class SagePayServer extends SagePay {

    public static $hidden = false;

    public static $handler = "SagePayServerHandler";

    public $Title = 'SagePay Server Integration';
}
