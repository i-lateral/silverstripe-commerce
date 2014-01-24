<?php

/**
 * Controller that is used to allow commerce users to manage their accounts
 *
 */
class Commerce_Account_Controller extends Commerce_Controller  implements PermissionProvider {

    protected $member;

    public static $url_segment = "commerce/account";

    private static $allowed_actions = array(
        "editdetails",
        "editaddress",
        "ordersoutstanding",
        "ordershistory",
        "EditDetailsForm",
        "EditAddressForm"
    );

    public function init() {
        parent::init();

        // Check we are logged in as a commerce user
        if(!Permission::check("COMMERCE_MANAGE_ACCOUNT")) Security::permissionFailure();

        // Set our memeber object
        $this->member = Member::currentUser();
    }

    public function index() {
        $this->customise(array(
            "Title" => $this->member->FirstName . " " . $this->member->Surname
        ));

        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function editdetails() {
        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function editaddress() {
        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function ordersoutstanding() {
        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function ordershistory() {
        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function EditDetailsForm() {
        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function EditAddressForm() {
        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    /**
     * Return a list of nav items for managing a users profile
     *
     * @return ArrayList
     */
    public function getAccountMenu() {
        $menu = new ArrayList();

        // Add account links
        $menu->add(new ArrayData(array(
            "Title" => "Edit Account Details",
            "Link"  => $this->Link("editdetails")
        )));

        $menu->add(new ArrayData(array(
            "Title" => "Edit Billing Details",
            "Link"  => $this->Link("editaddress")
        )));

        $menu->add(new ArrayData(array(
            "Title" => "Outstanding Orders",
            "Link"  => $this->Link("ordersoutstanding")
        )));

        $menu->add(new ArrayData(array(
            "Title" => "Order History",
            "Link"  => $this->Link("ordershistory")
        )));

        $this->extend("updateAccountMenu", $menu);

        return $menu;
    }

    public function providePermissions() {
        return array(
            "COMMERCE_MANAGE_ACCOUNT" => array(
                'name' => 'Manage commerce account',
                'help' => 'Allow user to manage their commerce account details',
                'category' => 'Commerce',
                'sort' => 100
            ),
        );
    }


}
