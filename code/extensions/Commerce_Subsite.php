<?php
/**
 * Description of Commerce_Subsite
 *
 * @author morven
 */
class Commerce_Subsite extends DataObjectDecorator {
    public function extraStatics() {
        return array(
            'db' => array(
                // Name and Tagline
                'Name'              => 'Varchar',
                'Tagline'           => 'Varchar',
                
                // Generic Store Configs
                'FacebookURL'       => 'Varchar(100)',
                'TwitterURL'        => 'HTMLText',
                'ContactEmail'      => 'Varchar(100)',
                'EmailFrom'         => 'Varchar',
                'ContactPhone'      => 'Varchar(50)',
                
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
    
    public function updateCMSFields(FieldSet &$fields) {
        // Add further config fields
        $fields->addFieldToTab('Root.Configuration', new TextField('Name', 'Human friendly name for this site'), "Title");
        $fields->addFieldToTab('Root.Configuration', new TextField('Tagline', 'Tagline of this site'), "Title");
        $fields->removeByName('Title');
        $fields->addFieldToTab('Root.Configuration', new TextField('Title', 'Name of subsite:', $this->owner->Title), 'Tagline');
        
        // Contact details
        $fields->addFieldToTab('Root.Contact', new TextField('FacebookURL'));
        $fields->addFieldToTab('Root.Contact', new TextField('TwitterURL'));
        $fields->addFieldToTab('Root.Contact', new TextField('ContactEmail'));
        $fields->addFieldToTab('Root.Contact', new TextField('ContactPhone'));
        $fields->addFieldToTab('Root.Contact', new EmailField('EmailFrom', 'Email address to appear on emails sent from this site'));
        
        
        // Localisation
        $fields->removeByName('Language');
        $fields->addFieldToTab('Root.Location', new DropdownField('Language', 'Language', i18n::get_common_locales()));
        
        $currency_table = new HasOneComplexTableField(
            $this->owner,
            'Currency',
            'CommerceCurrency'
        );
        
        $fields->addFieldToTab('Root.Location', new HeaderField('CurrencyTitle', 'Local Currency', 3));
        $fields->addFieldToTab('Root.Location', $currency_table);
        
        $postage_table = new TableField(
            'PostageAreas',
            'PostageArea',
            PostageArea::$summary_fields,
            PostageArea::$field_types,
            null,
            "ParentID = '{$this->owner->ID}'"
        );
        
        $fields->addFieldToTab('Root.Location', new HeaderField('PostageTitle', 'Postage Areas', 3));
        $fields->addFieldToTab('Root.Location', $postage_table);
        
        // Ecommerce Fields
        $fields->addFieldToTab('Root.Commerce', new TextField('OrderPrefix', 'Short code that can appear at the start of order numbers', null, 9));
        $fields->addFieldToTab('Root.Commerce', new HtmlEditorField('CartCopy', 'Copy to appear above shopping cart', 10));
        $fields->addFieldToTab('Root.Commerce', new TextAreaField('SuccessCopy', 'Content to appear on order success page'));
        $fields->addFieldToTab('Root.Commerce', new TextAreaField('FailerCopy', 'Content to appear on order failer page'));
        
        // Payment Gateway Options
        $email_options = array(
            0 => "Don't",
            1 => 'Send to customer and vendor',
            2 => 'Send only to vendor'
        );
        
        $fields->addFieldToTab('Root.Payment', new TextField('SagePayURL', 'SagePay payment URL'));
        $fields->addFieldToTab('Root.Payment', new TextField('SagePayVendor', 'SagePay vendor name'));
        $fields->addFieldToTab('Root.Payment', new TextField('SagePayEmail', 'SagePay email address'));
        $fields->addFieldToTab('Root.Payment', new OptionsetField('SagePaySendEmail', 'How would you like SagePay to send emails?', $email_options));
        $fields->addFieldToTab('Root.Payment', new PasswordField('SagePayPass', 'SagePay encrypted password'));
		$fields->addFieldToTab('Root.Payment', new TextareaField('GatewayMessage','Message to appear when user user is directed to payment provider'));
    }
}