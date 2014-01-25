<?php

/**
 * Controller that is used to allow commerce users to manage their accounts
 *
 */
class Commerce_Account_Controller extends Commerce_Controller implements PermissionProvider {

    protected $member;

    public static $url_segment = "commerce/account";

    private static $allowed_actions = array(
        "editdetails",
        "history",
        "order",
        "EditForm",
    );

    public function init() {
        parent::init();

        // Check we are logged in as a commerce user
        if(!Permission::check("COMMERCE_MANAGE_ACCOUNT")) Security::permissionFailure();

        // Set our memeber object
        $this->member = Member::currentUser();
    }

    /**
     * Display the currently outstanding orders for the current user
     *
     */
    public function index() {
        $orders = new PaginatedList($this->member->getOutstandingOrders(), $this->request);
        $content = new HTMLText();

        $this->extend("updateOutstandingOrders", $orders);

        if(!$orders->exists()) {
            $message = '<p class="message message-info">';
            $message .= _t("NOORDERS","There are currently no orders");
            $message .= '</p>';

            $content->setValue($message);
        }

        $this->customise(array(
            "Title" => _t('CommerceAccount.OUTSTANDINGORDERS','Outstanding Orders'),
            "Content" => $content,
            "Orders" => $orders
        ));

        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    /**
     * Display all outstanding orders for the current user
     *
     */
    public function history() {
        $orders = new PaginatedList($this->member->getHistoricOrders(), $this->request);
        $content = new HTMLText();

        $this->extend("updateHistoricOrders", $orders);

        if(!$orders->exists()) {
            $message = '<p class="message message-info">';
            $message .= _t("NOORDERS","There are currently no orders");
            $message .= '</p>';

            $content->setValue($message);
        }

        $this->customise(array(
            "Title" => _t('CommerceAccount.ORDERHISTORY','Order History'),
            "Content" => $content,
            "Orders" => $orders
        ));

        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    /**
     * Display the currently selected order from the URL
     *
     */
    public function order() {
        $orderID = $this->request->param("ID");
        $order = Order::get()->byID($orderID);
        $content = new HTMLText();

        $this->extend("updateOrderInfo", $order);

        if(!$order || ($order && !$order->canView())) {
            $message = '<p class="message message-error">';
            $message .= _t("NOTFOUND","Order not found");
            $message .= '</p>';

            $title = _t("NOTFOUND","Order not found");
            $content->setValue($message);
            $order = null;
        } else {
            $title =  _t('Commerce.ORDER','Order') . ': ' . $order->OrderNumber;
        }

        $this->customise(array(
            "Title" => $title,
            "Content" => $content,
            "Order" => $order
        ));

        return $this->renderWith(array(
            "Commerce_order",
            "Commerce",
            "Page"
        ));
    }

    public function editdetails() {
        $this->customise(array(
            "Title" => _t('CommerceAccount.EDITDETAILS','Edit account details')
        ));

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
            "ID"    => 0,
            "Title" => _t('CommerceAccount.OUTSTANDINGORDERS',"Outstanding orders"),
            "Link"  => $this->Link()
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 1,
            "Title" => _t('CommerceAccount.ORDERHISTORY',"Order history"),
            "Link"  => $this->Link("history")
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 2,
            "Title" => _t('CommerceAccount.EDITDETAILS',"Edit account Details"),
            "Link"  => $this->Link("editdetails")
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
