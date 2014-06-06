<?php

class WorldPay extends CommercePaymentMethod {

    public static $handler = "WorldPayHandler";

    public $Title = 'WorldPay';

    public $Icon = 'commerce/images/worldpay-small.png';

    private static $db = array(
        'InstallID' => 'Varchar(10)',
        'ResponsePassword' => 'Varchar(10)'
    );

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        if($this->ID) {
            $fields->addFieldToTab(
                "Root.Main",
                TextField::create('InstallID', 'Instalation ID'),
                "Summary"
            );

            $fields->addFieldToTab(
                "Root.Main",
                PasswordField::create("ResponsePassword", "Payment Response Password"),
                "Summary"
            );
        }

        return $fields;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        $this->CallBackSlug = (!$this->CallBackSlug) ? 'worldpay' : $this->CallBackSlug;

        if(!$this->Summary)
            $this->Summary = "Pay with credit/debit card securely via WorldPay";
    }
}
