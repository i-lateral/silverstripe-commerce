<?php
/**
 * Description of TagColour
 *
 * @author morven
 */
class TagColour extends DataObject {
    public static $db = array(
        'Title' => 'Varchar'
    );
    
    public static $has_one = array(
        'Preview'   => 'Image'
    );
    
    public static $summary_fields = array(
        'Title'
    );
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $fields->addFieldToTab('Root.Main', new ImageField('Preview', null, null, null, null, 'TagColours'));
        
        return $fields;
    }
    
    public function canCreate() {
        return true;
    }

    public function canEdit() {
        return true;
    }

    public function canDelete() {
        return true;
    }
}