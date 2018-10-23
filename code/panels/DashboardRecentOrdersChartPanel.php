<?php

class DashboardRecentOrdersChartPanel extends DashboardPanel
{

    private static $icon = "commerce/images/order_162.png";

    private static $defaults = array (
        'PanelSize' => "large"
    );

    public function getLabel()
    {
        return _t('Commerce.RecentOrdersChart', 'Recent Orders Chart');
    }

    public function getDescription()
    {
        return _t('Commerce.RecentOrdersChartDescription', 'Shows a chart of the last months orders.');
    }

    public function Chart()
    {
        $chart = DashboardChart::create(
            "Last 30 days orders",
            "Date",
            "Number of orders"
        );

        $results = ArrayList::create();
        $status = Order::config()->incomplete_status;

        // Get results for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = new DateTime();

            if ($i > 0) {
                $date->modify("-{$i} day");
            }

            $orders = Order::get()
                ->filter(
                    array(
                        "Created:PartialMatch" => $date->format('Y-m-d'),
                        "ClassName" => "Order",
                        "Status:not" => $status
                    )
                )->count();
            
            $results->add(
                ArrayData::create(
                    array(
                        "Date"  => $date->format('jS F Y'),
                        "Count" => $orders
                    )
                )
            );
        }

        // Reverse the data
        $results = $results->reverse();

        foreach ($results as $result) {
            $chart->addData($result->Date, $result->Count);
        }
        
        return $chart;
    }
}