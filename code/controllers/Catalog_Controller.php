<?php

class Catalog_Controller extends Page_Controller {
    public static $url_segment = '$ID';
    public static $url_slug = 'catalog';
	
	public function init() {
		parent::init();
		
		Requirements::themedCSS("Commerce","commerce");
	}
	
	public function getCategory() {	    
	    return ProductCategory::get()->filter('URLVariable', $this->request->Param('ID'))->First();
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
