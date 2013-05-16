<?php
/**
 * Order objects track all the details of an order and if they were completed or
 * not.
 *
 * @author morven
 */
class Order extends DataObject {
    public static $db = array(
        'OrderNumber'       => 'Varchar',
        'BillingFirstnames' => 'Varchar',
        'BillingSurname'    => 'Varchar',
        'BillingAddress1'   => 'Varchar',
        'BillingAddress2'   => 'Varchar',
        'BillingCity'       => 'Varchar',
        'BillingPostCode'   => 'Varchar',
        'BillingCountry'    => 'Varchar',
        'BillingEmail'      => 'Varchar',
        'BillingPhone'      => 'Varchar',
        'DeliveryFirstnames'=> 'Varchar',
        'DeliverySurname'   => 'Varchar',
        'DeliveryAddress1'  => 'Varchar',
        'DeliveryAddress2'  => 'Varchar',
        'DeliveryCity'      => 'Varchar',
        'DeliveryPostCode'  => 'Varchar',
        'DeliveryCountry'   => 'Varchar',
        'EmailDispatchSent' => 'Boolean',
        'Status'            => "Enum('incomplete,failed,paid,processing,dispatched','incomplete')"
    );

    public static $has_one = array(
        'Postage' => 'PostageArea'
    );

    public static $has_many = array(
        'Items' => 'OrderItem'
    );

    // Cast method calls nicely
    public static $casting = array(
        'BillingAddress'    => 'Text',
        'DeliveryAddress'   => 'Text',
        'PostageCost'       => 'Decimal',
        'SubTotal'          => 'Currency',
        'OrderTotal'        => 'Currency',
        'ItemSummary'       => 'HTMLText',
        'TranslatedStatus'  => 'Varchar'
    );

    public static $defaults = array(
        'EmailDispatchSent' => 0
    );

    public static $summary_fields = array(
        "OrderNumber" => "Order Number",
        "BillingFirstnames" => "First Name(s)",
        "BillingSurname" => "Surname",
        "BillingEmail" => "Email",
        "Status" => "Status",
        "Created" => "Created"
    );

    static $default_sort = "Created DESC";

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        // Remove defailt item admin
        $fields->removeByName('Items');
        $fields->removeByName('EmailDispatchSent');
        $fields->removeByName('PostageID');

        // Remove Billing Details
        $fields->removeByName('BillingFirstnames');
        $fields->removeByName('BillingSurname');
        $fields->removeByName('BillingAddress1');
        $fields->removeByName('BillingAddress2');
        $fields->removeByName('BillingCity');
        $fields->removeByName('BillingPostCode');
        $fields->removeByName('BillingCountry');

        // Remove Delivery Details
        $fields->removeByName('DeliveryFirstnames');
        $fields->removeByName('DeliverySurname');
        $fields->removeByName('DeliveryAddress1');
        $fields->removeByName('DeliveryAddress2');
        $fields->removeByName('DeliveryCity');
        $fields->removeByName('DeliveryPostCode');
        $fields->removeByName('DeliveryCountry');

        // Add non-editable order number
        $ordernum_field = TextField::create('OrderNumber')
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $fields->addFieldToTab('Root.Main', $ordernum_field, 'BillingEmail');

        // Display the created and last edited dates
        $lastedited_field = TextField::create('LastEdited', 'Last time order was saved')
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $created_field = TextField::create('Created')
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $fields->addFieldToTab('Root.Main', $created_field, 'EmailDispatchSent');
        $fields->addFieldToTab('Root.Main', $lastedited_field, 'EmailDispatchSent');

        // Load basic list of items
        $item_config = GridFieldConfig::create()->addComponents(
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldFooter()
        );

        $item_field = ToggleCompositeField::create('OrderItems', 'Order Items',
            array(
                GridField::create('Items',null,$this->Items(), $item_config)
            )
        )->setHeadingLevel(4);

        $fields->addFieldToTab('Root.Main', $item_field);

        // Structure billing details
        $billing_fields = ToggleCompositeField::create('BillingDetails', 'Billing Details',
            array(
                TextField::create('BillingFirstnames', 'First Name(s)'),
                TextField::create('BillingSurname', 'Surname'),
                TextField::create('BillingAddress1', 'Address 1'),
                TextField::create('BillingAddress2', 'Address 2'),
                TextField::create('BillingCity', 'City'),
                TextField::create('BillingPostCode', 'Post Code'),
                TextField::create('BillingCountry', 'Country')
            )
        )->setHeadingLevel(4);

        $fields->addFieldToTab('Root.Main', $billing_fields);

        // Structure delivery details
        $delivery_fields = ToggleCompositeField::create('DeliveryDetails', 'Delivery Details',
            array(
                DropdownField::create('PostageID', 'Postage', PostageArea::get()->map('ID', 'Location')),
                TextField::create('DeliveryFirstnames', 'First Name(s)'),
                TextField::create('DeliverySurname', 'Surname'),
                TextField::create('DeliveryAddress1', 'Address 1'),
                TextField::create('DeliveryAddress2', 'Address 2'),
                TextField::create('DeliveryCity', 'City'),
                TextField::create('DeliveryPostCode', 'Post Code'),
                TextField::create('DeliveryCountry', 'Country')
            )
        )->setHeadingLevel(4);

