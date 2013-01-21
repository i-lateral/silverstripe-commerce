<?php

/**
 * CommerceURLController determins what part of Silverstripe (framework, Commerce
 * or CMS) will handle the current URL.
 *
 * @package commerce
 * @subpackage control
 */
class CommerceURLController extends Controller {
    public function init() {
        parent::init();
    }

	public function handleRequest(SS_HTTPRequest $request, DataModel $model) {
	    $this->request = $request;
		$this->setDataModel($model);
		
		$this->pushCurrent();

		// Create a response just in case init() decides to redirect
		$this->response = new SS_HTTPResponse();

		$this->init();
	    
	    $urlsegment = $request->param('URLSegment');
	    
	    // First check products against URL segment
        if($product = Product::get()->filter('URLSegment',$urlsegment)->first()) {
            $controller = new Product_Controller($product);
        } elseif($category = ProductCategory::get()->filter('URLSegment',$urlsegment)->first()) {
            $controller = new Category_Controller($category);
	    } else {
	        // If CMS is installed
	        if(class_exists('ModelAsController'))
		        $controller = new ModelAsController();
        }
        
        $result = $controller->handleRequest($request, $model);
        
		$this->popCurrent();
        
        return $result;
	}
}
