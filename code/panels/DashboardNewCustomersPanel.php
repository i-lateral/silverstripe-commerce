<?php

class DashboardNewCustomersPanel extends DashboardPanel
{

    private static $icon = "commerce/images/customers.png";

    private static $db = array (
        "Count" => "Int"
    );

	private static $defaults = array (
        "Count"     => "7",
		"PanelSize" => "small"
    );

    public function getLabel()
    {
        return _t('Commerce.LatestCustomers','Latest Customers');
    }


    public function getDescription()
    {
        return _t('Commerce.LatestCustomersDescription','Shows latest customers to join.');
    }

    /**
     * Generate a link to the security admin controller
     *
     * @return String
     */
    public function Securitylink()
    {
        return Injector::inst()->create("SecurityAdmin")->Link();
    }

    public function PanelHolder()
    {
        Requirements::css("commerce/css/dashboard-commerce.css");
        return parent::PanelHolder();
    }

    public function getConfiguration()
    {
        $fields = parent::getConfiguration();

        $fields->push(TextField::create(
            "Count",
            "Number of customers to show"
        ));

        return $fields;
    }

    /**
     * Add view all button to actions
     *
     * @return ArrayList
     */
    public function getSecondaryActions() {
		$actions = parent::getSecondaryActions();
		$actions->push(DashboardPanelAction::create(
            $this->Securitylink(),
            _t("Commerce.ViewAll", "View All")
        ));
			
		return $actions;
	}

    /**
     * Get a list of members who registered through the users module
     * and return (ordered by most recent first).
     *
     * @return SS_List | null
     */
    public function Customers()
    {
        $members = null;
        
        $groups = Group::get()->filter(array(
            "Code" => Users::config()->new_user_groups
        ));

        if ($groups->exists()) {
            $count = ($this->Count) ? $this->Count : 7;

            $members = Member::get()
                ->filter("Groups.ID", $groups->column("ID"))
                ->sort("Created", "DESC")
                ->limit($count);
        }

        return $members;
    }

}