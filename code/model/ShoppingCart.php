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
     * @param Customise array of custom options for this product, needs to be a
     *        multi dimensional array with each item of format:
     *          -  "Title" => (str)"Item title"
     *          -  "Value" => (str)"Item Value"
     *          -  "ModifyPrice" => (float)"Modification to price"
     */
    public function add(Product $add_item, $quantity = 1, $customise = array()) {
        $added = false;
        $config = SiteConfig::current_site_config();

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
            (float)$tax_rate = $config->TaxRate;

            foreach($customise as $custom_item) {
                $custom_data->add(new ArrayData(array(
                    'Title' => ucwords(str_replace(array('-','_'), ' ', $custom_item["Title"])),
                    'Value' => $custom_item["Value"],
                    'ModifyPrice' => $custom_item['ModifyPrice']
                )));

                // If a customisation modifies price, adjust the price
                if($custom_item['ModifyPrice']) $price = (float)$price + (float)$custom_item['ModifyPrice'];
            }

            // Now, caclulate tax based on the new modified price and tax rate
            if($tax_rate > 0)
                (float)$tax = ($price / 100) * $tax_rate; // Get our tax amount from the price
            else
                (float)$tax = 0;

            $this->items->add(new ArrayData(array(
                'Key'           => $product_key,
                'ProductID'     => $add_item->ID,
                'Title'         => $add_item->Title,
                'SKU'           => $add_item->SKU,
                'Description'   => $add_item->Description,
                'Weight'        => $add_item->Weight,
                'Price'         => number_format($price,2),
                'Tax'           => number_format($tax, 2),
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
     * Find the cost of all items in the cart, without any tax.
     *
     * @return Float
     */
    public function SubTotalPrice() {
        $total = 0;

        foreach($this->Items() as $item) {
            $total = $total + ($item->Quantity * $item->Price);
        }

        return  number_format($total,2);
    }

    /**
     * Find the total cost of tax for all items in the cart.
     *
     * @return Float
     */
    public function TaxTotalPrice() {
        $config = SiteConfig::current_site_config();
        $total = 0;

        if($config->TaxRate > 0) {
            foreach($this->Items() as $item) {
                if($item->Tax > 0) $total += ($item->Quantity * $item->Tax);
            }
        }

        return  number_format($total,2);
    }

    /**
     * Find the total price of items in the shopping cart, including tax.
     *
     * @return Float
     */
    public function TotalPrice() {
        return number_format($this->SubTotalPrice() + $this->TaxTotalPrice(), 2);
    }

    /**
     * Save the current products list to a session.
     *
     */
    public function save() {
        Session::set("commerce.shoppingcart",$this->items);
    }


}
