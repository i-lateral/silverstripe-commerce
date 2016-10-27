<?php

class CommerceOrderItemExtension extends DataExtension {
    
    /**
     * Find any items in the product catalogue with a matching StockID, good for
     * adding "Order again" links in account panels or finding "Most ordered"
     * etc.
     *
     * @return Product
     */
    public function Product() {
        // If the StockID is set, and it matches a product, return product
        if($this->owner->StockID && $product = CatalogueProduct::get()->filter("StockID", $this->owner->StockID)->first())
            return $product;

        // If nothing has matched, return an empty product
        return CatalogueProduct::create();
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
