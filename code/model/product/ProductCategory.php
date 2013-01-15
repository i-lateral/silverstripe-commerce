<?php

class ProductCategory extends DataObject {
	public static $db = array(
		'Title'         => 'Varchar',
		'URLVariable'   => 'Varchar',
		'ListOrGrid'    => "Enum('Grid,List','Grid')",
		'Sort'	        => 'Int'
	);
	
	public static $has_one = array(
		'Parent'        => 'ProductCategory'
	);
	
	public static $many_many = array(
		'Products'      => 'Product'
	);

	public static $extensions = array(
		"Hierarchy"
	);
	
	/**
     * Return a URL to link to this catagory (via Catalog_Controller)
     * 
     * @return string URL to cart controller
     */
    public function Link(){
        return BASE_URL . '/' . Catalog_Controller::$url_slug . '/' . $this->URLVariable;
    }

    /**
	 * Returns TRUE if this is the currently active category.
	 *
	 * @return bool
	 */
	public function isCurrent() {
		return ($this->ID && Catalog_Controller::get_current_category()) ? $this->ID == Catalog_Controller::get_current_category()->ID : $this === Catalog_Controller::get_current_category();
	}
	
	/**
	 * Check if current category is a child of selected category
	 *
	 * @return bool
	 */
	public function isSection() {
		return $this->isCurrent() || (
			Catalog_Controller::get_current_category() instanceof ProductCategory && in_array($this->ID, Catalog_Controller::get_current_category()->getAncestors()->column())
		);
	}

    /**
	 * Return "link", "current" or section depending on if this category is the
	 * current category, or a child of the current category.
	 *
	 * @return string
	 */
	public function LinkingMode() {
		if($this->isCurrent()) {
			return 'current';
		} elseif($this->isSection()) {
			return 'section';
		} else {
			return 'link';
		}
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$url_field = TextField::create('URLVariable')
		    ->setReadonly(true)
		    ->performReadonlyTransformation();
		
		$fields->addFieldToTab('Root.Main', TextField::create('Title'));
		$fields->addFieldToTab('Root.Main', $url_field);
		$fields->addFieldToTab('Root.Main', DropdownField::create('ListOrGrid','View children as a list or grid?',$this->dbObject('ListOrGrid')->enumValues()));		
		$fields->addFieldToTab('Root.Main', NumericField::create('Sort'));
		
		// If record is just created, check for parent ID in URL and set appropriately
		$parentField = new TreeDropdownField('ParentID', 'Parent Category', 'ProductCategory');

		$fields->addFieldToTab('Root.Main', $parentField);
		
		return $fields;
	}
	
	public function onBeforeWrite() {
	    parent::onBeforeWrite();
	    
	    $this->URLVariable = Convert::raw2url($this->Title);
	    
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

    public function canView($member = false) {
        return true;
    }
    
    public function canCreate($member = false) {
        return true;
    }

    public function canEdit($member = false) {
        return true;
    }

    public function canDelete($member = false) {
        return true;
    }
}
