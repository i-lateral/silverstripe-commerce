<?php

class ProductCustomisation extends DataObject {
    public static $db = array(
        'Title'     => 'Varchar',
        'Required'  => 'Boolean',
        'DisplayAs' => "Enum('Dropdown,Radio,Checkboxes','Dropdown')",
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

	public static $default_sort = "\"Sort\" DESC";
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $fields->removeByName('Options');
        $fields->removeByName('ParentID');
        
        if($this->ID) {
			$field_types = singleton('ProductCustomisationOption')->getFieldTypes();
		    $options_field = new StackedTableField('Options', 'ProductCustomisationOption', null, $field_types);
            $fields->addFieldToTab('Root.Main', $options_field);
        } else {
            $fields->addFieldToTab('Root.Main',LiteralField::create('CreateWarning','<p>You need to create this before you can add options</p>'));
        }
        
        $this->extend('updateCMSFields', $fields);
        
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
			
			$this->extend('updateField', $field);
			
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
        'ModifyPrice'   => 'Decimal',
		'Sort'			=> 'Int',
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
        'ModifyPrice',
        'Default'
    );
    
    public static $field_types = array(
		'Title'         => 'TextField',
		'Sort'         	=> 'Int',
		'ModifyPrice'   => 'TextField',
		'Default'  		=> 'CheckboxField'
    );

	public static $default_sort = "\"Sort\" DESC";
    
    /**
     * Use this method to get a full list of field types
     * (for use in table fields)
     *  
     * @return Array of field names and types
     */
    public function getFieldTypes() {
		$fields = self::$field_types;
		
		$this->extend('updateFieldTypes', $fields);
		
		return $fields;
	}
    
    public function getItemSummary() {
        $config = SiteConfig::current_site_config();
        
        $summary = $this->Title;
        $summary .= ($this->ModifyPrice != 0) ? ' <span class="modify-price">' . $config->Currency()->HTMLNotation . $this->ModifyPrice . '</span>' : '';
        
		$this->extend('updateItemSummary', $summary);
		
        return $summary;
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
