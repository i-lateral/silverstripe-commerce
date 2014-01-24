<?php

class Ext_Commerce_Member extends DataExtension {
    private static $has_many = array(
        "Orders" => "Order"
    );

    /**
     * Get all orders that have been generated and are marked as paid or
     * processing
     *
     * @return DataList
     */
    public function getOutstandingOrders() {
        $orders = $this
            ->owner
            ->Orders()
            ->filter(array(
                "Status" => array("paid","processing")
            ));

        return $orders;
    }

    /**
     * Get all orders that have been generated and are marked as dispatched or
     * canceled
     *
     * @return DataList
     */
    public function getHistoricOrders() {
        $orders = $this
            ->owner
            ->Orders()
            ->filter(array(
                "Status" => array("dispatched","canceled")
            ));

        return $orders;
    }
}
