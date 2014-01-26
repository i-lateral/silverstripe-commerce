<?php

/**
 * Controller that is used to allow commerce users to manage their accounts
 *
 */
class Commerce_Account_Controller extends Commerce_Controller implements PermissionProvider {

    protected $member;

    public static $url_segment = "commerce/account";

    private static $allowed_actions = array(
        "edit",
        "changepassword",
        "history",
        "order",
        "EditAccountForm",
        "ChangePasswordForm",
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

    public function edit() {
        $member = Member::currentUser();

        $this->customise(array(
            "Title" => _t('CommerceAccount.EDITDETAILS','Edit account details'),
            "Form"  => $this->EditAccountForm()->loadDataFrom($member)
        ));

        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    public function changepassword() {
        // Set the back URL for this form
        Session::set("BackURL",$this->Link("changepassword"));

        $this->customise(array(
            "Title" => _t('Security.CHANGEPASSWORDHEADER','Change your password'),
            "Form"  => $this->ChangePasswordForm()
        ));

        return $this->renderWith(array(
            "Commerce_account",
            "Commerce",
            "Page"
        ));
    }

    /**
     * Factory for generating a profile form. The form can be expanded using an
     * extension class and calling the updateEditProfileForm method.
     *
     * @return Form
     */
    public function EditAccountForm() {
        $form = EditAccountForm::create($this, "EditAccountForm");

        $this->extend("updateEditProfileForm", $form);

        return $form;
    }

    /**
     * Factory for generating a change password form. The form can be expanded
     * using an extension class and calling the updateChangePasswordForm method.
     *
     * @return Form
     */
    public function ChangePasswordForm() {
        $form = ChangePasswordForm::create($this,"ChangePasswordForm");

        $form
            ->Actions()
            ->find("name","action_doChangePassword")
            ->addExtraClass("btn")
            ->addExtraClass("btn-green");

        $cancel_btn = LiteralField::create(
            "CancelLink",
            '<a href="' . $this->Link() . '" class="btn btn-red">'. _t("Commerce.CANCEL", "Cancel") .'</a>'
        );

        $form
            ->Actions()
            ->insertBefore($cancel_btn,"action_doChangePassword");

        $this->extend("updateChangePasswordForm", $form);

        return $form;
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
            "Title" => _t('CommerceAccount.EDITDETAILS',"Edit account details"),
            "Link"  => $this->Link("edit")
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 3,
            "Title" => _t('CommerceAccount.CHANGEPASSWORD',"Change password"),
            "Link"  => $this->Link("changepassword")
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
