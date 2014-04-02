<?php
/**
 * Description of Commerce_Subsite
 *
 * @author morven
 */
class Ext_Commerce_SiteConfig extends DataExtension {
    private static $db = array(
        // Commerce Configs
        'ContactEmail'          => 'Varchar(100)',
        'ContactPhone'          => 'Varchar(50)',
        'SuccessCopy'           => 'Text',
        'FailerCopy'            => 'Text',
        'OrderPrefix'           => 'Varchar(9)',
        'CartCopy'              => 'HTMLText',
        'EmailFromAddress'      => "Text",
        'SendFailedEmail'       => "Enum('No,Customer,Vendor,Both','No')",
        'FailedEmailAddress'    => "Text",
        'SendPaidEmail'         => "Enum('No,Customer,Vendor,Both','No')",
        'PaidEmailAddress'      => "Text",
        'SendProcessingEmail'   => "Enum('No,Customer,Vendor,Both','No')",
        'ProcessingEmailAddress'=> "Text",
        'SendDispatchedEmail'   => "Enum('No,Customer,Vendor,Both','Customer')",
        'DispatchedEmailAddress'=> "Text",
        'VendorEmailFooter'     => "Text"
    );

    private static $has_one = array(
        'NoProductImage'    => 'Image',
        'Currency'          => 'CommerceCurrency',
        'Weight'            => 'ProductWeight'
    );

    private static $has_many = array(
        'PostageAreas'      => 'PostageArea',
        'PaymentMethods'    => 'CommercePaymentMethod'
    );

    public function sendCommerceEmail($recipient, $status) {
        if($recipient == 'Customer')
                $array = array('Customer', 'Both');
        elseif($recipient == 'Vendor')
                $array = array('Vendor', 'Both');
        else
                $array = array();

        if($status == 'paid' && in_array($this->owner->SendPaidEmail, $array))
                return true;
        elseif($status == 'failed' && in_array($this->owner->SendFailedEmail, $array))
                return true;
        elseif($status == 'processing' && in_array($this->owner->SendProcessingEmail, $array))
                return true;
        elseif($status == 'dispatched' && in_array($this->owner->SendDispatchedEmail, $array))
                return true;
        else
                return false;
    }

