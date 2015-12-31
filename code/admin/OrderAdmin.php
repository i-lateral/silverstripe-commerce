<?php
 /**
  * Add interface to manage orders through the CMS
  *
  * @package Commerce
  */
class OrderAdmin extends ModelAdmin
{

    private static $url_segment = 'orders';

    private static $menu_title = 'Orders';

    private static $menu_priority = 4;

    private static $managed_models = array(
        'Order'
    );

    private static $model_importers = array();
    
    /**
     * For an order, export all fields by default
     * 
     */
    public function getExportFields()
    {
        if ($this->modelClass == 'Order') {
            $return = array(
                "OrderNumber"       => "#",
                "Status"            => "Status",
                "Created"           => "Created",
                "Company"           => "Company Name",
                "FirstName"         => "First Name(s)",
                "Surname"           => "Surname",
                "Email"             => "Email",
                "PhoneNumber"       => "Phone Number",
                "SubTotal"          => "SubTotal",
                "Postage"           => "Postage",
                "TaxTotal"          => "TaxTotal",
                "Total"             => "Total",
                "Address1"          => "Billing Address 1",
                "Address2"          => "Billing Address 2",
                "City"              => "Billing City",
                "PostCode"          => "Billing Post Code",
                "Country"           => "Billing Country",
                "DeliveryFirstnames"=> "Delivery First Name(s)",
                "DeliverySurname"   => "Delivery Surname",
                "DeliveryAddress1"  => "Delivery Address 1",
                "DeliveryAddress2"  => "Delivery Address 2",
                "DeliveryCity"      => "Delivery City",
                "DeliveryPostCode"  => "Delivery Post Code",
                "DeliveryCountry"   => "Delivery Country",
                "DiscountAmount"    => "Discount Amount",
                "PostageType"       => "Postage Type",
                "PostageCost"       => "Postage Cost",
                "PostageTax"        => "Postage Tax",
            );
        } else {
            $return = singleton($this->modelClass)->summaryFields();
        }

        $extend = $this->extend("updateExportFields", $return);

        if ($extend && is_array($extend)) {
            $return = $extend;
        }

        return $return;
    }

    public function getList()
    {
        $list = parent::getList();

        return $list;
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        if ($this->modelClass == 'Order') {
            $fields = $form->Fields();
            $gridField = $fields->fieldByName('Order');

            // Bulk manager
            $manager = new GridFieldBulkManager();
            $manager->removeBulkAction("bulkEdit");
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
                ->addComponent($manager);

            // Update list of items for subsite (if used)
            if (class_exists('Subsite')) {
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
