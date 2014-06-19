<?php
/**
 * ProductAdmin creates an admin area that allows editing of products
 * and Product Categories
 *
  * @package Commerce
 */

class CatalogueAdmin extends ModelAdmin {
    private static $url_segment = 'catalogue';

    private static $menu_title = 'Catalogue';

    private static $menu_priority = 11;

    private static $managed_models = array(
        'Product' => array('title' => 'Products'),
        'ProductCategory' => array('title' => 'Categories')
    );

    private static $model_importers = array(
      'Product' => 'ProductCSVBulkLoader',
   );

    public $showImportForm = array('Product');

    public function init() {
        parent::init();
    }

    public function getList() {
        $list = parent::getList();

        // Filter categories
        if($this->modelClass == 'ProductCategory') {
            $parentID = $this->request->requestVar('ParentID');
            if(!$parentID) $parentID = 0;

            $list = $list->filter('ParentID',$parentID);
        }

        return $list;
    }

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);
        $params = $this->request->requestVar('q');

        if($this->modelClass == 'Product') {
            $gridField = $form->Fields()->fieldByName('Product');
            $field_config = $gridField->getConfig();

            // Re add creation button and update grid field
            $add_button = new GridFieldAddNewButton('toolbar-header-left');
            $add_button->setButtonName('Add Product');

            // Bulk manager
            $manager = new GridFieldBulkManager();
            $manager->removeBulkAction("unlink");
            $manager->removeBulkAction("delete");

            $manager->addBulkAction(
                'enable',
                'Enable',
                'CommerceProductBulkAction'
            );

            $manager->addBulkAction(
                'disable',
                'Disable',
                'CommerceProductBulkAction'
            );

            $manager->addBulkAction(
                'delete',
                'Delete',
                'GridFieldBulkActionDeleteHandler',
                 array(
                    'isAjax' => true,
                    'icon' => 'decline',
                    'isDestructive' => true
                )
            );

            $field_config
                ->removeComponentsByType('GridFieldExportButton')
                ->removeComponentsByType('GridFieldPrintButton')
                ->removeComponentsByType('GridFieldAddNewButton')
                ->addComponents(
                    $add_button,
                    $manager
                );

        }

        // Alterations for Hiarachy on product cataloge
        if($this->modelClass == 'ProductCategory') {
            $fields = $form->Fields();
            $gridField = $fields->fieldByName('ProductCategory');

            // Set custom record editor
            $record_editor = new GridFieldDetailForm();
            $record_editor->setItemRequestClass('ProductCategory_ItemRequest');

            // Create add button and update grid field
            $add_button = new GridFieldAddNewButton('toolbar-header-left');
            $add_button->setButtonName('Add Category');

            // Tidy up category config
            $field_config = $gridField->getConfig();
            $field_config
                ->removeComponentsByType('GridFieldExportButton')
                ->removeComponentsByType('GridFieldPrintButton')
                ->removeComponentsByType('GridFieldDetailForm')
                ->removeComponentsByType('GridFieldAddNewButton')
                ->addComponents(
                    $record_editor,
                    $add_button,
                    GridFieldOrderableRows::create('Sort')
                );

            // Setup hierarchy view
            $parentID = $this->request->requestVar('ParentID');

            if($parentID){
                $field_config->addComponent(
                    GridFieldLevelup::create($parentID)
                        ->setLinkSpec('?ParentID=%d')
                        ->setAttributes(array(
                            'data-pjax' => 'ListViewForm,Breadcrumbs'
                        ))
                );
            }

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
                'listChildrenLink' => function($value, &$item) use($controller) {
                    return sprintf(
                        '<a class="list-children-link" data-pjax-target="ListViewForm" href="%s?ParentID=%d">&#9658;</a>',
                        $controller->Link(),
                        $item->ID
                    );
                }
            ));
        }

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
}

class ProductCategory_ItemRequest extends GridFieldDetailForm_ItemRequest {
    private static $allowed_actions = array(
        "ItemEditForm"
    );

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

        if($form) {
            // Update the default parent field
            $parentParam = Controller::curr()->request->requestVar('ParentID');
            $parent_field = $form->Fields()->dataFieldByName("ParentID");

            if($parentParam && $parent_field) {
                $parent_field->setValue($parentParam);
            }

            return $form;
        }
    }
}
