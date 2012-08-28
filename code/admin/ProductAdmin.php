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
	
	public $showImportForm = array('Product');
	
	
    public function init() {
        parent::init();
    }
	
	public function getEditForm($id = null, $fields = null) {
    	$form = parent::getEditForm($id, $fields);
		$params = $this->request->requestVar('q');
		
		// Alterations for Hiarachy on product cataloge
		if($this->modelClass == 'ProductCategory') {
			$fields = $form->Fields();
			$gridField = $fields->fieldByName('ProductCategory');
			
			// Tidy up category config
			$field_config = $gridField->getConfig();
			$field_config
	            ->removeComponentsByType('GridFieldExportButton')
	            ->removeComponentsByType('GridFieldPrintButton')
				->addComponents(
					GridFieldLevelup::create($this->currentCategoryID())->setLinkSpec('admin/products/ProductCategory/?ParentID=%d')
				);
			
			// Get GridField list
			$categories = ProductCategory::get();
			$categories
				->where("ParentID = {$this->currentCategoryID()}")
				->sort('Sort','DESC');
			
			// Update list
			$gridField->setList($categories);
			
			// Find data colums, so we can add link to view children
			$columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');

			// Don't allow navigating into children nodes on filtered lists
			$fields = array(
				'Title' => 'Title',
			);

			if(!$params) {
				$fields = array_merge(array('listChildrenLink' => ''), $fields);
			}
	
			$columns->setDisplayFields($fields);
			$columns->setFieldCasting(array('Title' => 'HTMLText'));

			$controller = $this;
			$columns->setFieldFormatting(array(
				'listChildrenLink' => function($value, &$item) use($controller) {
					$num = $item ? $item->numChildren() : null;
					if($num) {
						return sprintf(
							'<a class="list-children-link" data-pjax-target="ListViewForm" href="%s?ParentID=%d">%s</a>',
							$controller->Link(),
							$item->ID,
							$num
						);
					}
				}
			));
			
			
		}
		
        return $form;
    }
	
	/**
	 * Return fake-ID "root" if no ID is found (needed to upload files into the root-folder)
	 */
	public function currentCategoryID() {
		if(is_numeric($this->request->requestVar('ParentID'))) {
			return $this->request->requestVar('ParentID');
		} elseif (isset($this->urlParams['ParentID']) && is_numeric($this->urlParams['ID'])) {
			return $this->urlParams['ID'];
		} elseif(Session::get("{$this->class}.currentCategory")) {
			return Session::get("{$this->class}.currentCategory");
		} else {
			return 0;
		}
	}
}