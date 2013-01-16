<?php

class ProductCategory extends DataObject {
	public static $db = array(
		'Title'         => 'Varchar',
		'URLSegment'   => 'Varchar',
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
	
	public static $summary_fields = array(
	    'Title' => 'Title',
	    'URLSegment' => 'URLSegment'
	);
	
	/**
     * Return a URL to link to this catagory (via Catalog_Controller)
     * 
     * @return string URL to cart controller
     */
    public function Link(){
        return Controller::join_links(BASE_URL , $this->URLSegment);
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
		
		$url_field = TextField::create('URLSegment')
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
	    
	    // Only call on first creation, ir if title is changed
	    if(($this->ID = 0) || $this->isChanged('Title')) {
	        // Set the URL Segment, so it can be accessed via the controller
            $filter = URLSegmentFilter::create();
		    $t = $filter->filter($this->Title);
		
		    // Fallback to generic name if path is empty (= no valid, convertable characters)
		    if(!$t || $t == '-' || $t == '-1') $t = "category-{$this->ID}";
	        
	        // Ensure that this object has a non-conflicting URLSegment value.
	        $existing_cats = ProductCategory::get()->filter('URLSegment',$t)->count();
	        $existing_products = Product::get()->filter('URLSegment',$t)->count();
	        $existing_pages = (class_exists('SiteTree')) ? SiteTree::get()->filter('URLSegment',$t)->count() : 0;
	        
	        $count = (int)$existing_cats + (int)$existing_products + (int)$existing_pages;
	        
	        $this->URLSegment = ($count) ? $t . '-' . ($count + 1) : $t;
	    }
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
	
	public function ChildrenOrProducts() {
		if($this->Children()->exists())
			return $this->Children();
		elseif($this->Products()->exists())
			return $this->Products();
		else
		    return false;
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
