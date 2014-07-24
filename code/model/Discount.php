<?php

class Discount extends DataObject {

    private static $db = array(
        "Title"     => "Varchar",
        "Type"      => "Enum('Fixed,Percentage','Percentage')",
        "Code"      => "Varchar(299)",
        "Amount"    => "Decimal",
        "Expires"   => "Date"
    );

    private static $has_one = array(
        "Site"      => "SiteConfig"
    );

    private static $many_many = array(
        "Groups"    => "Group"
    );

    /**
     * Return a URL that allows this code to be added to a cart
     * automatically
     *
     * @return String
     */
    public function AddLink() {
        $link = Controller::join_links(
            Director::absoluteBaseURL(),
            ShoppingCart::config()->url_segment,
            "usediscount",
            $this->Code
        );

        return $link;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        if($this->Code) {
            $fields->addFieldToTab(
                "Root.Main",
                ReadonlyField::create(
                    "DiscountURL",
                    _t("Commerce.AddDiscountURL", "Add discount URL"),
                    $this->AddLink()
                ),
                "Code"
            );
        }

        return $fields;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        // Ensure that the code is URL safe
        $this->Code = Convert::raw2url($this->Code);
    }

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }

}
