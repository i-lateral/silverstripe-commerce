<?php
/**
 * Description of Commerce_Subsite
 *
 * @author morven
 */
class Commerce_SiteConfig extends DataExtension {
    public static $db = array(
        // Commerce Configs
        'SuccessCopy'       => 'Text',
        'FailerCopy'        => 'Text',
        'OrderPrefix'       => 'Varchar(9)',
        'CartCopy'          => 'HTMLText'
    );
    
    public static $has_one = array(
        'Currency'          => 'CommerceCurrency'
    );
    
    public static $has_many = array(
        'PostageAreas'      => 'PostageArea',
        'PaymentMethods'    => 'CommercePaymentMethod'
    );
    
    public function updateCMSFields(FieldList $fields) {
        // Ecommerce Fields
        $fields->addFieldToTab('Root.Main', TextField::create('OrderPrefix', 'Short code that can appear at the start of order numbers', null, 9));
        $fields->addFieldToTab('Root.Main', TextAreaField::create('SuccessCopy', 'Content to appear on order success page'));
        $fields->addFieldToTab('Root.Main', TextAreaField::create('FailerCopy', 'Content to appear on order failer page'));
		
		$currency_map = CommerceCurrency::get()->map();
		$currency_map->unshift('0','Please Select');
		
    	// Add dropdown to manage currency relations
    	$fields->addFieldToTab('Root.Currency', DropdownField::create('CurrencyID', null, $currency_map, $this->owner->CurrencyID));
		
		// Add currency grid field to manage currencies
        $currency_table = GridField::create('AllCurrencies','Currencies on this site',CommerceCurrency::get(), GridFieldConfig_RecordEditor::create());
        
		$fields->addFieldToTab('Root.Currency', HeaderField::create('CurrencyHeader', 'All currencies available on site', 3));
        $fields->addFieldToTab('Root.Currency', $currency_table);
		
		// Postage
        $postage_table = GridField::create('PostageAreas','PostageArea',$this->owner->PostageAreas(), GridFieldConfig_RecordEditor::create());
		
        $fields->addFieldToTab('Root.Postage', $postage_table);
        
		// Payment Methods
        $payment_table = GridField::create('PaymentMethods','CommercePaymentMethod',$this->owner->PaymentMethods(), GridFieldConfig_RecordEditor::create());
		
        $fields->addFieldToTab('Root.Payments', $payment_table);
		
		// Shopping Cart
        $fields->addFieldToTab('Root.Cart', HtmlEditorField::create('CartCopy', 'Copy to appear above shopping cart', 10));
    }
}
