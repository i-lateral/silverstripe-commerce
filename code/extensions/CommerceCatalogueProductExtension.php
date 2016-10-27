<?php

/**
 * Update a Product to add stock levels and pack size and weight fields
 *
 * @author ilateral (http://ilateralweb.co.uk)
 * @package commerce
 */
class CommerceCatalogueProductExtension extends DataExtension {
    private static $db = array(
        "StockLevel" => "Int",
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

        $fields->addFieldsToTab(
            "Root.Settings",
            array(
                NumericField::create(
                    "StockLevel",
                    $this->owner->FieldLabel("StockLevel")
                ),
                NumericField::create(
                    'PackSize',
                    $this->owner->FieldLabel("PackSize")
                ),
                NumericField::create(
                    'Weight',
                    $this->owner->FieldLabel("Weight")
                )
            ),
            "TaxRateID"
        );
    }
}
