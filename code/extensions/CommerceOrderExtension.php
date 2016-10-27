<?php

/**
 * Extension to the order object to allow us to perform a stock update
 * on save (if the order status has been set correctly) and add Gateway
 * Data
 * 
 * @author ilateral (http://ilateralweb.co.uk)
 * @package commerce
 */
class CommerceOrderExtension extends DataExtension
{
    private static $db = array(
        "GatewayData"       => "Text"
    );

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        
        // Deal with keeping track of stock levels
        $status = Order::config()->completion_status;
        $allow_negative = Commerce::config()->allow_negative_stock;
        
        // If we have just changed the order status and it matches loop
        // all products and update quantities.
        if ($this->owner->isChanged("Status") && $this->owner->Status == $status) {
            foreach ($this->owner->Items() as $order_item) {
                $product = $order_item->Match();
                
                if ($product && $order_item->Quantity) {
                    $product->StockLevel = ($product->StockLevel - $order_item->Quantity);
                    if(!$allow_negative && $product->StockLevel < 0) $product->StockLevel == 0;
                    $product->write();
                }
            }
        }
    }
}
