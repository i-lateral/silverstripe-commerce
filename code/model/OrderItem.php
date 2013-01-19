<?php
/**
 * OrderItem is a physical component of an order, that describes a product
 *
 * @author morven
 */
class OrderItem extends DataObject {
    public static $db = array(
        'Title'         => 'Varchar',
        'SKU'           => 'Varchar(100)',
        'Type'          => 'Varchar',
        'Customisation' => 'Text',
        'Quantity'      => 'Int',
        'Price'         => 'Currency'
    );
    
    public static $has_one = array(
        'Parent'    => 'Order'
    );
    
    public static $summary_fields = array(
        'Title',
        'SKU',
        'Customisation',
        'Quantity',
        'Total'
    );
    
    public function getTotal() {
        return $this->Quantity * $this->Price;
    }

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}
