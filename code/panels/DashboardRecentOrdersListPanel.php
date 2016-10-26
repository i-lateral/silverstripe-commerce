<?php

class DashboardRecentOrdersListPanel extends DashboardPanel
{

    private static $db = array (
        'Count' => 'Int'
    );

	private static $defaults = array (
		'PanelSize' => "normal"
	);

    private static $icon = "commerce/images/order_162.png";

    public function getLabel()
    {
        return _t('Commerce.RecentOrdersList','Recent Orders List');
    }

    public function getDescription()
    {
        return _t('Commerce.RecentOrdersListDescription','Shows a list of recent orders.');
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
        "Number of orders to show"
        ));

        return $fields;
    }

    public function OrdersLink()
    {
        return OrderAdmin::create()->Link();
    }

    public function Orders()
    {
        $count = ($this->Count) ? $this->Count : 7;

        return Order::get()
        ->sort("Created DESC")
        ->limit($count);
    }
}