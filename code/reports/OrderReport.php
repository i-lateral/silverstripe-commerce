<?php

class OrderReport extends SS_Report {
    
    function title() {
        return "Commerce Orders";
    }
    
    function parameterFields() {
        $params = new FieldList();
        
		// Check if any order exist
		if(Subsite::get_from_all_subsites("Order", null, 'Created ASC', null, 1)->exists()) {
	        // List all months
	        $months = array(
	            'All'
	        );
	        for ($i = 1; $i <= 12; $i++) { $months[] = date("F", mktime(0, 0, 0, $i + 1, 0, 0)); }
	        
	        $params->push(new DropdownField('Filter_Month', 'Filter by month', $months));
	        
	        // Get the first order, then count down from current year to that
	        $firstyear = new SS_Datetime('FirstDate');
	        $firstyear->setValue(Subsite::get_from_all_subsites("Order", null, 'Created ASC', null, 1)->First()->Created);
	        $years = array();
	        
	        for ($i = date('Y'); $i >= $firstyear->Year(); $i--) { $years[$i] = $i; }
	        
	        // Add years to dropdown for filtering
	        $params->push(new DropdownField('Filter_Year', 'Filter by year', $years));
	        
	        // Order Status
	        $status = singleton('Order')->dbObject('Status')->enumValues();
	        array_unshift($status, 'All'); // Add an all filter to the top of the list
	        $params->push(new DropdownField('Filter_Status', 'Filter By Status', $status));
	        
	        //Result Limit
	        $ResultLimitOptions = array(
	            0 => 'All',
	            50 => 50,
	            100 => 100,
	            200 => 200,
	            500 => 500,
	        );
	         
	        $params->push(new DropdownField(
	            "ResultsLimit", 
	            "Limit results to", 
	            $ResultLimitOptions
	        ));
	        
	        // Custom Sorting
	        $sort = array(
	            'Created DESC'      => 'Date (newest first)',
	            'Created ASC'       => 'Date (oldest first)'
	        );
	        
	        $params->push(new DropdownField('Sort', 'Sort results', $sort));
        }
                 
        return $params;
    }

    function sourceRecords($params, $sort, $limit) {
        $filter_date = '';
        $filter_status = '';
        $filter = '';
        
        // Add months filter
        $filter_date .= (isset($params['Filter_Month']) && $params['Filter_Month'] != "0") ? " Month(\"Created\") = '{$params['Filter_Month']}'" : "";
        
        // Add and clause if required
        $filter_date .= ($filter_date) ? " AND " : "";
        
        // Add years filter
        $filter_date .= (isset($params['Filter_Year'])) ? "YEAR(\"Created\") = '{$params['Filter_Year']}'" : "YEAR(\"Created\") = '".date('Y')."'";
        
        // Add Status Filter
        $filter_status .= (isset($params['Filter_Status']) && $params['Filter_Status'] != "0") ? " AND Status = '{$params['Filter_Status']}'" : "";
        
        $limit = (isset($params['ResultsLimit']) && $params['ResultsLimit'] != 0) ? $params['ResultsLimit'] : '';
        
        if(!isset($sort))
            $sort = (isset($params['Sort'])) ? Convert::raw2sql($params['Sort']) : 'Created DESC';
        
        $filter = $filter_date . $filter_status;
        
        if($orders = Subsite::get_from_all_subsites("Order", $filter, $sort, null, $limit))
            return $orders;
    }

    function columns() {  
        $fields = array(
            'OrderNumber' => array(
                'title' => 'Order<br/>Number',
                'formatting' => '$value'
            ),
            'Created' => array(
                'title' => 'Order<br/>Date',
                'casting' => 'SS_Datetime->Full'
            ),
            'PostageCost' => array(
                'title' => 'Postage<br/><br/>',
                'formatting' => '$value'
            ),
            'SubTotal' => array(
                'title' => 'Total<br/>(No Postage)',
                'formatting' => '$value'
            ),
            'BillingEmail' => array(
                'title' => 'Email Address<br/><br/>',
                'formatting' => '$value'
            ),
            'DeliveryFirstnames' => array(
                'title' => 'Delivery:<br/>First Name(s)',
                'formatting' => '$value'
            ),
            'DeliverySurname' => array(
                'title' => 'Delivery:<br/>Surname',
                'formatting' => '$value'
            ),
            'DeliveryAddress1' => array(
                'title' => 'Delivery:<br/>Address 1',
                'formatting' => '$value'
            ),
            'DeliveryAddress2' => array(
                'title' => 'Delivery:<br/>Address 2',
                'formatting' => '$value'
            ),
            'DeliveryCity' => array(
                'title' => 'Delivery:<br/>City',
                'formatting' => '$value'
            ),
            'DeliveryPostCode' => array(
                'title' => 'Delivery:<br/>Post Code',
                'formatting' => '$value'
            ),
            'DeliveryCountry' => array(
                'title' => 'Delivery:<br/>Country',
                'formatting' => '$value'
            )
        );
        return $fields;
    }
    
    function sortColumns() {
        return array();
    }
}