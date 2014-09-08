<?php

/**
 * The add item form is loaded into controllers to allow adding of products to
 * the shopping cart
 */
class AddItemToCartForm extends Form {

    public function __construct($controller, $object, $name = "AddItemForm") {
        $id = ($object) ? $object->ID : 0;

        $fields = FieldList::create(
            HiddenField::create('ID')->setValue($id)
        );

        $actions = FieldList::create(
            FormAction::create('doAddItemToCart',_t('Checkout.AddToCart','Add to Cart'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-green')
        );

        $requirements = new RequiredFields(array("Quantity"));

        $quantity_fields = QuantityField::create('Quantity', _t('Checkout.Qty','Qty'))
            ->setValue('1')
            ->addExtraClass('checkout-additem-quantity');

        // Add quantity, so it appears at the end of the fields
        $fields->add($quantity_fields);

        parent::__construct($controller, $name, $fields, $actions, $requirements);
    }

    public function doAddItemToCart($data) {
        $object = Product::get()->byID($data['ID']);
        $customisations = array();

        foreach($data as $key => $value) {
            if(!(strpos($key, 'customise') === false) && $value) {
                $custom_data = explode("_",$key);

                if($custom_item = ProductCustomisation::get()->byID($custom_data[1])) {
                    $modify_price = 0;

                    // Check if the current selected option has a price modification
                    if($custom_item->Options()->exists()) {
                        $option = $custom_item
                            ->Options()
                            ->filter("Title",$value)
                            ->first();
                        $modify_price = ($option) ? $option->ModifyPrice : 0;
                    }

                    $customisations[] = array(
                        "Title" => $custom_item->Title,
                        "Value" => $value,
                        "ModifyPrice" => $modify_price,
                    );
                }

            }
        }

        if($object) {
            $cart = ShoppingCart::get();
            $cart->add($object->ClassName, $object->ID, $data['Quantity'], $customisations);
            $cart->save();

            $message = _t('Checkout.AddedItemToCart', 'Added item to your shopping cart');
            $message .= ' <a href="'. $cart->Link() .'">';
            $message .= _t('Checkout.ViewCart', 'View cart');
            $message .= '</a>';

            $this->controller->setSessionMessage(
                "success",
                $message
            );
        }

        return $this->controller->redirectBack();
    }

}
