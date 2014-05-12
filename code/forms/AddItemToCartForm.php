<?php

/**
 * The add item form is loaded into controllers to allow adding of products to
 * the shopping cart
 */
class AddItemToCartForm extends Form {

    public function __construct($controller, $product, $name = "AddItemForm") {
        $productID = ($product) ? $product->ID : 0;

        $fields = FieldList::create(
            HiddenField::create('ProductID')->setValue($productID)
        );

        $actions = FieldList::create(
            FormAction::create('doAddItemToCart',_t('Commerce.ADDTOCART','Add to Cart'))
                ->addExtraClass('btn')
        );

        $requirements = new RequiredFields(array("Quantity"));

        // If product colour customisations are set, add them to the item form
        if($product && $product->Customisations()->exists()) {
            foreach($product->Customisations() as $customisation) {
                $field = $customisation->Field();
                $fields->add($field);

                // Check if field required
                if($customisation->Required) $requirements->addRequiredField($field->getName());
            }
        }

        $quantity_fields = QuantityField::create('Quantity', _t('Commerce.CARTQTY','Qty'))
            ->setValue('1')
            ->addExtraClass('commerce-additem-quantity');

        // Add quantity, so it appears at the end of the fields
        $fields->add($quantity_fields);

        parent::__construct($controller, $name, $fields, $actions, $requirements);
    }

    public function doAddItemToCart($data) {
        $product = Product::get()->byID($data['ProductID']);
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

        if($product) {
            $cart = ShoppingCart::get();
            $cart->add($product, $data['Quantity'], $customisations);
            $cart->save();

            $cart_url = Controller::join_links(BASE_URL, ShoppingCart_Controller::$url_segment);

            $message = _t('Commerce.ADDEDITEMTOCART', 'Added item to your shopping cart');
            $message .= ' <a href="'. $cart_url .'">';
            $message .= _t('Commerce.VIEWCART', 'View cart');
            $message .= '</a>';

            $this->controller->setSessionMessage(
                "success",
                $message
            );
        }

        return $this->controller->redirectBack();
    }

}
