<?php

/**
 * Adds some basic subsites options that can be added to all commerce objects
 */
class Ext_Subsites_CommerceObject extends DataExtension {
    private static $has_one=array(
        'Subsite' => 'Subsite', // The subsite that this page belongs to
    );

    public function onBeforeWrite() {
        if(!$this->owner->SubsiteID) $this->owner->SubsiteID = Subsite::currentSubsiteID();
    }

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab(
            "Root.Main",
            HiddenField::create(
                'SubsiteID',
                'SubsiteID',
                Subsite::currentSubsiteID()
            )
        );
    }
}
