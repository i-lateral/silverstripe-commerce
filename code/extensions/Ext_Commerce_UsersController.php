<?php
/**
 * Extension for Users Account Controller that provide methods such as cart link and category list
 * to templates
 *
 * @package commerce
 */
class Ext_Commerce_UsersController extends Extension {

    private static $allowed_actions = array(
        "addresses",
        "addaddress",
        "editaddress",
        "removeaddress",
        "history",
        "outstanding",
        "order",
        "AddressForm"
    );

    /**
     * Display all addresses associated with the current user
     */
    public function addresses() {
        return $this
            ->owner
            ->customise(array(
                "ClassName" => "AccountPage",
                "Title"     => _t("CommerceAccount.YourAddresses", "Your Addresses")
            ))->renderWith(array(
                "Commerce_Account_addresses",
                "Users_Account",
                "Users",
                "Page"
            ));

    }

    /**
     * Display all addresses associated with the current user
     */
    public function addaddress() {
        $form = $this->AddressForm();
        $form->Fields()->dataFieldByName("OwnerID")->setValue(Member::currentuserID());

        return $this
            ->owner
            ->customise(array(
                "ClassName" => "AccountPage",
                "Title"     => _t("CommerceAccount.AddAddress", "Add Address"),
                "Form"  => $form
            ))->renderWith(array(
                "Commerce_Account_addaddress",
                "Users_Account",
                "Users",
                "Page"
            ));

    }

    /**
     * Display all addresses associated with the current user
     */
    public function editaddress() {
        $member = Member::currentUser();
        $id = $this->owner->request->param("ID");
        $address = MemberAddress::get()->byID($id);

        if($address && $address->canEdit($member)) {
            $form = $this->AddressForm();
            $form->loadDataFrom($address);
            $form
                ->Actions()
                ->dataFieldByName("action_doSaveAddress")
                ->setTitle(_t("CommerceAccount.Save", "Save"));

            return $this
                ->owner
                ->customise(array(
                    "ClassName" => "AccountPage",
                    "Title"     => _t("CommerceAccount.EditAddress", "Edit Address"),
                    "Form" => $form
                ))->renderWith(array(
                    "Commerce_Account_editaddress",
                    "Users_Account",
                    "Users",
                    "Page"
                ));
        } else
            return $this->owner->httpError(404);
    }

    /**
     * Remove an addresses by the given ID (if allowed)
     */
    public function removeaddress() {
        $member = Member::currentUser();
        $id = $this->owner->request->param("ID");
        $address = MemberAddress::get()->byID($id);

        if($address && $address->canDelete($member)) {
            $address->delete();
            $this->owner->setFlashMessage(
                "success",
                _t("CommerceAccount.AddressRemoved","Address Removed")
            );

            return $this->owner->redirectback();
        } else
            return $this->owner->httpError(404);
    }

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
     * Form used for adding or editing addresses
     */
    public function AddressForm() {

        $personal_fields = CompositeField::create(
            HeaderField::create('PersonalHeader', _t('Commerce.PersonalDetails','Personal Details'), 2),
            TextField::create('FirstName',_t('Commerce.FirstName','First Name(s)') . '*'),
            TextField::create('Surname',_t('Commerce.Surname','Surname') . '*')
        )->setName("PersonalFields")
        ->addExtraClass('unit')
        ->addExtraClass('size1of2')
        ->addExtraClass('unit-50');

        $address_fields = CompositeField::create(
            HeaderField::create('AddressHeader', _t('Commerce.Address','Address'), 2),
            TextField::create('Address1',_t('Commerce.Address1','Address Line 1') . '*'),
            TextField::create('Address2',_t('Commerce.Address2','Address Line 2')),
            TextField::create('City',_t('Commerce.City','City') . '*'),
            TextField::create('PostCode',_t('Commerce.PostCode','Post Code') . '*'),
            CountryDropdownField::create(
                'Country',
                _t('Commerce.Country','Country')
            )->setAttribute("class",'countrydropdown dropdown btn')
        )->setName("AddressFields")
        ->addExtraClass('unit')
        ->addExtraClass('size1of2')
        ->addExtraClass('unit-50');

        $fields= FieldList::create(
            HiddenField::create("ID"),
            HiddenField::create("OwnerID"),
            CompositeField::create(
                $personal_fields,
                $address_fields
            )->setName("DeliveryFields")
            ->addExtraClass('line')
            ->addExtraClass('units-row')
        );

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $this->owner->Link('addresses') . '" class="btn btn-red commerce-action-back">' . _t('Commerce.Back','Back') . '</a>'
            ),

            FormAction::create('doSaveAddress', _t('CommerceAccount.Add','Add'))
                ->addExtraClass('commerce-action-next')
                ->addExtraClass('btn')
                ->addExtraClass('btn-green')
        );

        $validator = RequiredFields::create(array(
            'FirstName',
            'Surname',
            'Address1',
            'City',
            'PostCode',
            'Country',
        ));

        $form = Form::create($this->owner, "AddressForm", $fields, $actions, $validator);

        return $form;
    }


    /**
     * Method responsible for saving (or adding) a member's address.
     * If the ID field is set, the method assums we are saving
     * an address.
     *
     * If the ID field is not set, we assume a new address is being
     * created.
     *
     */
    public function doSaveAddress($data, $form) {

        if(!$data["ID"])
            $address = MemberAddress::create();
        else
            $address = MemberAddress::get()->byID($data["ID"]);

        if($address) {
            $form->saveInto($address);
            $address->write();

            $this->owner->setFlashMessage(
                "success",
                _t("CommerceAccount.AddressSaved","Address Saved")
            );
        } else {
            $this->owner->setFlashMessage(
                "error",
                _t("CommerceAccount.Error","There was an error")
            );
        }

        return $this->owner->redirect($this->owner->Link("addresses"));
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

        $menu->add(new ArrayData(array(
            "ID"    => 11,
            "Title" => _t('CommerceAccount.Addresses','Addresses'),
            "Link"  => $this->owner->Link("addresses")
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
