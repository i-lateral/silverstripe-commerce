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
    
    public function __construct(ArrayList $items) {
        $this->items = (Session::get('commerce-shoppingcart')) ? Session::get('commerce-shoppingcart') : new ArrayList();
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
        if (!$option)
      		$key = (int)$add_item->ID;
    	else
      		$key = (int)$add_item->ID . ':' . base64_encode(serialize($customise));
        
        foreach($this->items as $item) {
            // If an instance of this is already in the shopping basket, increase
            if($item->Key == $key) {
                $this->update($item->Key, ($item->Quantity + $quantity));
                $added = true;
            } 
        }
        
        // If no update was sucessfull, update records
        if(!$added) {
            $this->items->add(new ArrayData(array(
                'Key'           => $key,
                'ProductID'     => $add_item->ID,
                'Title'         => $add_item->Title,
                'Description'   => $add_item->Description,
                'Weight'        => $add_item->Weight,
                'Price'         => $add_item->Price,
                'Customised'    => ($customise) ? $customise : '',
                'ImageID'         => ($add_item->Images()->exists()) ? $add_item->Images()->first()->ID : null,
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
				return;
			}
		}
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
        Session::clear('commerce-shoppingcart');
        unset($_SESSION['commerce-shoppingcart']);
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
        Session::set("commerce-shoppingcart",$this->items);
    }
    
    
}
