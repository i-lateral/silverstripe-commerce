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
        "Stocked" => "Boolean",
        "StockLevel" => "Int",
        "PackSize" => "Int",
        "Weight" => "Decimal"
    );

    private static $defaults = array(
        "Stocked" => true,
        "StockLevel" => 10,
        "PackSize" => 1
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
                CheckboxField::create(
                    "Stocked",
                    $this->owner->FieldLabel("Stocked")
                ),
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

    public function getModelForm()
    {
        $controller = $this->owner->getController();

        $form = $controller->Form();
        $form->setAttribute('id',$form->FormName().'_'.$this->owner->ID);

        $fields = $form->Fields();
        $fields->removeByName('Quantity');
        $fields->push(
            HiddenField::create('Quantity')
                ->setValue('1')
                ->setForm($form)
        );

        foreach ($form->Actions() as $action) {
            $action->addExtraClass('btn-block');
        }

        return $form;
    }

    public function getController()
    {
        $ancestry = ClassInfo::ancestry($this->owner->class);
        
        while ($class = array_pop($ancestry)) {
            if (class_exists($class . "_Controller")) {
                break;
            }
        }
        
        // Find the controller we need, or revert to a default
        if ($class !== null) {
            $controller = "{$class}_Controller";
        } elseif (ClassInfo::baseDataClass($this->owner->class) == "CatalogueProduct") {
            $controller = "CatalogueProductController";
        } elseif (ClassInfo::baseDataClass($this->owner->class) == "CatalogueCategory") {
            $controller = "CatalogueCategoryController";
        }

        return class_exists($controller) ? Injector::inst()->create($controller, $this->owner) : $this->owner;
        
    }
}
