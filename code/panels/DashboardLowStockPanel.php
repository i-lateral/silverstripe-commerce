<?php

class DashboardLowStockPanel extends DashboardPanel
{

    private static $db = array (
        'Count' => 'Int'
    );

	private static $defaults = array (
		'Count' => "5",
		'PanelSize' => "small"
	);

    private static $icon = "commerce/images/warning.png";

    public function getLabel()
    {
        return _t('Commerce.LowStock','Low Stock');
    }

    public function getDescription()
    {
        return _t('Commerce.LowStockDescription','List of low stock products.');
    }

    public function PanelHolder()
    {
        Requirements::css("commerce/css/dashboard-commerce.css");
        return parent::PanelHolder();
    }

    public function getConfiguration()
    {
        $fields = parent::getConfiguration();

        $fields->push(TextField::create(
        "Count",
        "Number of products to show"
        ));

        return $fields;
    }

    public function CatalogueLink()
    {
        return CatalogueAdmin::create()->Link();
    }

    public function Products()
    {
        $count = ($this->Count) ? $this->Count : 7;
        
        return Product::get()
            ->filter("StockLevel:LessThan", Commerce::config()->low_stock_number)
            ->sort("StockLevel", "ASC")
            ->limit($count);
    }
}