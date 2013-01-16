<?php

class Product_Controller extends Page_Controller {    
    public static $allowed_actions = array(
        'AddItemForm'
    );
    
    public function init() {
        parent::init();
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
            $product = ($this->getProduct()) ? $this->getProduct() : false;
            $productID = ($product) ? $product->ID : 0;
            
            if($product && $product->Colours()->exists())
                $colours = DropdownField::create('Color','Colour',$product->Colours()->map())->addExtraClass('commerce-form-colour');
            else
                $colours = null;
            
            $fields = new FieldList(
                HiddenField::create('ProductID')->setValue($productID),
                $colours,
                NumericField::create('Quantity')->setValue('1')->addExtraClass('commerce-form-quantity')
            );
            
            $actions = new FieldList(
                FormAction::create('doAddItemToCart', 'Add to Cart')->addExtraClass('commerce-button')
            );
            
            $form = new Form($this, 'AddItemForm', $fields, $actions);
            $form->setFormAction(Controller::join_links(BASE_URL, Controller::curr()->request->param('URLSegment'), 'AddItemForm'));
            
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
