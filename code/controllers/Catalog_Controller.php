<?php

class Catalog_Controller extends Page_Controller {
    public static $url_segment = '$ID';
    public static $url_slug = 'catalog';
	
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
	
	public function getCategory() {	    
	    return self::get_current_category();
	}
	
	public function getTitle() {
	    return $this->getCategory()->Title;
	}
	
    public function index() {
        if(!$this->request->Param('ID'))
            return new SS_HTTPResponse(null, 404);
		else
        	return $this->renderWith(array('Catalog', 'Page'));
    }
}
