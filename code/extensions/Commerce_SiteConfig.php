<?php
/**
 * Description of Commerce_Subsite
 *
 * @author morven
 */
class Commerce_SiteConfig extends DataExtension {
    public function extraStatics($class = null, $extension = null) {
        return array(
            'db' => array(
                // Commerce Configs
                'SuccessCopy'       => 'Text',
                'FailerCopy'        => 'Text',
                'OrderPrefix'       => 'Varchar(9)',
                'CartCopy'          => 'HTMLText',
                
                // Payment Gateway config
                'SagePayURL'        => 'Varchar(100)',
                'SagePayVendor'     => 'Varchar(100)',
                'SagePayEmail'      => 'Varchar(100)',
                'SagePayPass'       => 'Varchar(100)',
                'SagePaySendEmail'  => "Enum('0,1,2','1')",
                'GatewayMessage'	=> 'Text'
            ),
            'has_one' => array(
                'Currency' => 'CommerceCurrency'
            ),
            'has_many' => array(
                'PostageAreas'      => 'PostageArea'
            )
        );
    }
    
    public function updateCMSFields(FieldList $fields) {
        // Ecommerce Fields
        $fields->addFieldToTab('Root.Main', new TextField('OrderPrefix', 'Short code that can appear at the start of order numbers', null, 9));
        $fields->addFieldToTab('Root.Main', new TextAreaField('SuccessCopy', 'Content to appear on order success page'));
        $fields->addFieldToTab('Root.Main', new TextAreaField('FailerCopy', 'Content to appear on order failer page'));
		
		$currency_map = CommerceCurrency::get()->map();
		$currency_map->unshift('0','Please Select');
		
    	// Add dropdown to manage currency relations
    	$fields->addFieldToTab('Root.Currency', new DropdownField('CurrencyID', null, $currency_map, $this->owner->CurrencyID));
		
		// Add currency grid field to manage currencies
        $currency_table = new GridField('AllCurrencies','Currencies on this site',CommerceCurrency::get(), GridFieldConfig_RecordEditor::create());
        
		$fields->addFieldToTab('Root.Currency', new HeaderField('CurrencyHeader', 'All currencies available on site', 3));
        $fields->addFieldToTab('Root.Currency', $currency_table);
		
		// Postage
        $postage_table = new GridField('PostageAreas','PostageArea',$this->owner->PostageAreas(), GridFieldConfig_RecordEditor::create());
		
        $fields->addFieldToTab('Root.Postage', $postage_table);
		
		// Shopping Cart
        $fields->addFieldToTab('Root.Cart', new HtmlEditorField('CartCopy', 'Copy to appear above shopping cart', 10));
        
        // Payment Gateway Options
        $email_options = array(
            "Don't",
            'Send to customer and vendor',
            'Send only to vendor'
        );
        
        $fields->addFieldToTab('Root.Payments', new TextField('SagePayURL', 'SagePay payment URL'));
        $fields->addFieldToTab('Root.Payments', new TextField('SagePayVendor', 'SagePay vendor name'));
        $fields->addFieldToTab('Root.Payments', new TextField('SagePayEmail', 'SagePay email address'));
        $fields->addFieldToTab('Root.Payments', new OptionsetField('SagePaySendEmail', 'How would you like SagePay to send emails?', $email_options));
        $fields->addFieldToTab('Root.Payments', new PasswordField('SagePayPass', 'SagePay encrypted password'));
		$fields->addFieldToTab('Root.Payments', new TextareaField('GatewayMessage','Message to appear when user user is directed to payment provider'));
    }
}