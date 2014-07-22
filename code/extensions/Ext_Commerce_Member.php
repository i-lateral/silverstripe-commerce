<?php

class Ext_Commerce_Member extends DataExtension {
    private static $db = array(
        "PhoneNumber"   => "Varchar",
        "Company"       => "Varchar(99)"
    );

    private static $has_many = array(
        "Orders"        => "Order",
        "Addresses"     => "MemberAddress"
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->remove("PhoneNumber");

        $fields->addFieldToTab(
            "Root.Main",
            TextField::create("PhoneNumber"),
            "Password"
        );

        $fields->addFieldToTab(
            "Root.Main",
            TextField::create("Company"),
            "FirstName"
        );

        return $fields;
    }

    /**
     * Get a discount from the groups this member is in
     *
     * @return Discount
     */
    public function getDiscount() {
        $discounts = ArrayList::create();

        foreach($this->owner->Groups() as $group) {
            foreach($group->Discounts() as $discount) {
                $discounts->add($discount);
            }
        }

        $discounts->sort("Amount", "DESC");

        return $discounts->first();
    }

    /**
     * Get all orders that have been generated and are marked as paid or
     * processing
     *
     * @return DataList
     */
    public function getOutstandingOrders() {
        $orders = $this
            ->owner
            ->Orders()
            ->filter(array(
                "Status" => array("paid","processing")
            ));

        return $orders;
    }

    /**
     * Get all orders that have been generated and are marked as dispatched or
     * canceled
     *
     * @return DataList
     */
    public function getHistoricOrders() {
        $orders = $this
            ->owner
            ->Orders()
            ->filter(array(
                "Status" => array("dispatched","canceled")
            ));

        return $orders;
    }
}
