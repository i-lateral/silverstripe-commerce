<?php

class OrderItemReport extends SS_Report {

    public function title() {
        return "Commerce Products Ordered";
    }

    public function description() {
        return "View all individual products ordered through this site";
    }

    public function columns() {
        return array(
            "SKU" => "SKU",
            "Details" => "Details",
            "Price" => "Price",
            "Quantity" => "Quantity"
        );
    }

    public function exportColumns() {
        // Loop through all colls and replace BR's with spaces
        $cols = array();

        foreach($this->columns() as $key => $value) {
            $cols[$key] = str_replace('<br/>', ' ', $value);
        }

        return $cols;
    }

    public function sortColumns() {
        return array();
    }

    public function getReportField() {
        $gridField = parent::getReportField();

        // Edit CSV export button
        $export_button = $gridField->getConfig()->getComponentByType('GridFieldExportButton');
        $export_button->setExportColumns($this->exportColumns());

        return $gridField;
    }

    public function sourceRecords($params, $sort, $limit) {
        $return = ArrayList::create();

        // Check filters
        $where_filter = array();

        $where_filter[] = (isset($params['Filter_Year'])) ? "YEAR(\"Created\") = '{$params['Filter_Year']}'" : "YEAR(\"Created\") = '".date('Y')."'";
        if(!empty($params['Filter_Month'])) $where_filter[] = "Month(\"Created\") = '{$params['Filter_Month']}'";
        if(!empty($params['Filter_Status'])) $where_filter[] = "Status = '{$params['Filter_Status']}'";
        if(!empty($params['Filter_FirstName'])) $where_filter[] = "FirstName = '{$params['Filter_FirstName']}'";
        if(!empty($params['Filter_Surname'])) $where_filter[] = "Surname = '{$params['Filter_Surname']}'";

        $orders = Order::get()
            ->where(implode(' AND ', $where_filter));

        foreach($orders as $order) {
            // Setup a filter for our order items
            $filter = array();

            if(!empty($params['Filter_ProductName']))
                $filter["Title:PartialMatch"] = $params['Filter_ProductName'];

            if(!empty($params['Filter_ProductSKU']))
                $filter["SKU"] = $params['Filter_ProductSKU'];

            $list = (count($filter)) ? $order->Items()->filter($filter) : $order->Items();

            foreach($list as $order_item) {
                if($order_item->SKU) {
                    if($list_item = $return->find("SKU", $order_item->SKU))
                        $list_item->Quantity = $list_item->Quantity + $order_item->Quantity;
                    else {
                        $report_item = OrderItemReportItem::create();
                        $report_item->ID = $order_item->SKU;
                        $report_item->SKU = $order_item->SKU;
                        $report_item->Details = $order_item->Title;
                        $report_item->Price = $order_item->Price;
                        $report_item->Quantity = $order_item->Quantity;

                        $return->add($report_item);
                    }
                }
            }
        }

        return $return;
    }

    public function parameterFields() {
        $fields = new FieldList();

        if(class_exists("Subsite")) {
            $first_order = Subsite::get_from_all_subsites("Order")
                ->sort('Created','ASC')
                ->first();
        } else {
            $first_order = Order::get()
                ->sort('Created','ASC')
                ->first();
        }

        // Check if any order exist
        if($first_order) {
            // List all months
            $months = array('All');
            for ($i = 1; $i <= 12; $i++) { $months[] = date("F", mktime(0, 0, 0, $i + 1, 0, 0)); }

            // Get the first order, then count down from current year to that
            $firstyear = new SS_Datetime('FirstDate');
            $firstyear->setValue($first_order->Created);
            $years = array();
            for ($i = date('Y'); $i >= $firstyear->Year(); $i--) { $years[$i] = $i; }

            // Order Status
            $status = singleton('Order')->dbObject('Status')->enumValues();
            array_unshift($status, 'All'); // Add an all filter to the top of the list

            $fields->push(TextField::create('Filter_FirstName', 'Filter by Customer First Name'));
            $fields->push(TextField::create('Filter_Surname', 'Filter by Customer Surname'));
            $fields->push(TextField::create('Filter_ProductSKU', 'Filter by SKU'));
            $fields->push(TextField::create('Filter_ProductName', 'Filter by Product Name'));
            $fields->push(DropdownField::create('Filter_Month', 'Filter by month', $months));
            $fields->push(DropdownField::create('Filter_Year', 'Filter by year', $years));
            $fields->push(DropdownField::create('Filter_Status', 'Filter by order status', $status));
        }

        return $fields;
    }
}
