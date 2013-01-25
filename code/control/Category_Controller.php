<?php

class Category_Controller extends Page_Controller {

	/**
	 * Find the current category via its URL
	 *
	 */
	public static function get_current_category() {
	    // Currently a category return it
	    if(Controller::curr()->request->param('URLSegment'))
	        return ProductCategory::get()->filter('URLVariable', Controller::curr()->request->param('URLSegment'))->first();
        // If not, create a fake one and return that with a map of all products
        else {
            $category = ProductCategory::create();
            $category->Title = _t('Commerce.CATALOGTITlE', 'Catalog');
            
            // If there are any categories, add as children
            if(ProductCategory::get()->exists()) {
                foreach(ProductCategory::get() as $category) {
                    $category->Children()->add($category);
                }
            }         
            
            return $category;
        }
	}
	
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
