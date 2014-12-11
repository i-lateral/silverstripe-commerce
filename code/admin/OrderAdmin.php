<?php
 /**
  * Add interface to manage orders through the CMS
  *
  * @package Commerce
  */
class OrderAdmin extends ModelAdmin {

    private static $url_segment = 'orders';

    private static $menu_title = 'Orders';

    private static $menu_priority = 4;

    private static $managed_models = array(
        'Order'
    );

    private static $model_importers = array();

    public function getList() {
        $list = parent::getList();

        return $list;
    }

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);

        if($this->modelClass == 'Order') {
            $fields = $form->Fields();
            $gridField = $fields->fieldByName('Order');

            // Bulk manager
            $manager = new GridFieldBulkManager();
            $manager->removeBulkAction("bulkedit");
            $manager->removeBulkAction("unLink");
            $manager->removeBulkAction("delete");

            $manager->addBulkAction(
                'paid',
                'Mark Paid',
                'CommerceGridFieldBulkAction_Paid'
            );

            $manager->addBulkAction(
                'processing',
                'Mark Processing',
                'CommerceGridFieldBulkAction_Processing'
            );

            $manager->addBulkAction(
                'dispatched',
                'Mark Dispatched',
                'CommerceGridFieldBulkAction_Dispatched'
            );


            // Add dispatch button
            $field_config = $gridField->getConfig();
            $field_config
                ->removeComponentsByType('GridFieldExportButton')
                ->addComponent($manager);

            // Update list of items for subsite (if used)
            if(class_exists('Subsite')) {
                $list = $gridField
                    ->getList()
                    ->filter(array(
                        'SubsiteID' => Subsite::currentSubsiteID()
                    ));

                $gridField->setList($list);
            }
        }

        $this->extend("updateEditForm", $form);

        return $form;
    }
}
