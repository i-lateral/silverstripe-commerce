<?php
/**
 * @package commerce
 */
class Commerce_Controller extends Extension {
    
    /**
     * @return void
     */
    public function onBeforeInit() {
    	if(Subsite::currentSubsite()) {
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
     * Return a URL to link to this controller
     * 
     * @return string URL to cart controller
     */
    public function getCartLink(){
        return BASE_URL . '/' . Cart_Controller::$url_slug;
    }
    
    /**
     * Return a URL to link to this controller
     * 
     * @return string URL to cart controller
     */
    public function getCartItemsTotal(){
        return Cart_Controller::TotalItems();
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