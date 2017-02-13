<?php

/**
 * Item that can be loaded into an OrderItem report
 *
 */
class OrderItemReportItem extends Object
{

    public $ClassName = "OrderItemReportItem";

    public $SKU;
    public $Details;
    public $OrderNumber;
    public $Price;
    public $Quantity;

    public function canView($member = null)
    {
        return true;
    }
}
