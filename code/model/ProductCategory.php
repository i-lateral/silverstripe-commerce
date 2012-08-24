<?php

class ProductCategory extends DataObject {
	public static $db = array(
		'Title' => 'Varchar'
	);
	
	public static $has_one = array(
		'Parent'	=> 'Category'
	);
	
	public static $many_many = array(
		'Products' => 'Product'
	);

	static $extensions = array(
		"Hierarchy"
	);

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}