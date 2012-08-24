<?php

class ProductImage extends Image {
	public static $db = array(
		'Sort'	=> 'Int'
	);
	
	public static $has_one = array(
		'ParentProduct'		=> 'Product'
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
