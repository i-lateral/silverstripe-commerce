<?php

class DashboardContentSummaryPanel extends DashboardPanel
{

    private static $icon = "commerce/images/search.png";

	private static $defaults = array (
		"PanelSize" => "small"
	);

    public function getLabel()
    {
        return _t('Commerce.SiteContentSummary','Site Content Summary');
    }


    public function getDescription()
    {
        return _t('Commerce.SiteContentSummaryDescription','Show a summary of website content');
    }

    public function PanelHolder()
    {
        Requirements::css("commerce/css/dashboard-commerce.css");
        return parent::PanelHolder();
    }

    /**
     * Get the total amount of products on this site
     *
     * @return Int
     */
    public function Products()
    {
        return Product::get()->count();
    }

    /**
     * Get the total amount of products on this site
     *
     * @return Int
     */
    public function Categories()
    {
        return Category::get()->count();
    }

    /**
     * Get the total amount of pages on this site
     *
     * @return Int
     */
    public function Pages()
    {
        return SiteTree::get()->count();
    }


    /**
     * Get the total amount of files on this site
     *
     * @return Int
     */
    public function Files()
    {
        return File::get()->count();
    }

    /**
     * Get a list of customers (users who have signed in via the registration form)
     *
     * @return Int
     */
    public function Customers()
    {
        $members = 0;
        
        $groups = Group::get()->filter(array(
            "Code" => Users::config()->new_user_groups
        ));

        if ($groups->exists()) {
            $count = ($this->Count) ? $this->Count : 7;

            $members = Member::get()
                ->filter("Groups.ID", $groups->column("ID"))
                ->count();
        }

        return $members;
    }
}