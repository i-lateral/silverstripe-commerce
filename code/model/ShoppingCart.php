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
        $this->items = $items;
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
        // Check for existing session, or set empty list if none
        $session = (Session::get('ShoppingCart')) ? Session::get('ShoppingCart') : new ArrayList();
        
        return new ShoppingCart($session);
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
     */
    public function add(Product $add_item, $quantity = 1) {
        $added = false;  
        foreach($this->items as $item) {
            // If an instance of this is already in the shopping basket, increase
            if($item->Product->ID == $add_item->ID) {
                $this->update($item->Product, ($item->Quantity + $quantity));
                $added = true;
            } 
        }
        
        // If no update was sucessfull, update records
        if(!$added) {
            $this->items->add(new ArrayData(array(
                'Product'   => $add_item,
                'Quantity'  => $quantity
            )));
        }
    }
    
    /**
     * Find an existing item and update its quantity
     *
     * @param Item 
     * @param Quantity
     */ 
    public function update(Product $update_item, $quantity) {
        foreach($this->items as $item) {
			if ($item->Product->ID === $update_item->ID) {
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
    public function remove(Product $remove_item) {
        foreach($this->items as $item) {
            if($item->Product->ID == $remove_item->ID)
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
        Session::clear('ShoppingCart');
        unset($_SESSION['ShoppingCart']);
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
            $total = $total + ($item->Quantity * $item->Product->Price);
        }
        
        return $total;
    }
    
    /**
     * Save the current products list to a session.
     *
     */
    public function save() {
        Session::set("ShoppingCart",$this->items);
    }
    
    
}
