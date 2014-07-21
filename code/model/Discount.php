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
