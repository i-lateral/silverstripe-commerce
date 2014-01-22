<?php
/**
 * Postage objects list available postage costs and destination locations
 *
 * @author morven
 */
class PostageArea extends DataObject {
    private static $db = array(
        'Location'  => 'Varchar',
        'Cost'      => 'Decimal'
    );

    private static $has_one = array(
        'Site' => 'SiteConfig',
    );

    private static $summary_fields = array(
        'Location'  => 'Location',
        'Cost'      => 'Cost'
    );

    private static $field_types = array(
        'Location'  => 'TextField',
        'Cost'      => 'TextField'
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
