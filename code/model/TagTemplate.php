<?php
/**
 * Description of TagTemplate
 *
 * @author morven
 */
class TagTemplate extends DataObject {
    public static $db = array(
        'Name'          => 'Varchar',
        'TagOneContent' => 'Text',
        'TagTwoContent' => 'Text'
    );
    
    public static $summary_fields = array(
        "ID"     => "ID Number",
        "Name"   => "Name"
    );

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