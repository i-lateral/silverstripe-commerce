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
    
    public static $casting = array(
        'CustomDetails'    => 'HTMLText'
    );
    
    public static $summary_fields = array(
        'Title',
        'SKU',
        'CustomDetails',
        'Quantity',
        'Total'
    );
    
    public function getTotal() {
        return $this->Quantity * $this->Price;
    }

    /**
     * Unserialise the list of customisations and rendering into a basic HTML
     * string
     *
     */
    public function getCustomDetails() {
        $return = "";
        
        if($this->Customisation) {
            $customisations = unserialize($this->Customisation);
            
            foreach($customisations as $custom) {
                $return .= $custom->Title . ': ' . $custom->Value . ";\n";
            }
        }
        
        return $return;
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
