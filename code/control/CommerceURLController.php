<?php

/**
 * CommerceURLController determins what part of Silverstripe (framework, Commerce
 * or CMS) will handle the current URL.
 *
 * @package commerce
 * @subpackage control
 */
class CommerceURLController extends Controller {
	public function handleRequest(SS_HTTPRequest $request, DataModel $model) {
	    // If CMS is installed
	    if(class_exists('ModelAsController')) {
		    $this->setDataModel($model);
		
		    $this->pushCurrent();
		    $this->init();

		    $controller = new ModelAsController();
		    $result     = $controller->handleRequest($request, $model);
		    
		    $this->popCurrent();
		    
		    return $result;
	    } else {
	        /**
	         *  @todo, implement something to do if CMS is not installed
	         */
	    }
		
	}
}
