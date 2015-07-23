<?php

class CommerceCatalogueProductExtension extends DataExtension {
    private static $db = array(
        "PackSize" => "Int",
        "Weight" => "Decimal"
    );
    
    /**
     * Get a list of suitable shipping bands
     * 
     */
    public function Shipping() {
        // Set a wildcard for all shipping
        $shipping = new ShippingCalculator("*");
        $shipping
            ->setCost($this->owner->Price)
            ->setWeight($this->owner->Weight);
            
        return $shipping->getPostageAreas();
    }
    
    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab(
            "Root.Settings",
            NumericField::create('PackSize', $this->owner->FieldLabel("PackSize")),
            "TaxRateID"
        );
        
        $fields->addFieldToTab(
            "Root.Settings",
            NumericField::create('Weight', $this->owner->FieldLabel("Weight")),
            "TaxRateID"
        );
    }
}
