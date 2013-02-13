<?php
/**
 * ProductAdmin creates an admin area that allows editing of products
 * and Product Categories
 * 
  * @package Commerce
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
    
    public function getList() {
        $list = parent::getList();
        
        // Filter categories
        if($this->modelClass == 'ProductCategory') {
            $list
			    ->where("ParentID = {$this->currentCategoryID()}")
			    ->sort('Sort','DESC');
        }
        
        return $list;
    }
	
	public function getEditForm($id = null, $fields = null) {
    	$form = parent::getEditForm($id, $fields);
		$params = $this->request->requestVar('q');
		
		// Alterations for Hiarachy on product cataloge
		if($this->modelClass == 'ProductCategory') {
			$fields = $form->Fields();
			$gridField = $fields->fieldByName('ProductCategory');
			
			// Set custom record editor
			$record_editor = new GridFieldDetailForm();
			$record_editor->setItemRequestClass('ProductCategory_ItemRequest');
			
			// Tidy up category config
			$field_config = $gridField->getConfig();
			$field_config
	            ->removeComponentsByType('GridFieldExportButton')
	            ->removeComponentsByType('GridFieldPrintButton')
	            ->removeComponentsByType('GridFieldDetailForm')
				->addComponents(
				    $record_editor,
					GridFieldLevelup::create($this->currentCategoryID())->setLinkSpec('admin/products/ProductCategory/?ParentID=%d')
				);
				
			// Find data colums, so we can add link to view children
			$columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');

			// Don't allow navigating into children nodes on filtered lists
			$fields = array(
				'Title' => 'Title',
				'URLSegment' => 'URLSegement'
			);

			if(!$params) {
				$fields = array_merge(array('listChildrenLink' => ''), $fields);
			}
	
			$columns->setDisplayFields($fields);
			$columns->setFieldCasting(array('Title' => 'HTMLText', 'URLSegment' => 'Text'));

			$controller = $this;
			$columns->setFieldFormatting(array(
			    'Title' => function($value, &$item) use($controller) {
					return sprintf(
						'<a class="list-children-link" data-pjax-target="ListViewForm" href="%s?ParentID=%d">' . $item->Title . '</a>',
						$controller->Link(),
						$item->ID,
						null
					);
				},
			    'URLSegment' => function($value, &$item) use($controller) {
					return sprintf(
						'<a class="list-children-link" data-pjax-target="ListViewForm" href="%s?ParentID=%d">' . $item->URLSegment . '</a>',
						$controller->Link(),
						$item->ID,
						null
					);
				},
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
		
		$this->extend('updateEditForm', $form);
		
        return $form;
    }
	
	/**
	 * Return the title of the current section. Either this is pulled from
	 * the current panel's menu_title or from the first active menu
	 *
	 * @return string
	 */
	function SectionTitle() {
	    if($this->modelClass == 'ProductCategory')
		    return 'Product Category';
	    else
	        return 'Product';
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

class ProductCategory_ItemRequest extends GridFieldDetailForm_ItemRequest {
	/**
	 *
	 * @param GridFIeld $gridField
	 * @param GridField_URLHandler $component
	 * @param DataObject $record
	 * @param Controller $popupController
	 * @param string $popupFormName 
	 */
	public function __construct($gridField, $component, $record, $popupController, $popupFormName) {		
		parent::__construct($gridField, $component, $record, $popupController, $popupFormName);
	}
	
	public function Link($action = null) {
	    $parentParam = Controller::curr()->request->requestVar('ParentID');
	    $link = $parentParam ? parent::Link() . "?ParentID=$parentParam" : parent::Link();
	    
		return $link;
	}
	
	/**
	 * CMS-specific functionality: Passes through navigation breadcrumbs
	 * to the template, and includes the currently edited record (if any).
	 * see {@link LeftAndMain->Breadcrumbs()} for details.
	 * 
	 * @param boolean $unlinked 
	 * @return ArrayData
	 */
	function Breadcrumbs($unlinked = false) {		
		if(!$this->popupController->hasMethod('Breadcrumbs')) return;
	    
		$items = $this->popupController->Breadcrumbs($unlinked);
		if($this->record && $this->record->ID) {
		    $ancestors = $this->record->getAncestors();
			$ancestors = new ArrayList(array_reverse($ancestors->toArray()));
			$ancestors->push($this->record);
			
			// Push each ancestor to breadcrumbs
			foreach($ancestors as $ancestor) {
				$items->push(new ArrayData(array(
					'Title' => $ancestor->Title,
					'Link' => ($unlinked) ? false : $this->popupController->Link() . "?ParentID={$ancestor->ID}"
				)));		
			}	
		} else {
			$items->push(new ArrayData(array(
				'Title' => sprintf(_t('GridField.NewRecord', 'New %s'), $this->record->singular_name()),
				'Link' => false
			)));	
		}
		
		return $items;
	}
	
	public function ItemEditForm() {
	    $form = parent::ItemEditForm();
	    
	    return $form;
	}
}
