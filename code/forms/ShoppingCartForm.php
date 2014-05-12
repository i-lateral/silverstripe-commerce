<?php
/**
 * Form used to control editing items in the shopping cart, providing features
 * such as update total, remove and empty.
 *
 * @author morven
 */
class ShoppingCartForm extends Form {

    protected $cart;

    public function getCart() {
        return $this->cart;
    }

    public function __construct($controller, $name = "ShoppingCartForm") {
        // Set shopping cart
        $this->cart = ShoppingCart::get();

        $fields = new FieldList();

        $actions = new FieldList(
            FormAction::create('doEmpty', _t('Commerce.CartEmpty','Empty Cart'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-red'),
            FormAction::create('doUpdate', _t('Commerce.CartUpdate','Update Cart'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-blue')
        );

        parent::__construct($controller, $name, $fields, $actions);
    }


    /**
     * Get all items in the current shopping cart and cast them properly.
     *
     * @return ArrayList
     */
    public function getItems() {
        $items = new ArrayList();

        foreach($this->cart->Items() as $item) {
            // Create a list for customisations, with some casting added
            $customised_list = new ArrayList();

            foreach($item->Customised as $customised) {
                $customised_list->add(new ArrayData(array(
                    'Title' => DBField::create_field('Varchar', $customised->Title),
                    'Value' => nl2br(Convert::raw2xml($customised->Value), true),
                    'ClassName' => Convert::raw2url($customised->Title)
                )));
            }

            $items->add(new ArrayData(array(
                'Key' => $item->Key,
                'Title' => DBField::create_field('Varchar', $item->Title),
                'SKU' => DBField::create_field('Varchar', $item->SKU),
                'Description' => nl2br(Convert::raw2xml($item->Description), true),
                'Customised' => $customised_list,
                'Price' => DBField::create_field('Currency', $item->Price),
                'Tax' => DBField::create_field('Currency', $item->Tax),
                'Quantity' => DBField::create_field('Int', $item->Quantity),
                'Image' => Image::get()->byID($item->ImageID),
            )));
        }

        return $items;
    }

    /**
     * Action that will update cart
     *
     * @param type $data
     * @param type $form
     */
    public function doUpdate($data) {
        foreach($this->cart->Items() as $cart_item) {
            foreach($data as $key => $value) {
                $sliced_key = explode("_", $key);
                if($sliced_key[0] == "Quantity") {
                    if(isset($cart_item) && ($cart_item->Key == $sliced_key[1])) {
                        if($value > 0) {
                            $this->cart->update($cart_item->Key,$value);
                        } else
                            $this->cart->remove($cart_item->Key);
                    }
                }
            }
        }

        $this->cart->save();

        // Clear and postage data that has been set
        Session::clear("Commerce.AvailablePostage");

        $this->controller->redirectBack();
    }

    /**
     * Action that will clear shopping cart and associated sessions
     *
     */
    public function doEmpty() {
        $this->cart->clear();

        return $this->controller->redirectBack();
    }
}
