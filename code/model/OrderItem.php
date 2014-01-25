<?php
/**
 * OrderItem is a physical component of an order, that describes a product
 *
 * @author morven
 */
class OrderItem extends DataObject {
    private static $db = array(
        'Title'         => 'Varchar',
        'SKU'           => 'Varchar(100)',
        'Type'          => 'Varchar',
        'Customisation' => 'Text',
        'Quantity'      => 'Int',
        'Price'         => 'Currency'
    );

    private static $has_one = array(
        'Parent'    => 'Order'
    );

    private static $casting = array(
        'CustomDetails' => 'HTMLText',
        'MatchProduct'  => 'Product'
    );

    private static $summary_fields = array(
        'Title',
        'SKU',
        'CustomDetails',
        'Quantity',
        'Total'
    );

    /**
     * Get the total cost of this item based on the quantity
     *
     * @return Currency
     */
    public function getTotal() {
        return $this->Quantity * $this->Price;
    }

    /**
     * Find any items in the product catalogue with a matching SKU, good for
     * adding "Order again" links in account panels or finding "Most ordered"
     * etc.
     *
     * @return Product
     */
     public function getMatchProduct() {
        if($this->SKU)
            return Product::get()->filter("SKU",$this->SKU)->first();
        else
            return Product::create(); // Create an empry product to return
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
