<?php

class CatalogPage extends Page {
	public static $db = array ();
	
	public function getProductCategories() {
		return ProductCategory::get();
	}
	
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		if(!SiteTree::get()->filter('ClassName',$this->ClassName)->exists()) {
			$catalog_page = new CatalogPage();
			$catalog_page->Title = 'Product Catalog';
			$catalog_page->Sort = 4;
			$catalog_page->write();
			$catalog_page->publish('Stage', 'Live');
			$catalog_page->flushCache();
			DB::alteration_message('Catalog page created', 'created');
		}
	}
}