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
     * Title of this payment method (eg: PayPal, WorldPay, etc)
     *
     */
    public $Title;
    
    /**
     * Summary that will appear when the user enters their details
     *
     */
    public $Summary;
    
    public static $db = array(        
        // Payment Gateway config
        'LiveURL'           => 'Varchar(100)',
        'DevURL'            => 'Varchar(100)',
        'AccountName'       => 'Varchar(100)',
        'UserName'          => 'Varchar(100)',
        'Password'          => 'Varchar(100)',
        'Default'           => 'Boolean'
    );
    
    public static $has_one = array(
        'ParentConfig'  => 'SiteConfig'
    ); 
    
    public static $summary_fields = array(
        'Title'     => 'Title',
        'Summary'   => 'Summary',
        'Default'   => 'Default payment method'
    );
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $fields->removeByName('ParentConfigID');
        
		// Setup Payment Gateway type
		$payments = ClassInfo::subclassesFor('CommercePaymentMethod');
		// Remove parent class from list
		unset($payments['CommercePaymentMethod']);
		
		$classname_field = DropdownField::create('ClassName','Type of Payment',$payments)
		    ->setHasEmptyDefault(true)
		    ->setEmptyString('Select Gateway');
	    
	    $fields->addFieldToTab('Root.Main', $classname_field);
	    
	    if($this->ID) {
            $fields->addFieldToTab('Root.Main', TextField::create('LiveURL', 'Live payment URL'));
            $fields->addFieldToTab('Root.Main', TextField::create('DevURL', 'Development payment URL'));
            $fields->addFieldToTab('Root.Main', TextField::create('AccountName', 'Account name'));
            $fields->addFieldToTab('Root.Main', TextField::create('UserName', 'Account UserName (if different)'));
            $fields->addFieldToTab('Root.Main', PasswordField::create('Password', 'Password'));
            $fields->addFieldToTab('Root.Main', CheckboxField::create('Default', 'Default payment method?'));
        } else {
            $fields->removeByName('LiveURL');
            $fields->removeByName('DevURL');
            $fields->removeByName('AccountName');
            $fields->removeByName('UserName');
            $fields->removeByName('Password');
        }
        
        return $fields;
    }
    
    /**
     * Return a string that will be loaded into the summary form
     *
     * @return String
     */
    public function GatewayData() {
        user_error('You have not added a GatewayData() method on your PaymentMethod Class');
    }
}
