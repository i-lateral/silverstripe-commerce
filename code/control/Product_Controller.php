<?php

class Product_Controller extends Page_Controller {
	
    public static $allowed_actions = array(
        'AddItemForm'
    );
    
	/**
	 * Find the current product via its URL
	 *
	 */
	public static function get_current_product() {
	    if(Controller::curr()->request->param('URLSegment'))
	        return Product::get()->filter('URLVariable', Controller::curr()->request->param('URLSegment'))->first();
        else
            return false;
	}
    
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
            
            $quantity_fields = QuantityField::create('Quantity', _t('Commerce.CARTQTY','Qty'))->setValue('1')->addExtraClass('commerce-form-additem-quantity');
            
            $fields = FieldList::create(HiddenField::create('ProductID')->setValue($productID));
            
            $requirements = new RequiredFields('Quantity');
            
            // If product colour customisations are set, add them to the item form 
            if($product && $product->Customisations()->exists()) {
                foreach($product->Customisations() as $customisation) {
					$field = $customisation->Field();
                    $fields->add($field);
                    
                    // Check if field required
                    if($customisation->Required) $requirements->addRequiredField($field->getName());
                }
            }
            
            // Add quantity, so it appears at the end of the fields
            $fields->add($quantity_fields);
            
            $actions = FieldList::create(
                FormAction::create('doAddItemToCart', _t('Commerce.ADDTOCART','Add to Cart'))->addExtraClass('commerce-button')->addExtraClass('btn')
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
