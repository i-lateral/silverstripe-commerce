<?php

class Product extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar',
		'Description'	=> 'HTMLText',
		'Quantity'		=> 'Int',
		'StockID'		=> 'Varchar(99)'
	);
	
	public static $has_many = array(
		'Images'		=> 'ProductImage'
	);
	
	public static $belongs_many_many = array(
		'Categories'	=> 'ProductCategory'
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