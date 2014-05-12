<?php

/**
 * Class that represents the weight of a product and how that weight is rendered
 *
 */
 class ProductWeight extends DataObject {
    private static $db = array(
        'Title' => 'Varchar',
        'Unit'  => 'Varchar(3)'
    );

    private static $summary_fields = array(
        'Title',
        'Unit'
    );

    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        if(!ProductWeight::get()->exists()) {
            $weight = new ProductWeight();
            $weight->Title = "Kilograms";
            $weight->Unit = "kg";
            $weight->write();
            $weight->flushCache();
            DB::alteration_message('Kilograms weight created', 'created');

            $weight = new ProductWeight();
            $weight->Title = "Grams";
            $weight->Unit = "g";
            $weight->write();
            $weight->flushCache();
            DB::alteration_message('Grams weight created', 'created');

            $weight = new ProductWeight();
            $weight->Title = "Pound";
            $weight->Unit = "lb";
            $weight->write();
            $weight->flushCache();
            DB::alteration_message('Pounds weight created', 'created');

            $weight = new ProductWeight();
            $weight->Title = "Ounce";
            $weight->Unit = "oz";
            $weight->write();
            $weight->flushCache();
            DB::alteration_message('Ounces weight created', 'created');
        }
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
