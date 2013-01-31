<?php

class ProductCustomisation extends DataObject {
    public static $db = array(
        'Title'     => 'Varchar',
        'DisplayAs' => "Enum('Dropdown,Radio,Checkboxes','Dropdown')",
        'Required'  => 'Boolean',
        'Sort'      => 'Int'
    );
    
    public static $has_one = array(
        'Parent'    => 'Product'
    );
    
    public static $has_many = array(
        'Options'   => 'ProductCustomisationOption'
    );
    
    public static $summary_fields = array(
        'Title',
        'DisplayAs',
        'Sort'
    );

	public static $default_sort = "\"Sort\"";
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $fields->removeByName('Options');
        $fields->removeByName('ParentID');
        
        if($this->ID) {
            $field_types = array(
                'Title'         => 'TextField',
                'Detail'        => 'TextField',
                'Quantity'      => 'NumericField',
                'ModifyPrice'   => 'TextField',
                'ModifyWeight'  => 'TextField',
                'Default'  		=> 'CheckboxField'
            );
            
		    $options_field = new StackedTableField('Options', 'ProductCustomisationOption', null, $field_types);
            $fields->addFieldToTab('Root.Main', $options_field);
        } else {
            $fields->addFieldToTab('Root.Main',LiteralField::create('CreateWarning','<p>You need to create this before you can add options</p>'));
        }
        return $fields;
    }
    
    public function DefaultOptions() {
		return $this->Options()->filter('Default', 1);
	}
    
    /**
	 * Method that turns this object into a field type, to be loaded into a form
	 * 
	 * @return FormField
	 */
	public function Field() {
		if($this->Title && $this->DisplayAs) {
			$name = 'customise_' . Convert::raw2url($this->Title);
			$title = ($this->Required) ? $this->Title . ' *' : $this->Title;
			$options = $this->Options()->map('ID','ItemSummary');
			$defaults = $this->DefaultOptions();
			$default = ($defaults->first()) ? $defaults->first()->ID : 0;
			
			switch($this->DisplayAs) {
				case 'Dropdown':
					$field = DropdownField::create($name, $title, $options, $default)
								->setEmptyString(_t('Commerce.PLEASESELECT','Please Select')
					);
					break;
				case 'Radio':
					$field = OptionSetField::create($name, $title, $options, $default);
					break;
				case 'Checkboxes':
					$field = CheckboxSetField::create($name, $title, $options, $defaults->column('ID'));
					break;
			}
			
			return $field;
		} else
			return false;
	}
    
    public function onBeforeDelete() {
        // Delete all options when this opbect is deleted
        foreach($this->Options() as $option) {
            $option->delete();
        }
        
        parent::onBeforeDelete();
    }
	
    public function canView($member = false) {
        return $this->Parent()->canView($member);
    }

    public function canCreate($member = null) {
        return $this->Parent()->canCreate($member);
    }

    public function canEdit($member = null) {
        return $this->Parent()->canEdit($member);
    }

    public function canDelete($member = null) {
        return $this->Parent()->canDelete($member);
    }
}

class ProductCustomisationOption extends DataObject {
    public static $db = array(
        'Title'         => 'Varchar',
        'Detail'        => 'Varchar',
        'Quantity'      => 'Int',
        'ModifyPrice'   => 'Decimal',
        'ModifyWeight'  => 'Decimal',
        'Default'		=> 'Boolean'
    );
    
    public static $has_one = array(
        "Parent"        => 'ProductCustomisation'
    );
    
    public static $casting = array(
        'ItemSummary'   => 'Varchar'
    );
    
    public static $summary_fields = array(
        'Title',
        'Detail',
        'Quantity',
        'ModifyPrice',
        'ModifyWeight',
        'Default'
    );
    
    public function getItemSummary() {
        $config = SiteConfig::current_site_config();
        
        $return = $this->Title;
        $return .= ($this->Detail) ? ' <span class="detail">' . $this->Detail : '</span>';
        $return .= ($this->ModifyPrice != 0) ? ' <span class="modify-price">(' . $config->Currency()->HTMLNotation . $this->ModifyPrice . ')</span>' : '';
        
        return $return;
    }
	
    public function canView($member = false) {
        return $this->Parent()->canView($member);
    }

    public function canCreate($member = null) {
        return $this->Parent()->canCreate($member);
    }

    public function canEdit($member = null) {
        return $this->Parent()->canEdit($member);
    }

    public function canDelete($member = null) {
        return $this->Parent()->canDelete($member);
    }
}
