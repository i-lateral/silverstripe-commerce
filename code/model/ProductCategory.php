<?php

class ProductCategory extends DataObject {
	public static $db = array(
		'Title' => 'Varchar'
	);
	
	public static $has_one = array(
		'Parent'	=> 'Category'
	);
	
	public static $many_many = array(
		'Products' => 'Product'
	);

	public static $extensions = array(
		"Hierarchy"
	);
	
	/**
     * Return a URL to link to this catagory (via Catalog_Controller)
     * 
     * @return string URL to cart controller
     */
    public function getLink(){
        return BASE_URL . '/' . Catalog_Controller::$url_slug . '/' . Convert::raw2url($this->Title);
    }

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Main', new TextField('Title'));
		
		// If record is just created, check for parent ID in URL and set appropriately
		$parentField = new TreeDropdownField('ParentID', 'Parent Category', 'ProductCategory');

		$fields->addFieldToTab('Root.Main', $parentField);
		
		return $fields;
	}
	
	public function onBeforeDelete() {
		parent::onBeforeDelete();
		
		if($this->Children()) {
			foreach($this->Children() as $child) {
				$child->delete();
			}
		}
	}
	
	public function populateDefaults() {
		$parentParam = Controller::curr()->request->requestVar('ParentID');
		
		if($parentParam && is_numeric($parentParam))
			$this->ParentID = $parentParam;
		
		parent::populateDefaults();
	}
	
	public function getChildren() {
		if($this->getChildren()->exists()) {
			return 1;
		} else {
			return 0;
		}
	}

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}