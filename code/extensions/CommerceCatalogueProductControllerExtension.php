<?php

class CommerceCatalogueProductControllerExtension extends Extension {
    
    /**
     * Add simple "add item to cart" functionality to products, if
     * catalogue module is installed
     * 
     */
    public function updateForm($form) {
        $object = $this->owner->dataRecord;
        
        // Add object type and classname
        $form
            ->Fields()
            ->push(HiddenField::create('ID')->setValue($object->ID)->setForm($form));
            
        $form
            ->Fields()
            ->push(HiddenField::create('ClassName')->setValue($object->ClassName)->setForm($form));
            
        $form
            ->Fields()
            ->push(
                QuantityField::create('Quantity', _t('Commerce.Qty','Qty'))
                    ->setValue('1')
                    ->addExtraClass('checkout-additem-quantity')
                    ->setForm($form)
            );
        
        // Add "Add item" button
        $form
            ->Actions()
            ->push(
                FormAction::create('doAddItemToCart',_t('Commerce.AddToCart','Add to Cart'))
                    ->addExtraClass('btn')
                    ->addExtraClass('btn-green btn-primary')
                    ->setForm($form)
            );

        // Add validator
        $form
            ->getValidator()
            ->addRequiredField("Quantity");
    }
    
    public function doAddItemToCart($data, $form) {
        $classname = $data["ClassName"];
        $id = $data["ID"];
        $cart = ShoppingCart::get();

        if($object = $classname::get()->byID($id)) {
            if ($object->TaxRateID && $object->TaxRate()->Amount) {
                $tax_rate = $object->TaxRate()->Amount;
            } else {
                $tax_rate = 0;
            }

            $deliverable = (isset($object->Deliverable)) ? $object->Deliverable : true;

            $item_to_add = array(
                "Key" => $object->ID,
                "Title" => $object->Title,
                "Content" => $object->Content,
                "BasePrice" => $object->Price,
                "TaxRate" => $tax_rate,
                "Image" => $object->Images()->first(),
                "StockID" => $object->StockID,
                "ID" => $object->ID,
                "Weight" => $object->Weight,
                "ClassName" => $object->ClassName,
                "Stocked" => $object->Stocked,
                "Deliverable" => $deliverable
            );

            // Try and add item to cart, return any exceptions raised
            // as a message
            try {
                $cart->add($item_to_add, $data['Quantity']);
                $cart->save();
                
                $message = _t('Commerce.AddedItemToCart', 'Added item to your shopping cart');
                $message .= ' <a href="'. $cart->Link() .'">';
                $message .= _t('Commerce.ViewCartNow', 'View cart now');
                $message .= '</a>';

                $this->owner->setSessionMessage(
                    "success",
                    $message
                );
            } catch(ValidationException $e) {
                $this->owner->setSessionMessage(
                    "bad",
                    $e->getMessage()
                );
            } catch(Exception $e) {
                $this->owner->setSessionMessage(
                    "bad",
                    $e->getMessage()
                );
            }
        } else {
            $this->owner->setSessionMessage(
                "bad",
                _t("Commerce.ThereWasAnError", "There was an error")
            );
        }

        return $this->owner->redirectBack();
    }
}
