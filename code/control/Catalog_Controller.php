<?php

class Catalog_Controller extends Page_Controller {
    public static $url_slug = 'catalog';
    
    public static $allowed_actions = array(
        'AddItemForm'
    );
	
	public function init() {
		parent::init();
		
		Requirements::themedCSS("Commerce","commerce");
	}
	
	/**
	 * Find the current category via its URL
	 *
	 */
	public static function get_current_category() {
	    if(Controller::curr() instanceof Catalog_Controller)
	        return ProductCategory::get()->filter('URLVariable', Controller::curr()->request->Param('ID'))->First();
        else
            return false;
	}
	
	/**
	 * Find the current product via its URL
	 *
	 */
	public static function get_current_product() {
	    if(Controller::curr() instanceof Catalog_Controller)
	        return Product::get()->filter('URLVariable', Controller::curr()->request->Param('ProductID'))->First();
        else
            return false;
	}
	
	public function isProduct() {
	    if(Controller::curr()->request->Param('ProductID'))
	        return true;
        else
            return false;
	}
	
	public function getCategory() {	    
	    return self::get_current_category();
	}
	
	public function getProduct() {	
	    return self::get_current_product();
	}
	
	public function getTitle() {
	    if($this->isProduct())
	        return $this->getProduct()->Title;
        else
	        return $this->getCategory()->Title;
	}
	
	/**
	 * Create an array list of either current category children or products
	 *
	 */
	public function CategoriesOrProducts() {
	    $category = $this->getCategory();
	    $return = false;
	    
	    if($category->Children()->exists())
	        $return = $category->Children();
        elseif($category->Products()->exists())
            $return = $category->Products();
            
        return $return;
	}
	
    public function index() {
		if($this->request->Param('ID') && $this->request->Param('ProductID'))
        	return $this->renderWith(array('Product', 'Page'));
		else
        	return $this->renderWith(array('Categorys', 'Page'));
    }
    
    public function AddItemForm() {
        if(ShoppingCart::isEnabled()) {
            $productID = ($this->getProduct()) ? $this->getProduct()->ID : 0;
            $fields = new FieldList(
                HiddenField::create('ProductID')->setValue($productID),
                NumericField::create('Quantity')->setValue('1')->addExtraClass('commerce-form-quantity')
            );
            
            $actions = new FieldList(
                FormAction::create('doAddItemToCart', 'Add to Cart')->addExtraClass('commerce-button')
            );
            
            return new Form($this, 'AddItemForm', $fields, $actions);
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
