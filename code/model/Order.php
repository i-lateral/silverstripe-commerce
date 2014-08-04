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
        'Status'            => "Enum('incomplete,failed,canceled,paid,pending,processing,dispatched','incomplete')",
        // Billing Details
        'Company'           => 'Varchar',
        'FirstName'         => 'Varchar',
        'Surname'           => 'Varchar',
        'Address1'          => 'Varchar',
        'Address2'          => 'Varchar',
        'City'              => 'Varchar',
        'PostCode'          => 'Varchar',
        'Country'           => 'Varchar',
        'Email'             => 'Varchar',
        'PhoneNumber'       => 'Varchar',
        // Delivery Details
        'DeliveryFirstnames'=> 'Varchar',
        'DeliverySurname'   => 'Varchar',
        'DeliveryAddress1'  => 'Varchar',
        'DeliveryAddress2'  => 'Varchar',
        'DeliveryCity'      => 'Varchar',
        'DeliveryPostCode'  => 'Varchar',
        'DeliveryCountry'   => 'Varchar',
        // Discount provided
        "DiscountAmount"    => "Currency",
        // Postage and Email notification
        'PostageType'       => 'Varchar',
        'PostageCost'       => 'Currency',
        'PostageTax'        => 'Currency',
        'EmailDispatchSent' => 'Boolean',
        // Payment Gateway Info
        'GatewayData'       => 'Text',
        'PaymentID'         => 'Varchar(99)' // ID number returned by the payment gateway (if any)
    );

    private static $has_one = array(
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
        'Postage'           => 'Currency',
        'TaxTotal'          => 'Currency',
        'Total'             => 'Currency',
        'ItemSummary'       => 'HTMLText',
        'TranslatedStatus'  => 'Varchar'
    );

    private static $defaults = array(
        'EmailDispatchSent' => 0,
        'DiscountAmount'    => 0
    );

    private static $summary_fields = array(
        "OrderNumber"   => "#",
        "FirstName"     => "First Name(s)",
        "Surname"       => "Surname",
        "Email"         => "Email",
        "Status"        => "Status",
        "Total"         => "Total",
        "Created"       => "Created"
    );

    private static $extensions = array(
        "Versioned('History')"
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
        $fields->removeByName('Address1');
        $fields->removeByName('Address2');
        $fields->removeByName('City');
        $fields->removeByName('PostCode');
        $fields->removeByName('Country');

        // Remove Delivery Details
        $fields->removeByName('DeliveryFirstnames');
        $fields->removeByName('DeliverySurname');
        $fields->removeByName('DeliveryAddress1');
        $fields->removeByName('DeliveryAddress2');
        $fields->removeByName('DeliveryCity');
        $fields->removeByName('DeliveryPostCode');
        $fields->removeByName('DeliveryCountry');

        // Remove default postage fields
        $fields->removeByName('PostageType');
        $fields->removeByName('PostageCost');
        $fields->removeByName('PostageTax');

        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create('OrderNumber', "#"),
            'Status'
        );

        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create('Created')
        );

        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create('LastEdited', 'Last time order was saved')
        );

        // Structure billing details
        $billing_fields = ToggleCompositeField::create('BillingDetails', 'Billing Details',
            array(
                TextField::create('Address1', 'Address 1'),
                TextField::create('Address2', 'Address 2'),
                TextField::create('City', 'City'),
                TextField::create('PostCode', 'Post Code'),
                TextField::create('Country', 'Country')
            )
        )->setHeadingLevel(4);


        // Structure delivery details
        $delivery_fields = ToggleCompositeField::create('DeliveryDetails', 'Delivery Details',
            array(
                TextField::create('DeliveryFirstnames', 'First Name(s)'),
                TextField::create('DeliverySurname', 'Surname'),
                TextField::create('DeliveryAddress1', 'Address 1'),
                TextField::create('DeliveryAddress2', 'Address 2'),
                TextField::create('DeliveryCity', 'City'),
                TextField::create('DeliveryPostCode', 'Post Code'),
                TextField::create('DeliveryCountry', 'Country'),
            )
        )->setHeadingLevel(4);

        // Postage details
        // Structure billing details
        $postage_fields = ToggleCompositeField::create('Postage', 'Postage Details',
            array(
                ReadonlyField::create('PostageType'),
                ReadonlyField::create('PostageCost'),
                ReadonlyField::create('PostageTax')
            )
        )->setHeadingLevel(4);

        $fields->addFieldToTab('Root.Main', $billing_fields);
        $fields->addFieldToTab('Root.Main', $delivery_fields);
        $fields->addFieldToTab('Root.Main', $postage_fields);


        // Add order items and totals
        $fields->addFieldToTab(
            'Root.Items',
            GridField::create(
                'Items',
                "Order Items",
                $this->Items(),
                GridFieldConfig::create()->addComponents(
                    new GridFieldSortableHeader(),
                    new GridFieldDataColumns()
                )
            )
        );

        $fields->addFieldToTab(
            "Root.Items",
            ReadonlyField::create("SubTotal")
                ->setValue($this->getSubTotal())
        );

        $fields->addFieldToTab(
            "Root.Items",
            ReadonlyField::create("DiscountAmount")
                ->setValue($this->DiscountAmount)
        );

        $fields->addFieldToTab(
            "Root.Items",
            ReadonlyField::create("Tax")
                ->setValue($this->getTaxTotal())
        );

        $fields->addFieldToTab(
            "Root.Items",
            ReadonlyField::create("Total")
                ->setValue($this->getTotal())
        );

        $member = Member::currentUser();

        if(Permission::check('ADMIN', 'any', $member)) {
            // Add non-editable payment ID
            $paymentid_field = TextField::create('PaymentID', "Payment gateway ID number")
                ->setReadonly(true)
                ->performReadonlyTransformation();


            $gateway_data = LiteralField::create(
                "FormattedGatewayData",
                "<strong>Data returned from the payment gateway:</strong><br/><br/>" .
                str_replace(",",",<br/>",$this->GatewayData)
            );


            $fields->addFieldToTab('Root.Gateway', $paymentid_field);
            $fields->addFieldToTab("Root.Gateway", $gateway_data);
        }

        // Setup basic history of this order
        $versions = $this->AllVersions();
        $curr_version = $versions->First()->Version;
        $message = "";

        foreach($versions as $version) {
            $i = $version->Version;
            $name = "History_{$i}";

            if($i > 1) {
                $frm = Versioned::get_version($this->class, $this->ID, $i - 1);
                $to = Versioned::get_version($this->class, $this->ID, $i);
                $diff = new DataDifferencer($frm, $to);

                if($version->Author())
                    $message = "<p>{$version->Author()->FirstName} ({$version->LastEdited})</p>";
                else
                    $message = "<p>Unknown ({$version->LastEdited})</p>";

                if($diff->ChangedFields()->exists()) {
                    $message .= "<ul>";

                    // Now loop through all changed fields and track as message
                    foreach($diff->ChangedFields() as $change) {
                        if($change->Name != "LastEdited")
                            $message .= "<li>{$change->Title}: {$change->Diff}</li>";
                    }

                    $message .= "</ul>";
                }
            }

            $fields->addFieldToTab("Root.History", LiteralField::create(
                $name,
                "<div class=\"field\">{$message}</div>"
            ));
        }

        $this->extend("updateCMSFields", $fields);

        return $fields;
    }

    public function getBillingAddress() {
        $address = ($this->Address1) ? $this->Address1 . ",\n" : '';
        $address .= ($this->Address2) ? $this->Address2 . ",\n" : '';
        $address .= ($this->City) ? $this->City . ",\n" : '';
        $address .= ($this->PostCode) ? $this->PostCode . ",\n" : '';
        $address .= ($this->Country) ? $this->Country : '';

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


    public function hasDiscount() {
         return (ceil($this->DiscountAmount)) ? true : false;
    }

    /**
     * Total values of items in this order (without any tax)
     *
     * @return Decimal
     */
    public function getSubTotal() {
        $total = 0;

        // Calculate total from items in the list
        foreach($this->Items() as $item) {
            $total += $item->getSubTotal();
        }

        return $total;
    }

    /**
     * Total values of items in this order
     *
     * @return Decimal
     */
    public function getTaxTotal() {
        $total = 0;

        // Calculate total from items in the list
        foreach($this->Items() as $item) {
            $total += $item->getTaxTotal();
        }

        // Add any tax from postage
        $total += $this->PostageTax;

        return $total;
    }

    /**
     * Get the postage cost for this order
     *
     * @return Decimal
     */
    public function getPostage() {
        return $this->PostageCost;
    }

    /**
     * Total of order including postage
     *
     * @return Decimal
     */
    public function getTotal() {
        $sub = ($this->hasDiscount()) ? $this->SubTotal - $this->DiscountAmount : $this->SubTotal;

        return number_format($sub + $this->Postage + $this->TaxTotal, 2);
    }

    /**
     * Return a list string summarising each item in this order
     *
     * @return string
     */
    public function getItemSummary() {
        $return = '';

        foreach($this->Items() as $item) {
            $return .= "{$item->Quantity} x {$item->Title};\n";
        }

        return $return;
    }

    public function getTranslatedStatus() {
        switch($this->Status) {
            case "incomplete":
                $return = _t("CommerceStatus.Incomplete","Incomplete");
                break;
            case "failed":
                $return = _t("CommerceStatus.Failed","Failed");
                break;
            case "canceled":
                $return = _t("CommerceStatus.Cancelled","Cancelled");
                break;
            case "paid":
                $return = _t("CommerceStatus.Paid","Paid");
                break;
            case "pending":
                $return = _t("CommerceStatus.Pending","Pending");
                break;
            case "processing":
                $return = _t("CommerceStatus.Processing","Processing");
                break;
            case "dispatched":
                $return = _t("CommerceStatus.Dispatched","Dispatched");
                break;
        }

        return $return;
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

    /**
     * API Callback before this object is written to the DB
     *
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();

        // See if this order was just marked paid, if so reduce quantities for
        // items.
        if($this->isChanged("Status") && $this->Status == "paid") {
            foreach($this->Items() as $item) {
                $product = $item->MatchProduct;

                if($product->ID && $product->Quantity) {
                    $new_qty = $product->Quantity - $item->Quantity;
                    $product->Quantity = ($new_qty > 0) ? $new_qty : 0;
                    $product->write();
                }
            }
        }
    }

    /**
     * API Callback before this object is removed from to the DB
     *
     */
    public function onBeforeDelete() {
        // Delete all items attached to this order
        foreach($this->Items() as $item) {
            $item->delete();
        }

        parent::onBeforeDelete();
    }


    /**
     * API Callback after this object is written to the DB
     *
     */
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

                $subject = _t('CommerceEmail.Order', 'Order') . " {$this->OrderNumber} {$this->getTranslatedStatus()}";

                $body = $this->renderWith('OrderEmail_Customer', $vars);
                $email = new Email($from,$this->Email,$subject,$body);
                $email->sendPlain();

                // If subsites enabled, set the language back
                if($this->SubsiteID && class_exists('Subsite') && $this->Subsite())
                    i18n::set_locale($current_i18n);
            }

            // Deal with vendor email
            if($siteconfig->sendCommerceEmail('Vendor', $this->Status)) {
                $subject = _t('CommerceEmail.Order', 'Order') . " {$this->OrderNumber} {$this->getTranslatedStatus()}";
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


    /**
     * API Callback after this object is removed from to the DB
     *
     */
    public function onAfterDelete() {
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
            "COMMERCE_ORDER_HISTORY" => array(
                'name' => 'View order history',
                'help' => 'Allow user to see the history of an order',
                'category' => 'Commerce',
                'sort' => 96
            )
        );
    }

    /**
     * Only order creators or users with VIEW admin rights can view
     *
     * @return Boolean
     */
    public function canView($member = null) {
        $extended = $this->extend('canView', $member);
        if($extended && $extended !== null) return $extended;

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
     * Anyone can create orders, even guest users
     *
     * @return Boolean
     */
    public function canCreate($member = null) {
        $extended = $this->extend('canCreate', $member);
        if($extended && $extended !== null) return $extended;

        return true;
    }

    /**
     * Only users with EDIT admin rights can view an order
     *
     * @return Boolean
     */
    public function canEdit($member = null) {
        $extended = $this->extend('canEdit', $member);
        if($extended && $extended !== null) return $extended;

        if($member instanceof Member)
            $memberID = $member->ID;
        else if(is_numeric($member))
            $memberID = $member;
        else
            $memberID = Member::currentUserID();

        if($memberID && Permission::checkMember($memberID, array("ADMIN", "COMMERCE_EDIT_ORDERS")))
            return true;

        return false;
    }

    /**
     * No one should be able to delete an order once it has been created
     *
     * @return Boolean
     */
    public function canDelete($member = null) {
        $extended = $this->extend('canDelete', $member);
        if($extended && $extended !== null) return $extended;

        return false;
    }
}
