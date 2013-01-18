<?php

class Product_Controller extends Page_Controller {
	
    public static $allowed_actions = array(
        'AddItemForm'
    );
    
    public function init() {
        parent::init();
    }
    
	public function __construct($dataRecord = null) {
	    $this->dataRecord = $dataRecord;
		$this->failover = $this->dataRecord;
		
	    $this->Title = $this->dataRecord->Title;
		
		parent::__construct();
	}
    
    /**
	 * Find the current product via its URL
	 *
	 */
	public static function getProduct() {
        $product = Product::get()->filter('URLsegment', Controller::curr()->request->Param('URLSegment'))->first();
        
        return ($product) ? $product : false;
	}
	
    
    public function AddItemForm() {
        if(ShoppingCart::isEnabled()) {
            $product = (self::getProduct()) ? self::getProduct() : false;
            $productID = ($product) ? $product->ID : 0;
            
            $quantity_fields = NumericField::create('Quantity')->setValue('1')->addExtraClass('commerce-form-additem-quantity');
            
            $fields = FieldList::create(HiddenField::create('ProductID')->setValue($productID));
            
            // If product colour customisations are set, add them to the item form 
            if($product && $product->Customisations()->exists()) {
                foreach($product->Customisations() as $customisation) {
                    switch($customisation->DisplayAs) {
                        case 'Dropdown':
                            $field = DropdownField::create(Convert::raw2url($customisation->Title),$customisation->Title, $customisation->Options()->map('ID','ItemSummary'));
                            break;
                        case 'Radio':
                            $field = OptionSetField::create(Convert::raw2url($customisation->Title),$customisation->Title, $customisation->Options()->map('ID','ItemSummary'));
                            break;
                        case 'Checkboxes':
                            $field = CheckboxSetField::create(Convert::raw2url($customisation->Title),$customisation->Title, $customisation->Options()->map('ID','ItemSummary'));
                            break;
                    }
                    $fields->add($field);
                }
            }
            
            $fields->add($quantity_fields);
            
            $actions = FieldList::create(
                FormAction::create('doAddItemToCart', 'Add to Cart')->addExtraClass('commerce-button')
            );
            
            $form = Form::create($this, 'AddItemForm', $fields, $actions)
                ->addExtraClass('commerce-form-additem')
                ->setFormAction(Controller::join_links(BASE_URL, Controller::curr()->request->param('URLSegment'), 'AddItemForm'));
            
            return $form;
        } else
            return false;
    }

    public function doAddItemToCart($data, $form) {
        $product = Product::get()->byID($data['ProductID']);
        
        if($product) {
            $cart = ShoppingCart::get();
            $cart->add($product, $data['Quantity']);
            $cart->save();
        }
        
        return $this->redirectBack();
    }
}
