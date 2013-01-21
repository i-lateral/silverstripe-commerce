<?php

class Category_Controller extends Page_Controller {

    public function init() {
        parent::init();
    }
    
	public function __construct($dataRecord = null) {
		parent::__construct();
		
	    $this->dataRecord = $dataRecord;
		$this->failover = $this->dataRecord;
	}
    
    /**
	 * Find the current product via its URL
	 *
	 */
	public static function getCategory() {
        $return = ProductCategory::get()->filter('URLsegment', Controller::curr()->request->Param('URLSegment'))->first();
        
        return ($return) ? $return : false;
	}
	
	public function Children() {
	    return $this->dataRecord->ChildrenOrProducts();
	}
}
