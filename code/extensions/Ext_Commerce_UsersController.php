<?php
/**
 * Extension for Users Account Controller that provide methods such as cart link and category list
 * to templates
 *
 * @package commerce
 */
class Ext_Commerce_UsersController extends Extension {

    private static $allowed_actions = array(
        "history",
        "outstanding",
        "order"
    );

    /**
     * Display all outstanding orders for the current user
     *
     */
    public function outstanding() {
        $member = Member::currentUser();
        $orders = new PaginatedList($member->getOutstandingOrders(), $this->owner->request);


        if(!$orders->exists()) {
            $message = '<p class="message message-info">';
            $message .= _t("CommerceAccount.NoOrders","There are currently no orders");
            $message .= '</p>';

            $content = new HTMLText();
            $content->setValue($message);
        } else {
            $content = $this->owner->renderWith(
                "Commerce_Account_OrdersList",
                array("Orders" => $orders)
            );
        }

        $this->owner->customise(array(
            "ClassName" => "AccountPage",
            "Title" => _t('CommerceAccount.OutstandingOrders','Outstanding Orders'),
            "Content" => $content,
            "Orders" => $orders
        ));

        return $this->owner->renderWith(array(
            "Users_Account",
            "Users",
            "Page"
        ));
    }

    /**
     * Display all historic orders for the current user
     *
     */
    public function history() {
        $member = Member::currentUser();
        $orders = new PaginatedList($member->getHistoricOrders(), $this->owner->request);


        if(!$orders->exists()) {
            $message = '<p class="message message-info">';
            $message .= _t("CommerceAccount.NoOrders","There are currently no orders");
            $message .= '</p>';

            $content = new HTMLText();
            $content->setValue($message);
        } else {
            $content = $this->owner->renderWith(
                "Commerce_Account_OrdersList",
                array("Orders" => $orders)
            );
        }

        $this->owner->customise(array(
            "ClassName" => "AccountPage",
            "Title" => _t('CommerceAccount.OrderHistory','Order History'),
            "Content" => $content,
            "Orders" => $orders
        ));

        return $this->owner->renderWith(array(
            "Users_Account",
            "Users",
            "Page"
        ));
    }

    /**
     * Display the currently selected order from the URL
     *
     */
    public function order() {
        $orderID = $this->owner->request->param("ID");
        $order = Order::get()->byID($orderID);
        $content = new HTMLText();

        if(!$order || ($order && !$order->canView())) {
            $message = '<p class="message message-error">';
            $message .= _t("CommerceAccount.NotFound","Order not found");
            $message .= '</p>';

            $title = _t("CommerceAccount.NotFound","Order not found");
            $content->setValue($message);
            $order = null;
        } else {
            $title =  _t('Commerce.Order','Order') . ': ' . $order->OrderNumber;
        }

        $this->owner->customise(array(
            "ClassName" => "AccountPage",
            "Title" => $title,
            "Content" => $content,
            "Order" => $order
        ));

        return $this->owner->renderWith(array(
            "Commerce_Account_order",
            "Commerce",
            "Page"
        ));
    }

    /**
     * Add commerce specific links to account menu
     *
     */
    public function updateAccountMenu($menu) {

        $menu->add(new ArrayData(array(
            "ID"    => 1,
            "Title" => _t('CommerceAccount.OutstandingOrders','Outstanding Orders'),
            "Link"  => $this->owner->Link("outstanding")
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 2,
            "Title" => _t('CommerceAccount.OrderHistory',"Order history"),
            "Link"  => $this->owner->Link("history")
        )));
    }

    /**
     * Add fields used by this module to the profile editing form
     *
     */
    public function updateEditAccountForm($form) {
        $form->Fields()->insertBefore(TextField::create(
            "Company",
            _t('CommerceAccount.Company',"Company")
        ),"FirstName");

        $form->Fields()->add(TextField::create(
            "PhoneNumber",
            _t("CommerceAccount.PhoneNumber","Phone Number")
        ));
    }
}
