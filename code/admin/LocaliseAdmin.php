<?php
 /**
  * Add interface to manage localisation settings through the CMS
  * 
  * @package Commerce
  */
class LocaliseAdmin extends ModelAdmin {
    public static $url_segment = 'localisation';
    public static $menu_title = 'Localisation';
    public static $menu_priority = -1;
    
    static $managed_models = array(
        'CommerceCurrency',
        'ProductWeight',
    );
    
    public function getEditForm($id = null, $fields = null) {
    	$form = parent::getEditForm($id, $fields);
		
        return $form;
    }
}
