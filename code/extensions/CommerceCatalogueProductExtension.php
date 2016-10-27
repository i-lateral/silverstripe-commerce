<?php

/**
 * Update a Product to add stock levels and pack size and weight fields
 *
 * @author ilateral (http://ilateralweb.co.uk)
 * @package commerce
 */
class CommerceCatalogueProductExtension extends DataExtension
{
    
    private static $db = array(
        "StockLevel" => "Int",
        "PackSize" => "Int",
        "Weight" => "Decimal"
    );

    private static $summary_fields = array(
        "CMSThumbnail"  => "Thumbnail",
        "ClassName"     => "Product",
        "StockID"       => "StockID",
        "Title"         => "Title",
        "BasePrice"     => "Price",
        "TaxRate.Amount"=> "Tax Percent",
        "CategoriesList"=> "Categories",
        "StockLevel"    => "Stock",
        "Disabled"      => "Disabled"
    );


    /**
     * Reset summary fields to new default
     *
     */
    public function updateSummaryFields(&$fields) {
        $fields = Config::inst()->get($this->class, 'summary_fields');
    }
    
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
