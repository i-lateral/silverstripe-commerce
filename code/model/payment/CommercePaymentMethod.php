<?php

/**
 * 'Abstract' class that you will extend to add payment providers
 * These will automatically be added under the "Payments" tab in
 * Settings, inside the CMS
 *
 *
 */
class CommercePaymentMethod extends DataObject {

    /**
     * Shall this class appear in the list of payment providers. Use this to
     * hide "high level" classes that you intend to extend
     */
    public static $hidden = false;

    /**
     * The controller this is mapped to
     */
    public static $handler;

    /**
     * Title of this payment method (eg: PayPal, WorldPay, etc)
     */
    public $Title;

    /**
     * Route to icon that is associated with this provider
     */
    public $Icon;

    public static $db = array(
        // Payment Gateway config
        'Summary'           => 'Text',
        'URL'               => 'Varchar(200)',
        'GatewayMessage'    => 'Text',
        'Default'           => 'Boolean'
    );

    public static $has_one = array(
        'ParentConfig'  => 'SiteConfig'
    );

    public static $casting = array(
        'Label' => 'Text'
    );

    public static $summary_fields = array(
        'Title',
        'Summary',
        'Default'
    );

    /*
     * Combine this objects summary and it's icon, if it has one.
     *
     * @return String
     */
    public function getLabel() {
        return ($this->Icon) ? '<img class="payment-icon" src="'. $this->Icon .'" /> <span>' . $this->Summary . '</span>' : "<span>{$this->Summary}</span>";
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName('ParentConfigID');

        // Setup Payment Gateway type
        $payments = ClassInfo::subclassesFor('CommercePaymentMethod');
        // Remove parent class from list
        unset($payments['CommercePaymentMethod']);

        // Check if any payment types have been hidden and unset
        foreach($payments as $payment_type) {
            if($payment_type::$hidden)
                unset($payments[$payment_type]);
        }

        $classname_field = DropdownField::create('ClassName','Type of Payment',$payments)
            ->setHasEmptyDefault(true)
            ->setEmptyString('Select Gateway');

        $fields->addFieldToTab('Root.Main', $classname_field);

        if($this->ID) {
            $fields->addFieldToTab('Root.Main', TextField::create('Summary', 'Summary message to appear on website'));
            $fields->addFieldToTab('Root.Main', TextField::create('URL', 'Payment gateway URL'));
            $fields->addFieldToTab('Root.Main', CheckboxField::create('Default', 'Default payment method?'));
            $fields->addFieldToTab('Root.Main', TextareaField::create('GatewayMessage','Message to appear when user user is directed to payment provider'));
        } else {
            $fields->removeByName('URL');
            $fields->removeByName('Summary');
            $fields->removeByName('Default');
            $fields->removeByName('GatewayMessage');
        }

        return $fields;
    }

    // Get relevent payment gateway URL to use in HTML form
    public function GatewayURL() {
            return $this->URL;
    }
}
