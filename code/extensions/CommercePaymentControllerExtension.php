<?php 
/**
 * Extend payment process to return a status to orders  
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package commerce
 */
class CommercePaymentControllerExtension extends Extension {
    
    /**
     * Tap into the checkout process and setup a new order
     * 
     */
    public function onBeforeIndex($data, $return) {
        $cart = ShoppingCart::get();
        $data['OrderNumber'] = "";
        
        // Setup an order based on the data from the shopping cart and load data
        $order = new Order();
        $order->update($data);

        // If user logged in, track it against an order
        if(Member::currentUserID())
            $order->CustomerID = Member::currentUserID();

        // Write so we can setup our foreign keys
        $order->write();

        // Loop through each session cart item and add that item to the order
        foreach($cart->getItems() as $cart_item) {
            $order_item = new OrderItem();
            
            $order_item->Title          = $cart_item->Title;
            $order_item->Customisation  = serialize($cart_item->Customisation);
            $order_item->Quantity       = $cart_item->Quantity;
            
            if($cart_item->StockID)
                $order_item->StockID = $cart_item->StockID;
            
            if($cart_item->Price)
                $order_item->Price = $cart_item->Price->RAW();
            
            if($cart_item->Tax)
                $order_item->Tax = $cart_item->Tax->RAW();
            
            $order_item->write();

            $order->Items()->add($order_item);
        }

        $order->write();

        Session::set("Commerce.Order", $order);
    }
    
    
    public function onBeforeCallback($callback) {
        if(array_key_exists("OrderID",$callback) && array_key_exists("Status",$callback)) {
            $order = Order::get()->byID($callback["OrderID"]);
            $order->Status = $callback["Status"];
            
            if(array_key_exists("GatewayData",$callback))
                $order->GatewayData = $callback["GatewayData"];
        }
    }
}
