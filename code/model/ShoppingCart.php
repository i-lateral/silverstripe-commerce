<?php

/**
 * Class used to store and retrieve products stored in the shopping cart session
 *
 * @packace commerce
 *
 */
class ShoppingCart extends ViewableData {

    /**
     * Determines if the shopping cart is currently enabled
     *
     */
    protected static $enabled = true;

    protected $items;

    public function __construct() {
        if(Session::get('commerce.shoppingcart'))
            $this->items = Session::get('commerce.shoppingcart');
        else
            $this->items = new ArrayList();
    }

    /**
     * Set the enabled switch
     *
     * @param Enabled boolean
     */
    public static function set_enabled($enabled = true) {
        self::$enabled = $enabled;
    }

    /**
     * find out if the shopping cart is enabled
     *
     * @return Boolean
     */
    public static function isEnabled() {
        return self::$enabled;
    }

    /**
     * Return the current shopping cart or a create a new one if none exists
     *
     * @return ShoppingCart
     */
    public static function get() {
        return new ShoppingCart();
    }

    /**
     * Get all items in the current shopping cart
     *
     */
    public function Items() {
        return $this->items;
    }

    /**
     * Add a product to the shopping cart via its ID number.
     *
     * @param Item Product Object you wish to add
     * @param Quantity number of this item to add
     * @param Customise array of custom options for this product
     */
    public function add(Product $add_item, $quantity = 1, $customise = array()) {
        $added = false;

        // Make a string to match id's against ones already in the cart
        $product_key = ($customise) ? (int)$add_item->ID . ':' . base64_encode(serialize($customise)) : (int)$add_item->ID;

        // Check if the add call is trying to add an item already in the cart,
        // if so update the current quantity
        foreach($this->items as $item) {
            // If an instance of this is already in the shopping basket, increase
            if($item->Key == $product_key) {
                $this->update($item->Key, ($item->Quantity + $quantity));
                $added = true;
            }
        }

        // If no update was sucessfull, update records
        if(!$added) {
            $custom_data = new ArrayList();
            $price = $add_item->Price;

            foreach($customise as $custom_key => $custom_value) {
                // Check if customisation is an automated database custom, or an overwrite
                if(is_array($custom_value)) {
                    $value = $custom_value['Value'];
                    $modify_price = $custom_value['Price'];
                } elseif(is_int((int)$custom_value) && $custom_item = ProductCustomisationOption::get()->byID($custom_value)) {
                    $value = $custom_item->Title;
                    $modify_price = $custom_item->ModifyPrice;
                }

                $custom_data->add(new ArrayData(array(
                    'Title' => ucwords(str_replace(array('-','_'), ' ', $custom_key)),
                    'Value' => $value,
                    'ModifyPrice' => $modify_price
                )));

                // If a customisation modifies price, adjust the price
                if($modify_price) $price = (float)$price + (float)$modify_price;
            }

            $this->items->add(new ArrayData(array(
                'Key'           => $product_key,
                'ProductID'     => $add_item->ID,
                'Title'         => $add_item->Title,
                'Description'   => $add_item->Description,
                'Weight'        => $add_item->Weight,
                'Price'         => $price,
                'Customised'    => ($custom_data) ? $custom_data : '',
                'ImageID'       => ($add_item->Images()->exists()) ? $add_item->Images()->first()->ID : null,
                'Quantity'      => $quantity
            )));
        }
    }

    /**
     * Find an existing item and update its quantity
     *
     * @param Item
     * @param Quantity
     */
    public function update($item_key, $quantity) {
        foreach($this->items as $item) {
            if ($item->Key === $item_key) {
                $item->Quantity = $quantity;
                return true;
            }
        }

        return false;
     }

    /**
     * Completly remove a product in the shopping cart.
     *
     * @param Item Product Object you wish to remove
     */
    public function remove($item_key) {
        foreach($this->items as $item) {
            if($item->Key == $item_key)
                $this->items->remove($item);
        }
    }

    /**
     * Empty the shopping cart object of all items.
     *
     */
    public function removeAll() {
        foreach($this->items as $item) {
            $this->remove($item);
        }
    }

    /**
     * Clear the shopping cart object and destroy the session. Different to
     * empty, as that retains the session.
     *
     */
    public function clear() {
        Session::clear('commerce.shoppingcart');
        unset($_SESSION['commerce.shoppingcart']);
    }

    /**
     * Find the total quantity of items in the shopping cart
     *
     */
    public function TotalItems() {
        $total = 0;

        foreach($this->Items() as $item) {
            $total = $total + $item->Quantity;
        }

        return $total;
    }

    /**
     * Find the total quantity of items in the shopping cart
     *
     */
    public function TotalPrice() {
        $total = 0;

        foreach($this->Items() as $item) {
            $total = $total + ($item->Quantity * $item->Price);
        }

        return  money_format('%i',$total);
    }

    /**
     * Save the current products list to a session.
     *
     */
    public function save() {
        Session::set("commerce.shoppingcart",$this->items);
    }


}
