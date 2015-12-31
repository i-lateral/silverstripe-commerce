<?php

class OrderReport extends SS_Report
{

    public function title()
    {
        return "Commerce Orders";
    }

    public function description()
    {
        return "View reports on all orders made through this site";
    }

    public function columns()
    {
        return array(
            'OrderNumber' => 'Order<br/>Number',
            'Created' => 'Order<br/>Date',
            'PostageCost' => 'Postage',
            'SubTotal' => 'Total<br/>(No Postage)',
            'BillingEmail' => 'Email Address<br/><br/>',
            'DeliveryFirstnames' => 'Delivery:<br/>First Name(s)',
            'DeliverySurname' => 'Delivery:<br/>Surname',
            'DeliveryAddress1' => 'Delivery:<br/>Address 1',
            'DeliveryAddress2' => 'Delivery:<br/>Address 2',
            'DeliveryCity' => 'Delivery:<br/>City',
            'DeliveryPostCode' => 'Delivery:<br/>Post Code',
            'DeliveryCountry' => 'Delivery:<br/>Country'
        );
    }

    public function exportColumns()
    {
        // Loop through all colls and replace BR's with spaces
        $cols = array();

        foreach ($this->columns() as $key => $value) {
            $cols[$key] = str_replace('<br/>', ' ', $value);
        }

        return $cols;
    }

    public function sortColumns()
    {
        return array();
    }

    public function getReportField()
    {
        $gridField = parent::getReportField();

        // Edit CSV export button
        $export_button = $gridField->getConfig()->getComponentByType('GridFieldExportButton');
        $export_button->setExportColumns($this->exportColumns());

        return $gridField;
    }

    public function sourceRecords($params, $sort, $limit)
    {
        // Check filters
        $where_filter = array();

        $where_filter[] = (isset($params['Filter_Year'])) ? "YEAR(\"Created\") = '{$params['Filter_Year']}'" : "YEAR(\"Created\") = '".date('Y')."'";
        if (!empty($params['Filter_Month'])) {
            $where_filter[] = "Month(\"Created\") = '{$params['Filter_Month']}'";
        }
        if (!empty($params['Filter_Status'])) {
            $where_filter[] = "Status = '{$params['Filter_Status']}'";
        }

        $limit = (isset($params['ResultsLimit']) && $params['ResultsLimit'] != 0) ? $params['ResultsLimit'] : '';

        if (!isset($sort)) {
            $sort = (isset($params['Sort'])) ? Convert::raw2sql($params['Sort']) : 'Created DESC';
        }

        $orders = Order::get()
            ->where(implode(' AND ', $where_filter))
            ->limit($limit)
            ->sort($sort);

        return $orders;
    }

    public function parameterFields()
    {
        $fields = new FieldList();

        if (class_exists("Subsite")) {
            $first_order = Subsite::get_from_all_subsites("Order")
                ->sort('Created', 'ASC')
                ->first();
        } else {
            $first_order = Order::get()
                ->sort('Created', 'ASC')
                ->first();
        }

        // Check if any order exist
        if ($first_order) {
            // List all months
            $months = array('All');
            for ($i = 1; $i <= 12; $i++) {
                $months[] = date("F", mktime(0, 0, 0, $i + 1, 0, 0));
            }

            // Get the first order, then count down from current year to that
            $firstyear = new SS_Datetime('FirstDate');
            $firstyear->setValue($first_order->Created);
            $years = array();
            for ($i = date('Y'); $i >= $firstyear->Year(); $i--) {
                $years[$i] = $i;
            }

            // Order Status
            $status = singleton('Order')->dbObject('Status')->enumValues();
            array_unshift($status, 'All'); // Add an all filter to the top of the list

            //Result Limit
            $ResultLimitOptions = array(
                0 => 'All',
                50 => 50,
                100 => 100,
                200 => 200,
                500 => 500,
            );

            // Custom Sorting
            $sort = array(
                'Created DESC'      => 'Date (newest first)',
                'Created ASC'       => 'Date (oldest first)'
            );

            $fields->push(DropdownField::create('Filter_Month', 'Filter by month', $months));
            $fields->push(DropdownField::create('Filter_Year', 'Filter by year', $years));
            $fields->push(DropdownField::create('Filter_Status', 'Filter By Status', $status));
            $fields->push(DropdownField::create("ResultsLimit", "Limit results to", $ResultLimitOptions));
            $fields->push(DropdownField::create('Sort', 'Sort results', $sort));
        }

        return $fields;
    }
}
