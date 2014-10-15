<?php

class CommerceCatalogueProductExtension extends DataExtension {
    private static $db = array(
        "PackSize" => "Int",
        "Weight" => "Decimal"
    );

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
