<?php
 /**
  * Add interface to manage orders through the CMS
  * 
  * @package OrderAdmin
  */
class OrderAdmin extends ModelAdmin {
    public static $url_segment = 'orders';
    public static $menu_title = 'Orders';
    public static $menu_priority = 4;
    
    protected $resultsTableClassName = 'OrderTableField';
    
    static $managed_models = array(
        'Order'
    );

    static $model_importers = array();
    
    public function init() {
        parent::init();
        
        Requirements::javascript(Director::absoluteBaseURL() . 'commerce/js/OrderAdmin.js');
    }
}