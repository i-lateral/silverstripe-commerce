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
        return _t('Commerce.RecentOrdersList', 'Recent Orders List');
    }

    public function getDescription()
    {
        return _t('Commerce.RecentOrdersListDescription', 'Shows a list of recent orders.');
    }

    /**
     * Generate a link to the order admin controller
     *
     * @return String
     */
    public function Orderslink()
    {
        return Injector::inst()->create("OrderAdmin")->Link();
    }

    public function PanelHolder()
    {
        Requirements::css("commerce/css/dashboard-commerce.css");
        return parent::PanelHolder();
    }

    public function getConfiguration()
    {
        $fields = parent::getConfiguration();

        $fields->push(
            TextField::create(
                "Count",
                "Number of orders to show"
            )
        );

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
        $actions->push(
            DashboardPanelAction::create(
                $this->OrdersLink(),
                _t("Commerce.ViewAll", "View All")
            )
        );
            
        return $actions;
    }

    /**
     * Return a full list of orders for the template
     *
     * @return DataList
     */
    public function Orders()
    {
        $count = ($this->Count) ? $this->Count : 7;
        $status = Order::config()->incomplete_status;

        return Order::get()
            ->filter(
                array(
                    "ClassName" => "Order",
                    "Status:not" => $status
                )
            )->sort("Created DESC")
            ->limit($count);
    }
}