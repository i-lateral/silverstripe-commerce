<?php 
/**
 * Extend payment process to return a status to orders  
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package commerce
 */
class CommercePaymentControllerExtension extends Extension
{
    
    /**
     * Tap into the checkout process and setup a new order then pass its
     * order number back to our payment controller.
     * 
     */
    public function onBeforeIndex($data)
    {
        $cart = ShoppingCart::get();
        
        // Setup an order based on the data from the shopping cart and load data
        $order = new Estimate();
        
        $order->update($data);
        $order->OrderNumber = "";
        
        // If we are using collection, track it here
        if ($cart->isCollection()) {
            $order->Action = "collect";
        }

        // If user logged in, track it against an order
        if (Member::currentUserID()) {
            $order->CustomerID = Member::currentUserID();
        }

        // Write so we can setup our foreign keys
        $order->write();

        // Loop through each session cart item and add that item to the order
        foreach ($cart->getItems() as $cart_item) {
            $order_item = new OrderItem();
            
            $order_item->Title          = $cart_item->Title;
            $order_item->Customisation  = serialize($cart_item->Customisations);
            $order_item->Quantity       = $cart_item->Quantity;
            
            if ($cart_item->StockID) {
                $order_item->StockID = $cart_item->StockID;
            }

            if ($cart_item->Price) {
                $order_item->Price = $cart_item->Price;
            }

            if ($cart_item->TaxRate) {
                $order_item->TaxRate = $cart_item->TaxRate;
            }

            $order_item->write();

            $order->Items()->add($order_item);
        }
        
        // Overwrite the default order number
        $data->OrderNumber = $order->OrderNumber;

        return $data;
    }

    /**
     * Manipulate a payment so we can map the provided order to it.
     *
     * @param Payment $payment
     * @param Object $order_data
     * @param array $form_data
     * @return void
     */
    public function onBeforeSubmit($payment, $order_data, $form_data)
    {
        $order = Order::get()
            ->find("OrderNumber", $order_data->OrderNumber);

        if ($order) {
            $payment->OrderID = $order->ID;
            $payment->write();
        }
    }
}
