<?php 
/**
 * Extend payment process to return a status to orders  
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package commerce
 */
class CommercePaymentHandlerExtension extends Extension {
    
    /**
     * Tap into the checkout process and setup a new order then pass its
     * order number back to our payment controller.
     * 
     */
    public function onBeforeIndex() {
        $cart = ShoppingCart::get();
        $data = $this->owner->getOrderData();
        
        // Setup an order based on the data from the shopping cart and load data
        $order = new Order();
        
        $order->update($data);
        $order->OrderNumber = "";

        // If user logged in, track it against an order
        if(Member::currentUserID())
            $order->CustomerID = Member::currentUserID();

        // Write so we can setup our foreign keys
        $order->write();

        // Loop through each session cart item and add that item to the order
        foreach($cart->getItems() as $cart_item) {
            $order_item = new OrderItem();
            
            $order_item->Title          = $cart_item->Title;
            $order_item->Customisation  = serialize($cart_item->Customisations);
            $order_item->Quantity       = $cart_item->Quantity;
            
            if($cart_item->StockID)
                $order_item->StockID = $cart_item->StockID;
            
            if($cart_item->Price)
                $order_item->Price = $cart_item->Price;
            
            if($cart_item->TaxRate)
                $order_item->TaxRate = $cart_item->TaxRate;
            
            $order_item->write();

            $order->Items()->add($order_item);
        }
        
        // Overwrite the default order number
        $this->owner->getOrderData()->OrderNumber = $order->OrderNumber;
    }
    
    
    public function onAfterCallback() {
        $data = $this->owner->getPaymentData();
        $order = null;
        
        if($data->Status && $data->OrderID) {
            $order = Order::get()
                ->filter("OrderNumber", $data->OrderID)
                ->first();
        }
        
        if($data->Status && $data->PaymentID) {
            $order = Order::get()
                ->filter("PaymentNo", $data->PaymentID)
                ->first();
        }
        
        if($order) {
            $order->Status = $data->Status;
            $order->GatewayData = json_encode($data->GatewayData);
            $order->write();
        }
    }
}
