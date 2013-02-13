<?php
 /**
  * Add interface to manage orders through the CMS
  * 
  * @package Commerce
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
    
    public function getList() {
        $list = parent::getList();
        
        return $list;
    }
    
    public function getEditForm($id = null, $fields = null) {
    	$form = parent::getEditForm($id, $fields);
		
		if($this->modelClass == 'Order') {
			$fields = $form->Fields();
			$gridField = $fields->fieldByName('Order');
			
			// Enable selectable
			$gridField->setAttribute('data-selectable', true);
            $gridField->setAttribute('data-multiselect', true);
            
            // Add dispatch button
			$field_config = $gridField->getConfig();
			$field_config
	            ->removeComponentsByType('GridFieldExportButton')
				->addComponents(
				    new GridField_DispatchedButton(),
				    new GridField_EditSelectable()
				);
		}
		
		$this->extend('updateEditForm', $form);
		
        return $form;
    }
}
