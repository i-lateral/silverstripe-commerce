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

    /**
     * Get a link to the catalogue admin
     *
     * @return string
     */
    public function CatalogueLink()
    {
        return Injector::inst()->create("CatalogueAdmin")->Link();
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

    /**
     * Add view all button to actions
     *
     * @return ArrayList
     */
    public function getSecondaryActions()
    {
		$actions = parent::getSecondaryActions();
		$actions->push(DashboardPanelAction::create(
            $this->CatalogueLink(),
            _t("Commerce.ViewAll", "View All")
        ));
			
		return $actions;
	}

    /**
     * Get a list of products to render in the template
     *
     * @return DataList
     */
    public function Products()
    {
        $count = ($this->Count) ? $this->Count : 7;
        
        return Product::get()
            ->filter("StockLevel:LessThan", Commerce::config()->low_stock_number)
            ->sort("StockLevel", "ASC")
            ->limit($count);
    }
}