        $fields->addFieldToTab('Root.Main', $delivery_fields);

        return $fields;
    }

    public function getPostageCost() {
        return $this->Postage()->Cost;
    }

    public function getBillingAddress() {
        $address = ($this->BillingAddress1) ? $this->BillingAddress1 . ",\n" : '';
        $address .= ($this->BillingAddress2) ? $this->BillingAddress2 . ",\n" : '';
        $address .= ($this->BillingCity) ? $this->BillingCity . ",\n" : '';
        $address .= ($this->BillingPostCode) ? $this->BillingPostCode . ",\n" : '';
        $address .= ($this->BillingCountry) ? $this->BillingCountry : '';

        return $address;
    }

    public function getDeliveryAddress() {
        $address = ($this->DeliveryAddress1) ? $this->DeliveryAddress1 . ",\n" : '';
        $address .= ($this->DeliveryAddress2) ? $this->DeliveryAddress2 . ",\n" : '';
        $address .= ($this->DeliveryCity) ? $this->DeliveryCity . ",\n" : '';
        $address .= ($this->DeliveryPostCode) ? $this->DeliveryPostCode . ",\n" : '';
        $address .= ($this->DeliveryCountry) ? $this->DeliveryCountry : '';

        return $address;
    }

    public function getOrderTotal() {
        $total = $this->SubTotal;

        // Add postage
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            $total += DataObject::get_by_id('PostageArea', Session::get('PostageID'))->Cost;

        return number_format($total,2);
    }

    public function getItemSummary() {
        $return = '';

        foreach($this->Items() as $item) {
            $return .= "{$item->Quantity} x {$item->Title};\n";
        }

        return $return;
    }

    public function getSubTotal() {
        $total = 0;

        // Calculate total from items in the list
        foreach($this->Items() as $order_item) {
            $total += $order_item->getTotal();
        }

        return $total;
    }

    public function getTranslatedStatus() {
        return _t("Namespace." . strtoupper($this->Status), $this->Status);
    }

    protected function generate_order_number() {
        $id = str_pad($this->ID, 8,  "0");

        $guidText =
            substr($id, 0, 4) . '-' .
            substr($id, 4, 4) . '-' .
            rand(1000,9999);

        // Work out if an order prefix string has been set in siteconfig
        $config = SiteConfig::current_site_config();

        $guidText = ($config->OrderPrefix) ? $config->OrderPrefix . '-' . $guidText : $guidText;

        return $guidText;
    }

    public function onBeforeDelete() {
        // Delete all items attached to this order
        foreach($this->Items() as $item) {
            $item->delete();
        }

        parent::onBeforeDelete();
    }

    public function onAfterWrite() {
        parent::onAfterWrite();

        // Check if an order number has been generated, if not, add it and save again
        if(!$this->OrderNumber) {
            $this->OrderNumber = $this->generate_order_number();
            $this->write();
        }

        // Deal with sending the status email
        if($this->isChanged('Status') && in_array($this->Status, array('paid','processing','dispatched')) ) {
            $siteconfig = SiteConfig::current_site_config();

            $subject = _t('CommerceEmail.ORDER', 'Order') . " {$this->OrderNumber} {$this->getTranslatedStatus()}";
            $from =  $siteconfig->EmailFromAddress;

            $vars = array(
                'Order' => $this,
                'SiteConfig' => $siteconfig
            );

            // Deal with customer email
            if($siteconfig->sendCommerceEmail('Customer', $this->Status)) {
                // if subsites installed, then get the native language for that site
                $current_i18n = i18n::get_locale();
                if($this->SubsiteID && class_exists('Subsite') && $this->Subsite())
                    i18n::set_locale($this->Subsite()->Language);

                $body = $this->renderWith('OrderEmail_Customer', $vars);
                $email = new Email($from,$this->BillingEmail,$subject,$body);
                $email->sendPlain();

                // If subsites enabled, set the language back
                if($this->SubsiteID && class_exists('Subsite') && $this->Subsite())
                    i18n::set_locale($current_i18n);
            }

            // Deal with vendor email
            if($siteconfig->sendCommerceEmail('Vendor', $this->Status)) {
                    switch($this->Status) {
                        case 'paid':
                            $email_to = $siteconfig->PaidEmailAddress;
                        case 'processing':
                            $email_to = $siteconfig->ProcessingEmailAddress;
                        case 'dispatched':
                            $email_to = $siteconfig->DispatchedEmailAddress;
                    }

                    if(isset($email_to)) {
                            $body = $this->renderWith('OrderEmail_Vendor', $vars);
                            $email = new Email($from,$email_to,$subject,$body);
                            $email->sendPlain();
                    }
            }


        }
    }

    protected function onAfterDelete() {
        parent::onAfterDelete();

        foreach ($this->Items() as $item) {
            $item->delete();
        }
    }

    public function canCreate($member = null) {
        return false;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}
