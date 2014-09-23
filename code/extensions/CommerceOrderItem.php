<?php

class CommerceOrderItem extends DataExtension {
    
    /**
     * Find any items in the product catalogue with a matching SKU, good for
     * adding "Order again" links in account panels or finding "Most ordered"
     * etc.
     *
     * @return Product
     */
    public function Product() {
        // If the SKU is set, and it matches a product, return product
        if($this->owner->StockID && $product = Product::get()->filter("StockID", $this->SKU)->first())
            return $product;

        // If nothing has matched, return an empty product
        return Product::create();
    }

    /**
     * Get the product, this method is used by casting
     *
     * @return Product
     */
    public function getMatchProduct() {
        return $this->Product();
    }
    
}
