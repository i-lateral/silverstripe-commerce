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
        'Price'         => 'Currency',
        'Tax'           => 'Currency'
    );

    private static $has_one = array(
        'Parent'    => 'Order'
    );

    private static $casting = array(
        'CustomDetails' => 'HTMLText',
        'MatchProduct'  => 'Product',
        'SubTotal'      => 'Currency',
        'TaxTotal'      => 'Currency',
        'Total'         => 'Currency'
    );

    private static $summary_fields = array(
        'Title',
        'SKU',
        'CustomDetails',
        'Quantity',
        'Price',
        'Tax',
        'Total'
    );

    /**
     * Find any items in the product catalogue with a matching SKU, good for
     * adding "Order again" links in account panels or finding "Most ordered"
     * etc.
     *
     * @return Product
     */
    public function Product() {
        // If the SKU is set, and it matches a product, return product
        if($this->SKU && $product = Product::get()->filter("SKU", $this->SKU)->first())
            return $product;

        // If nothing has matched, return an empty product
        return Product::create();
    }

    /**
     * Get the product, this method is used by casting
     *
     * @return Product
     */
    public function getMatchProduct() {
        return $this->Product();
    }

    /**
     * Get the total cost of this item based on the quantity, not including tax
     *
     * @return Decimal
     */
    public function getSubTotal() {
        return $this->Quantity * $this->Price;
    }

    /**
     * Get the total cost of tax for this item based on the quantity
     *
     * @return Decimal
     */
    public function getTaxTotal() {
        return $this->Quantity * $this->Tax;
    }

    /**
     * Get the total cost of this item based on the quantity
     *
     * @return Currency
     */
    public function getTotal() {
        return $this->SubTotal + $this->TaxTotal;
    }

    /**
     * Unserialise the list of customisations and rendering into a basic
     * HTML string
     *
     */
    public function getCustomDetails() {
        $htmltext = HTMLText::create();
        $return = "";

        if($this->Customisation) {
            $customisations = unserialize($this->Customisation);

            foreach($customisations as $custom) {
                $return .= $custom->Title . ': ' . $custom->Value . ";<br/>";
            }
        }

        $htmltext->setValue($return);
        return $htmltext;
    }

    /**
     * Only order creators or users with VIEW admin rights can view
     *
     * @return Boolean
     */
    public function canView($member = null) {
        $extended = $this->extend('canView', $member);
        if($extended && $extended !== null) return $extended;

        if($member instanceof Member)
            $memberID = $member->ID;
        else if(is_numeric($member))
            $memberID = $member;
        else
            $memberID = Member::currentUserID();

        if($memberID && Permission::checkMember($memberID, array("ADMIN", "COMMERCE_VIEW_ORDERS")))
            return true;
        else if($memberID && $memberID == $this->CustomerID)
            return true;

        return false;
    }

    /**
     * Anyone can create an order item
     *
     * @return Boolean
     */
    public function canCreate($member = null) {
        $extended = $this->extend('canCreate', $member);
        if($extended && $extended !== null) return $extended;

        return true;
    }

    /**
     * No one can edit items once they are created
     *
     * @return Boolean
     */
    public function canEdit($member = null) {
        $extended = $this->extend('canEdit', $member);
        if($extended && $extended !== null) return $extended;

        return false;
    }

    /**
     * No one can delete items once they are created
     *
     * @return Boolean
     */
    public function canDelete($member = null) {
        $extended = $this->extend('canDelete', $member);
        if($extended && $extended !== null) return $extended;

        return false;
    }
}
