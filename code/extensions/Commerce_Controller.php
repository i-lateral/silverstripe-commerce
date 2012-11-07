<?php
/**
 * Extension for Content Controller that provide methods such as cart link and category list
 * to templates
 * 
 * @package commerce
 */
class Commerce_Controller extends Extension {
    
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
	public function getProductCategories($ParentID = 0) {
		return ProductCategory::get()->where("ParentID = {$ParentID}")->sort('Sort','DESC');
	}
    
    /**
     * Get a full list of products, filtered by a category if provided.
     *
     * @param ParentCategory the ID of 
     */
    public function getProducts($ParentCategory = null) {
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
	public function getProductCategoryNav($ParentID = 0) {
		$vars = array(
			'ProductCategories' => $this->owner->getProductCategories($ParentID)
		);
		
		return $this->owner->renderWith('ProductCategoryNav_List',$vars);
	}
	
    /**
     * Return a URL to link to this controller
     * 
     * @return string URL to cart controller
     */
    public function getShoppingCartLink(){
        return BASE_URL . '/' . Cart_Controller::$url_slug;
    }
    
    /**
     * Return a list of all items in the shopping cart
     *
     */
    public function getShoppingCart() {
        return ShoppingCart::get();
    }
    
    /**
     * Checks to see if the shopping cart functionality is enabled
     *
     */
    public function ShoppingCartEnabled() {
        return ShoppingCart::isEnabled();
    }
    
    /**
     * Get the 'no-product' image from the DB
     *
     */
    public function getCommerceNoImage() {
        $config = SiteConfig::current_site_config();
        if($config->NoProductImageID) // If image attached via the CMS
            return $config->NoProductImage();
        elseif($image = Image::get()->filter('Name','no-image.png')->first()) // Else see if image exists in database
            return $image;
        else
            return false;
    }
    
    /**
     * Determin if you should show the Subsites Menu
     * 
     * @return boolean
     */
    public function ShowSitesMenu() {
        $sites_num = $this->owner->getTotalSites();
        
        if($sites_num > 1)
            return true;
        else
            return false;
    }
    
    /**
     * Get the total number of Subsites and return as an int
     * 
     * @return int Total number of sites
     */
    public function getTotalSites() {
        $sites = DataObject::get('Subsite');
        
        if($sites->exists())
            return $sites->Count();
        else
            return 0;
    }
    
    /**
     * Get a list of all subsites and return as a DataObjectSet
     * 
     * @return DataObjectSet of all Subsites
     */
    public function getAllSites() {
        return (DataObject::get('Subsite')) ? DataObject::get('Subsite') : false;
    }
}
