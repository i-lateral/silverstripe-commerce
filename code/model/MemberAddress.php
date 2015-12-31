<?php

/**
 * An address that belongs to a member object. This allows us to define
 * more than one address that a user can have or send orders to.
 *
 * @package commerce
 * @author i-lateral (http://www.i-lateral.com)
 */
class MemberAddress extends DataObject
{

    public static $db = array(
        'FirstName'         => 'Varchar',
        'Surname'           => 'Varchar',
        'Address1'          => 'Varchar',
        'Address2'          => 'Varchar',
        'City'              => 'Varchar',
        'PostCode'          => 'Varchar',
        'Country'           => 'Varchar',
    );

    public static $has_one = array(
        "Owner" => "Member"
    );

    /**
     * Anyone logged in can create
     *
     * @return Boolean
     */
    public function canCreate($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if ($member) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Only creators or admins can view
     *
     * @return Boolean
     */
    public function canView($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if ($member && $this->OwnerID == $member->ID) {
            return true;
        } elseif ($member && Permission::checkMember($member->ID, array("ADMIN"))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Only order creators or admins can edit
     *
     * @return Boolean
     */
    public function canEdit($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if ($member && $this->OwnerID == $member->ID) {
            return true;
        } elseif ($member && Permission::checkMember($member->ID, array("ADMIN"))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Only creators or admins can delete
     *
     * @return Boolean
     */
    public function canDelete($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if ($member && $this->OwnerID == $member->ID) {
            return true;
        } elseif ($member && Permission::checkMember($member->ID, array("ADMIN"))) {
            return true;
        } else {
            return false;
        }
    }
}
