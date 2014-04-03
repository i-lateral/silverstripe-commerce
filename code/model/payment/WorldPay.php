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
            $callback_url = Controller::join_links(
                Director::absoluteBaseURL(),
                Commerce_Payment_Controller::$url_segment,
                "callback",
                $this->ID
            );

            $fields->addFieldToTab(
                'Root.Main',
                ReadonlyField::create('ResponseURL', 'Payment Response URL')
                    ->setValue($callback_url)
            );

            $fields->addFieldToTab(
                'Root.Main',
                TextField::create('InstallID', 'Instalation ID')
            );

            $fields->addFieldToTab(
                'Root.Main',
                TextField::create('ResponsePassword', 'Payment Response Password)')
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
