<?php

class Product extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar',
		'URLVariable'	=> 'Varchar',
		'Price'         => 'Decimal',
		'Description'	=> 'HTMLText',
		'Quantity'		=> 'Int',
		'PackSize'      => 'Varchar',
		'Weight'        => 'Int',
		'StockID'		=> 'Varchar(99)'
	);
	
	public static $has_many = array(
		'Images'		=> 'ProductImage',
		'Colours'       => 'ProductColour',
		'Attributes'    => 'ProductAttribute'
	);
	
	public static $belongs_many_many = array(
		'Categories'	=> 'ProductCategory'
	);
	
	public static $casting = array(
	    'CategoriesList' => 'Varchar'
	);
	
	public static $summary_fields = array(
	    'Title'         => 'Title',
	    'StockID'       => 'Stock Number', 
	    'Price'         => 'Price',
	    'CategoriesList'=> 'Categories'
	);
	
	/**
     * Return a URL to link to this product (via Catalog_Controller)
     * 
     * @return string URL to cart controller
     */
    public function Link(){
        if(Controller::curr()->request->Param('ID'))
            $cat_url = Controller::curr()->request->Param('ID');
        elseif($this->Categories()->First())
            $cat_url = $this->Categories()->First()->URLVariable;
        else
            $cat_url = 'product';
        
        return Controller::join_links(BASE_URL , Catalog_Controller::$url_slug , $cat_url , $this->URLVariable);
    }
    
    /**
     * Overwrite default image and load a not found image if not found
     *
     */
    public function getImages() {    
        if($this->Images()->exists())
            $images = $this->Images();
        else {
            $images = new Image();
            $images->Title = "No Image Available";
            $images->FileName = BASE_URL . '/commerce/images/no-image.png';
        }
        
        return $images;
    }
    
	public function getCategoriesList() {
	    $list = '';
	    
	    if($this->Categories()->exists()){
	        foreach($this->Categories() as $category) {
	            $list .= $category->Title;
	            $list .= ', ';
	        }
	    }
	    
	    return $list;
	}
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$url_field = TextField::create('URLVariable')
		    ->setReadonly(true)
		    ->performReadonlyTransformation();
		
		$fields->addFieldToTab('Root.Main', $url_field, 'Price');
		
		$fields->removeByName('Quantity');
		$fields->removeByName('PackSize');
		$fields->removeByName('Weight');
		$fields->removeByName('StockID');
		
		$additional_field = ToggleCompositeField::create('AdditionalData', 'Additional Data',
			array(
				NumericField::create("Quantity", $this->fieldLabel('Quantity')),
				TextField::create("PackSize", $this->fieldLabel('PackSize')),
				TextField::create("Weight", $this->fieldLabel('Weight')),
				TextField::create("StockID", $this->fieldLabel('StockID'))
			)
		)->setHeadingLevel(4);
		
		$fields->addFieldToTab('Root.Main', $additional_field);
		
		// Deal with product images
		$upload_field = new UploadField('Images');
		$upload_field->setFolderName('products');
		
		$fields->addFieldToTab('Root.Images', $upload_field);
		
		// Deal with product features
		$attributes_field = new StackedTableField('Attributes', 'ProductAttribute', null, array('Title' => 'TextField', 'Content' => 'TextField'));
		$colours_field = new StackedTableField('Colours', 'ProductColour', null, array('Title' => 'TextField', 'ColourCode' => 'ColorField', 'Quantity' => 'TextField'));
		
		$fields->addFieldToTab('Root.Attributes', $attributes_field);
		$fields->addFieldToTab('Root.Colours', $colours_field);
		
		return $fields;
	}
	
	public function onBeforeWrite() {
	    parent::onBeforeWrite();
	    
	    $this->URLVariable = Convert::raw2url($this->Title);
	}
	
    public function canView($member = false) {
        return true;
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
