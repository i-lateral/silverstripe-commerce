<?php
/**
 * Order objects track all the details of an order and if they were completed or
 * not.
 *
 * Makes use of permissions provider to lock out users who have not got the
 * relevent COMMERCE permissions for:
 *   VIEW
 *   EDIT
 *   DELETE
 *
 * Any user can create an order (this allows us to support "guest" users).
 *
 * @author morven
 */
class Order extends DataObject implements PermissionProvider {

    private static $db = array(
        'OrderNumber'       => 'Varchar',
        'Status'            => "Enum('incomplete,failed,canceled,paid,processing,dispatched','incomplete')",
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
        'GatewayData'       => 'Text',
        'PostageType'       => 'Varchar',
        'PostageCost'       => 'Currency',
        'PaymentID'         => 'Varchar(99)', // ID number returned by the payment gateway (if any)
    );

    private static $has_one = array(
        "Postage"           => "PostageArea",
        "Customer"          => "Member"
    );

    private static $has_many = array(
        'Items'             => 'OrderItem'
    );

    // Cast method calls nicely
    private static $casting = array(
        'BillingAddress'    => 'Text',
        'DeliveryAddress'   => 'Text',
        'SubTotal'          => 'Currency',
        'Total'             => 'Currency',
        'ItemSummary'       => 'HTMLText',
        'TranslatedStatus'  => 'Varchar'
    );

    private static $defaults = array(
        'EmailDispatchSent' => 0
    );

    private static $summary_fields = array(
        "OrderNumber"       => "Order Number",
        "BillingFirstnames" => "First Name(s)",
        "BillingSurname"    => "Surname",
        "BillingEmail"      => "Email",
        "Status"            => "Status",
        "Created"           => "Created"
    );

    private static $default_sort = "Created DESC";

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        // Remove defailt item admin
        $fields->removeByName('Items');
        $fields->removeByName('EmailDispatchSent');
        $fields->removeByName('PostageID');
        $fields->removeByName('PaymentID');
        $fields->removeByName('GatewayData');

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

        // Add non-editable payment ID
        $paymentid_field = TextField::create('PaymentID', "Payment gateway ID number")
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $fields->addFieldToTab('Root.Gateway', $paymentid_field);

        $gateway_data = LiteralField::create(
            "FormattedGatewayData",
            "<strong>Data returned from the payment gateway:</strong><br/><br/>" .
            str_replace(",",",<br/>",$this->GatewayData)
        );

        $fields->addFieldToTab("Root.Gateway", $gateway_data);

        return $fields;
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

    /**
     * Total values of items in this order
     *
     * @return Currency
     */
    public function getSubTotal() {
        $total = 0;

        // Calculate total from items in the list
        foreach($this->Items() as $item) {
            $total += $item->getTotal();
        }

        $currency = new Currency();
        $currency->setValue($total);

        return $currency;
    }

    /**
     * Total of order including postage
     *
     * @return Decimal
     */
    public function getTotal() {
        $value = $this->SubTotal->Value + $this->PostageCost;
        $currency = new Currency();
        $currency->setValue($value);

        return $currency;
    }

    public function getItemSummary() {
        $return = '';

        foreach($this->Items() as $item) {
            $return .= "{$item->Quantity} x {$item->Title};\n";
        }

        return $return;
    }

    public function getTranslatedStatus() {
        return _t("Commerce." . strtoupper($this->Status), $this->Status);
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
        if($this->isChanged('Status') && in_array($this->Status, array('failed','paid','processing','dispatched')) ) {
            $siteconfig = SiteConfig::current_site_config();

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

                $subject = _t('CommerceEmail.ORDER', 'Order') . " {$this->OrderNumber} {$this->getTranslatedStatus()}";

                $body = $this->renderWith('OrderEmail_Customer', $vars);
                $email = new Email($from,$this->BillingEmail,$subject,$body);
                $email->sendPlain();

                // If subsites enabled, set the language back
                if($this->SubsiteID && class_exists('Subsite') && $this->Subsite())
                    i18n::set_locale($current_i18n);
            }

            // Deal with vendor email
            if($siteconfig->sendCommerceEmail('Vendor', $this->Status)) {
                $subject = _t('CommerceEmail.ORDER', 'Order') . " {$this->OrderNumber} {$this->getTranslatedStatus()}";
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

    public function providePermissions() {
        return array(
            "COMMERCE_VIEW_ORDERS" => array(
                'name' => 'View any order',
                'help' => 'Allow user to view any commerce order',
                'category' => 'Commerce',
                'sort' => 99
            ),
            "COMMERCE_EDIT_ORDERS" => array(
                'name' => 'Edit any order',
                'help' => 'Allow user to edit any commerce order',
                'category' => 'Commerce',
                'sort' => 98
            ),
            "COMMERCE_DELETE_ORDERS" => array(
                'name' => 'Delete any order',
                'help' => 'Allow user to delete any commerce order',
                'category' => 'Commerce',
                'sort' => 97
            ),
        );
    }

    /**
     * Anyone can create orders, even guest users
     *
     * @return Boolean
     */
    public function canCreate($member = null) {
        return true;
    }

    /**
     * Only order creaters or users with VIEW admin rights can view an order
     *
     * @return Boolean
     */
    public function canView($member = null) {
        if($member instanceof Member)
            $memberID = $member->ID;
        else if(is_numeric($member))
            $memberID = $member;
        else
            $memberID = Member::currentUserID();

        if($memberID && Permission::checkMember($memberID, array("ADMIN", "COMMERCE_VIEW_ORDERS")))
            return true;
        else if($memberID && $memberID == $this->CustomerID)
            return true;

        return false;
    }

    /**
     * Only order creaters or users with EDIT admin rights can view an order
     *
     * @return Boolean
     */
    public function canEdit($member = null) {
        if($member instanceof Member)
            $memberID = $member->ID;
        else if(is_numeric($member))
            $memberID = $member;
        else
            $memberID = Member::currentUserID();

        if($memberID && Permission::checkMember($memberID, array("ADMIN", "COMMERCE_EDIT_ORDERS")))
            return true;
        else if($memberID && $memberID == $this->CustomerID)
            return true;

        return false;
    }

    /**
     * Only order creaters or users with DELETE admin rights can view an order
     *
     * @return Boolean
     */
    public function canDelete($member = null) {
        if($member instanceof Member)
            $memberID = $member->ID;
        else if(is_numeric($member))
            $memberID = $member;
        else
            $memberID = Member::currentUserID();

        if($memberID && Permission::checkMember($memberID, array("ADMIN", "COMMERCE_DELETE_ORDERS")))
            return true;
        else if($memberID && $memberID == $this->CustomerID)
            return true;

        return false;
    }
}
