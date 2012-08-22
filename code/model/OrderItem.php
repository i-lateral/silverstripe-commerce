<?php
/**
 * OrderItem is a physical component of an order, that describes a product
 *
 * @author morven
 */
class OrderItem extends DataObject {
    public static $db = array(
        'Type'      => 'Varchar',
        'Quantity'  => 'Int',
        'Price'     => 'Currency',
        'Colour'    => 'Varchar',
        'TagOne'    => 'Text',
        'TagTwo'    => 'Text'
    );
    
    public static $has_one = array(
        'Parent'    => 'Order'
    );
    
    public static $summary_fields = array(
        'Type'      => 'Type',
        'Quantity'  => 'Qty',
        'TagOne'    => 'Tag One Copy',
        'TagTwo'    => 'Tag Two Copy',
        'Colour'    => 'Colour'
    );
    
    public function getTotal() {
        return $this->Quantity * $this->Price;
    }
}