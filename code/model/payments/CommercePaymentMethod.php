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
    
    public static $db = array(        
        // Payment Gateway config
        'Summary'           => 'Text',
        'LiveURL'           => 'Varchar(100)',
        'DevURL'            => 'Varchar(100)',
        'GatewayMessage'	=> 'Text',
        'Default'           => 'Boolean'
    );
    
    public static $has_one = array(
        'ParentConfig'  => 'SiteConfig'
    ); 
    
    public static $summary_fields = array(
        'Title',
        'Summary',
        'Default'
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
            $fields->addFieldToTab('Root.Main', TextField::create('Summary', 'Summary message to appear on website'));
            $fields->addFieldToTab('Root.Main', TextField::create('LiveURL', 'Live payment URL'));
            $fields->addFieldToTab('Root.Main', TextField::create('DevURL', 'Development payment URL'));
            $fields->addFieldToTab('Root.Main', CheckboxField::create('Default', 'Default payment method?'));
		    $fields->addFieldToTab('Root.Main', TextareaField::create('GatewayMessage','Message to appear when user user is directed to payment provider'));
        } else {
            $fields->removeByName('LiveURL');
            $fields->removeByName('DevURL');
        }
        
        return $fields;
    }
    
    // Get relevent payment gateway URL to use in HTML form
    public function GatewayURL() {
        if(Director::isDev())
            return $this->DevURL;
        else
            return $this->LiveURL;
    }
    
    /**
     * Return a form that will be loaded into the Payment template and will post
     * to the payment gateway provider.
     *
     * @return Form
     */
    public function getGatewayFields() {
        user_error('You have not added a GatewayFields() method on your PaymentMethod Class');
    }
    
    /**
     * Return a form that will be loaded into the Payment template and will post
     * to the payment gateway provider.
     *
     * @return Form
     */
    public function getGatewayActions() {
        $actions = new FieldList(
            LiteralField::create('BackButton','<a href="' . BASE_URL . '/' . Checkout_Controller::$url_segment . '" class="action">' . _t('Commerce.BACK','Back') . '</a>'),
            FormAction::create('Submit', _t('Commerce.CONFIRMPAY','Confirm and Pay'))
        );
        
        return $actions;
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
