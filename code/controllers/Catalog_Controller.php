<?php

class CatalogPage_Controller extends Page_Controller {
    public static $url_segment = 'catalog/$Action/$ID';
    public static $url_slug = 'catalog';
	
	public $Title = 'Product Catalog';
	
	public function init() {
		parent::init();
	}
	
    public function index() {
    	$vars = array(
			'Title' => $this->title
		);
		
        return $this->renderWith(array('Cart','Page'), $vars);
    }
}