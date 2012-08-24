<?php
/**
 * ProductAdmin creates an admin area that allows editing of products
 * and Product Categories
 * 
 */

class ProductAdmin extends ModelAdmin {
    public static $url_segment = 'products';
    public static $menu_title = 'Products';
    public static $menu_priority = 10;
    public static $managed_models = array('Product','ProductCategory');
	
    public function init() {
        parent::init();
    }
}