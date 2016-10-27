<?php

/*
 * Convert all orders that are marked as "incomplete" to instead be estimates
 *
 * @author ilateral (http://www.ilateral.co.uk)
 * @package commerce
 * @subpackage tasks
 */
class ConvertIncompleteOrdersToEstimateTask extends BuildTask
{
    protected $title = 'Convert incomplete Orders';

    protected $description = 'Find all incomplete Orders and convert them to an Estimate instead';

    public function run($request)
    {
        $orders = Order::get()
            ->filter("Status", "incomplete");
        $number = 0;

        foreach ($orders as $order) {
            $order->ClassName = "Estimate";
            $order->write();
            $number++;
        }

        $this->log("Converted {$number} Orders");
    }

    private function log($message)
    {
        if(Director::is_cli()) {
            echo $message . "\n";
        } else {
            echo $message . "<br/>";
        }
    }
}