    public function updateCMSFields(FieldList $fields) {
        $fields->removeByName('ContactEmail');
        $fields->removeByName('ContactPhone');

        // Ecommerce Fields

        // Compress default commerce settings
        $contact_fields = ToggleCompositeField::create(
            'ContactDetails',
            'Contact Details',
            array(
                TextField::create('ContactEmail', 'Email Address'),
                TextField::create('ContactPhone', 'Phone Number')
            )
        )->setHeadingLevel(4);

        // Compress default commerce settings
        $settings_fields = ToggleCompositeField::create(
            'CommerceSettings',
            'Default Settings',
            array(
                TextField::create('OrderPrefix', 'Short code that can appear at the start of order numbers', null, 9),
                DropdownField::create('CurrencyID', 'Currency to use', CommerceCurrency::get()->map(), $this->owner->CurrencyID)->setEmptyString('Please Select'),
                DropdownField::create('WeightID', 'Weight to use', ProductWeight::get()->map(), $this->owner->WeightID)->setEmptyString('Please Select'),
                UploadField::create('NoProductImage','Overwrite default "image unavailable" image')
            )
        )->setHeadingLevel(4);


        // Compress shopping cart settings
        $cart_fields = ToggleCompositeField::create(
            'CartProcess',
            'Cart and Checkout Content',
            array(
                HtmlEditorField::create('CartCopy', 'Shopping cart')->setRows(15)->addExtraClass('stacked'),
                TextAreaField::create('SuccessCopy', 'Order success page')->setRows(4)->setColumns(30)->addExtraClass('stacked'),
                TextAreaField::create('FailerCopy', 'Order failer page')->setRows(4)->setColumns(30)->addExtraClass('stacked')
            )
        )->setHeadingLevel(4);

        // Compress email alerts
        $email_fields = ToggleCompositeField::create(
            'EmailAlerts',
            'Email Alerts',
            array(
                TextField::create('EmailFromAddress', 'Send commerce notifications from?'),
                LiteralField::create('OrderPlacedHeader', '<div class="field"><h4>When an order is placed</h4></div>'),
                DropdownField::create('SendPaidEmail', 'Send emails to', $this->owner->dbObject('SendPaidEmail')->enumValues()),
                TextField::create('PaidEmailAddress', 'Vendor address'),
                LiteralField::create('OrderFailedHeader', '<div class="field"><h4>When an order fails</h4></div>'),
                DropdownField::create('SendFailedEmail', 'Send emails to', $this->owner->dbObject('SendPaidEmail')->enumValues()),
                TextField::create('FailedEmailAddress', 'Vendor address'),
                LiteralField::create('OrderProcessingHeader', '<div class="field"><h4>When an order is marked as processing</h4></div>'),
                DropdownField::create('SendProcessingEmail', 'Send emails to', $this->owner->dbObject('SendProcessingEmail')->enumValues()),
                TextField::create('ProcessingEmailAddress', 'Vendor address'),
                LiteralField::create('OrderPlacedHeader', '<div class="field"><h4>When an order is marked as dispatched</h4></div>'),
                DropdownField::create('SendDispatchedEmail', 'Send emails to', $this->owner->dbObject('SendDispatchedEmail')->enumValues()),
                TextField::create('DispatchedEmailAddress', 'Vendor address'),
                LiteralField::create('FooterContent', '<div class="field"><h4>Footer Content</h4></div>'),
                TextareaField::create('VendorEmailFooter', 'Add custom content to the footer of vendor emails?')
            )
        )->setHeadingLevel(4);

        // Deal with product features
        $postage_field = new GridField(
            'PostageAreas',
            '',
            $this->owner->PostageAreas(),
            GridFieldConfig::create()
                ->addComponent(new GridFieldButtonRow('before'))
                ->addComponent(new GridFieldToolbarHeader())
                ->addComponent(new GridFieldTitleHeader())
                ->addComponent(new GridFieldEditableColumns())
                ->addComponent(new GridFieldDeleteAction())
                ->addComponent(new GridFieldAddNewInlineButton('toolbar-header-left'))
        );

        // Setup compressed postage options
        $postage_fields = ToggleCompositeField::create(
            'PostageFields',
            'Postage Options',
            array($postage_field)
        );

        // Payment Methods
        $payment_table = GridField::create(
            'PaymentMethods',
            'CommercePaymentMethod',
            $this->owner->PaymentMethods(),
            GridFieldConfig::create()->addComponents(
                new GridFieldToolbarHeader(),
                new GridFieldAddNewButton('toolbar-header-right'),
                new GridFieldSortableHeader(),
                new GridFieldDataColumns(),
                new GridFieldPaginator(20),
                new GridFieldEditButton(),
                new GridFieldDeleteAction(),
                new GridFieldDetailForm()
            )
        );

        // setup compressed payment options
        $payment_fields = ToggleCompositeField::create(
            'PaymentFields',
            'Payment Options',
            array($payment_table)
        );

        // Add config sets
        $fields->addFieldToTab('Root.Commerce', $contact_fields);
        $fields->addFieldToTab('Root.Commerce', $settings_fields);
        $fields->addFieldToTab('Root.Commerce', $cart_fields);
        $fields->addFieldToTab('Root.Commerce', $email_fields);
        $fields->addFieldToTab('Root.Commerce', $postage_fields);
        $fields->addFieldToTab('Root.Commerce', $payment_fields);
    }

    public function requireDefaultRecords() {

        // If "no product image" is not in DB, add it
        if(!Image::get()->filter('Name','no-image.png')->first()) {
            $image = new Image();
            $image->Name = 'no-image.png';
            $image->Title = 'No Image';
            $image->Filename = 'commerce/images/no-image.png';
            $image->ShowInSearch = 0;
            $image->write();

            DB::alteration_message('No image file added to DB', 'created');
        }
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        // If product image has not been set, add the default
        if(!$this->owner->NoProductImageID) {
            $image = Image::get()
                ->filter('Name','no-image.png')
                ->first();

            if($image) {
                $this->owner->NoProductImageID = $image->ID;
            }
        }
    }
}
