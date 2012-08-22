<?php
/**
 * Postage objects list available postage costs and destination locations
 *
 * @author morven
 */
class PostageArea extends DataObject {
    public static $db = array(
        'Location'  => 'Varchar',
        'Cost'      => 'Decimal'
    );
    
    public static $has_one = array(
        'Parent'    => 'CartPage'
    );
    
    public static $summary_fields = array(
        'Location'  => 'Location',
        'Cost'      => 'Cost'
    );
    
    public static $field_types = array(
        'Location'  => 'TextField',
        'Cost'      => 'TextField'
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