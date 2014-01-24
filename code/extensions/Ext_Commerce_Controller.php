<?php
/**
 * Extension for Content Controller that provide methods such as cart link and category list
 * to templates
 *
 * @package commerce
 */
class Ext_Commerce_Controller extends Extension {

    /**
     * @return void
     */
    public function onBeforeInit() {
        if(class_exists('Subsite') && Subsite::currentSubsite()) {
            // Set the location
            i18n::set_locale(Subsite::currentSubsite()->Language);

            // Check if url is primary domain, if not, re-direct
            if($_SERVER['HTTP_HOST'] != Subsite::currentSubsite()->getPrimaryDomain())
                Director::redirect(Subsite::currentSubsite()->absoluteBaseURL());
        }
    }

    /**
     * @return void
     */
    public function onAfterInit(){
        Requirements::css('commerce/css/Commerce.css');

        Requirements::javascript(SAPPHIRE_DIR . "/javascript/i18n.js");
        Requirements::add_i18n_javascript('commerce/lang/js');

        Requirements::javascript('commerce/js/Commerce.js');
    }

    /**
     * Gets a list of all ProductCategories
     *
     * @param Parent the ID of a parent cetegory
     * @return DataList
     */
    public function getCommerceCategories($ParentID = 0) {
        return ProductCategory::get()
            ->filter("ParentID",$ParentID)
            ->sort('Sort','DESC');
    }

    /**
     * Get a full list of products, filtered by a category if provided.
     *
     * @param ParentCategory the ID of
     */
    public function getCommerceProducts($ParentCategory = null) {
        $products = Product::get();

        if(isset($ParentCategory) && is_int($ParentCategory))
            $products = $products->where("ParentID = {$ParentID}");

        return $products;
    }

    /**
     * Renders a list of all ProductCategories ready to be loaded into a template
     *
     * @return HTML
     */
    public function getCommerceCategoryNav($ParentID = 0) {
        $vars = array(
            'ProductCategories' => $this->owner->getCommerceCategories($ParentID)
        );

        return $this->owner->renderWith('Commerce_CategoryNav',$vars);
    }


    /**
     * Return a URL to link to this controller
     *
     * @return string URL to cart controller
     */
    public function getCommerceCartLink(){
        return Controller::join_links(
            BASE_URL,
            ShoppingCart_Controller::$url_slug
        );
    }


    /**
     * Return a rendered button for the shopping cart
     *
     * @return string Rendered HTML of cart button
     */
    public function getCommerceCartButton(){
        $vars = array(
            'Link'  => $this->owner->getCommerceCartLink(),
            'Cart' => $this->owner->getCommerceCart()
        );

        return $this->owner->renderWith('Commerce_CartButton',$vars);
    }


    /**
     * Return a list of all items in the shopping cart
     *
     */
    public function getCommerceCart() {
        return ShoppingCart::get();
    }

    /**
     * Checks to see if the shopping cart functionality is enabled
     *
     */
    public function getCommerceCartEnabled() {
        return ShoppingCart::isEnabled();
    }
}
