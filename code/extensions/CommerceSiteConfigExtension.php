<?php
/**
 * Add additional settings to siteconfig
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package commerce
 */
class CommerceSiteConfigExtension extends DataExtension {
    private static $db = array(
        "Currency"  => "Varchar(3)",
        "Weight"    => "Varchar(2)"
    );

    public function updateCMSFields(FieldList $fields) {
        // Compress default commerce settings
        $fields->addFieldsToTab(
            "Root.Catalogue",
            array(
                DropdownField::create(
                    'Currency',
                    'Currency to use',
                    Commerce::config()->currency_codes,
                    $this->owner->Currency
                )->setEmptyString('Please Select'),

                DropdownField::create(
                    'Weight',
                    'Weight to use',
                    Commerce::config()->weight_units,
                    $this->owner->Weight
                )->setEmptyString('Please Select')
            )
        );
    }

    public function requireDefaultRecords() {

        // If "no product image" is not in DB, add it
        if(!Image::get()->filter('Name','no-image.png')->first()) {
            $image = new Image();
            $image->Name = 'no-image.png';
            $image->Title = 'No Image';
            $image->Filename = 'commerce/images/no-image.png';
            $image->ShowInSearch = 0;
            $image->write();

            DB::alteration_message('No image file added to DB', 'created');
        }
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        // If product image has not been set, add the default
        if(!$this->owner->NoProductImageID) {
            $image = Image::get()
                ->filter('Name','no-image.png')
                ->first();

            if($image) {
                $this->owner->NoProductImageID = $image->ID;
            }
        }
    }
}
