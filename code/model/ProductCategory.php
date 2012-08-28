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

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Main', new TextField('Title'));
		
		// If record is just created, check for parent ID in URL and set appropriately
		$parentField = new TreeDropdownField('ParentID', 'Parent Category', 'ProductCategory');

		$fields->addFieldToTab('Root.Main', $parentField);
		
		return $fields;
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