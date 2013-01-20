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
            
            $quantity_fields = QuantityField::create('Quantity')->setValue('1')->addExtraClass('commerce-form-additem-quantity');
            
            $fields = FieldList::create(HiddenField::create('ProductID')->setValue($productID));
            
            $requirements = new RequiredFields('Quantity');
            
            // If product colour customisations are set, add them to the item form 
            if($product && $product->Customisations()->exists()) {
                foreach($product->Customisations() as $customisation) {
                    $name = 'customise_' . Convert::raw2url($customisation->Title);
                    $title = ($customisation->Required) ? $customisation->Title . ' *' : $customisation->Title;

                    switch($customisation->DisplayAs) {
                        case 'Dropdown':
                            $field = DropdownField::create($name, $title, $customisation->Options()->map('ID','ItemSummary'))->setEmptyString('Please Select');
                            break;
                        case 'Radio':
                            $field = OptionSetField::create($name, $title, $customisation->Options()->map('ID','ItemSummary'));
                            break;
                        case 'Checkboxes':
                            $field = CheckboxSetField::create($name, $title, $customisation->Options()->map('ID','ItemSummary'));
                            break;
                    }
                    $fields->add($field);
                    
                    // Check if field required
                    if($customisation->Required) $requirements->addRequiredField($name);
                }
            }
            
            // Add quantity, so it appears at the end of the fields
            $fields->add($quantity_fields);
            
            $actions = FieldList::create(
                FormAction::create('doAddItemToCart', 'Add to Cart')->addExtraClass('commerce-button')
            );
            
            $form = Form::create($this, 'AddItemForm', $fields, $actions, $requirements)
                ->addExtraClass('commerce-form-additem')
                ->setFormAction(Controller::join_links(BASE_URL, Controller::curr()->request->param('URLSegment'), 'AddItemForm'));
            
            return $form;
        } else
            return false;
    }

    public function doAddItemToCart($data, $form) {
        $product = Product::get()->byID($data['ProductID']);
        $customisations = array();
        
        foreach($data as $key => $value) {
            if(!(strpos($key, 'customise') === false))
                $customisations[str_replace('customise_','',$key)] = $value;
        }

        if($product) {
            $cart = ShoppingCart::get();
            $cart->add($product, $data['Quantity'], $customisations);
            $cart->save();
        }

        return $this->redirectBack();
    }
}
