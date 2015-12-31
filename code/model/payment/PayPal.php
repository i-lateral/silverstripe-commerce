<?php

class PayPal extends CommercePaymentMethod
{

    public static $handler = "PayPalHandler";

    public $Title = 'PayPal';

    public $Icon = 'commerce/images/paypal-small.png';

    private static $db = array(
        'BusinessID' => 'Varchar(99)'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->ID) {
            $fields->removeByName("URL");

            $fields->addFieldToTab(
                "Root.Main",
                TextField::create('BusinessID', 'Business ID'),
                "Summary"
            );
        }

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->CallBackSlug = (!$this->CallBackSlug) ? 'paypal' : $this->CallBackSlug;

        $this->Summary = (!$this->Summary) ? "Pay with PayPal" : $this->Summary;
    }
}
