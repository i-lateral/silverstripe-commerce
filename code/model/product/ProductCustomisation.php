<?php

class ProductCustomisation extends DataObject {
    private static $db = array(
        'Title'     => 'Varchar',
        'Required'  => 'Boolean',
        'DisplayAs' => "Enum('Dropdown,Radio,Checkboxes','Dropdown')",
        'Sort'      => 'Int'
    );

    private static $has_one = array(
        'Parent'    => 'Product'
    );

    private static $has_many = array(
        'Options'   => 'ProductCustomisationOption'
    );

    private static $summary_fields = array(
        'Title',
        'DisplayAs'
    );

    private static $default_sort = "\"Sort\" DESC";

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName('Options');
        $fields->removeByName('ParentID');
        $fields->removeByName('Sort');

        if($this->ID) {
            $field_types = singleton('ProductCustomisationOption')->getFieldTypes();

            // Deal with product features
            $add_button = new GridFieldAddNewInlineButton('toolbar-header-left');
            $add_button->setTitle('Add Customisation Option');

            $options_field = new GridField(
                'Options',
                '',
                $this->Options(),
                GridFieldConfig::create()
                    ->addComponent(new GridFieldButtonRow('before'))
                    ->addComponent(new GridFieldToolbarHeader())
                    ->addComponent(new GridFieldTitleHeader())
                    ->addComponent(new GridFieldEditableColumns())
                    ->addComponent(new GridFieldDeleteAction())
                    ->addComponent($add_button)
                    ->addComponent(new GridFieldOrderableRows('Sort'))
            );

            $fields->addFieldToTab('Root.Main', $options_field);
        } else {
            $fields->addFieldToTab('Root.Main',LiteralField::create('CreateWarning','<p>You need to create this before you can add options</p>'));
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    // Get the default options for this customisation
    public function DefaultOptions() {
        $options = $this->Options()->filter('Default', 1);

        $this->extend('updateDefaultOptions', $options);

        return $options;
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
    private static $db = array(
        'Title'         => 'Varchar',
        'ModifyPrice'   => 'Decimal',
        'Sort'          => 'Int',
        'Default'       => 'Boolean'
    );

    private static $has_one = array(
        "Parent"        => 'ProductCustomisation'
    );

    private static $casting = array(
        'ItemSummary'   => 'Varchar'
    );

    private static $summary_fields = array(
        'Title',
        'ModifyPrice',
        'Default'
    );

    private static $field_types = array(
        'Title'         => 'TextField',
        'Sort'          => 'Int',
        'ModifyPrice'   => 'TextField',
        'Default'       => 'CheckboxField'
    );

    private static $default_sort = "\"Sort\" DESC";

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
        $summary .= ($this->ModifyPrice != 0) ? ' (' . $config->Currency()->HTMLNotation . $this->ModifyPrice . ')' : '';

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
