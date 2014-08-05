<?php

/**
 * Allow slightly more complex product imports from a CSV file
 *
 * @package commerce
 * @author i-lateral (http://www.i-lateral.com)
 */
class ProductCSVBulkLoader extends CsvBulkLoader {

    public $duplicateChecks = array(
        'ID'    => 'ID',
        'SKU'   => 'SKU'
    );

    public function __construct($objectClass = null) {
        if(!$objectClass) $objectClass = 'Product';

        parent::__construct($objectClass);
    }


    /**
     * Perform more complex imports of generic columns
     *
     */
    public function processRecord($record, $columnMap, &$results, $preview = false) {

        // Get Current Object
        $objID = parent::processRecord($record, $columnMap, $results, $preview);
        $object = DataObject::get_by_id($this->objectClass, $objID);

        $this->extend("onBeforeProcess", $record, $object);

        // Loop through all fields and setup associations
        foreach($record as $key => $value) {

            // Find any categories (denoted by a 'CategoryXX' column)
            if(strpos($key,'Category') !== false) {
                $category = ProductCategory::get()
                    ->filter("Title", $value)
                    ->first();

                if($category)
                    $object->Categories()->add($category);
            }

            if($key == 'Categories') {
                $parts = explode(',', $value);
                if(!count($parts)) return false;

                // First remove all categories
                foreach($object->Categories() as $category) {
                    $object->Categories()->remove($category);
                }

                // Now re-add categories
                foreach($parts as $part) {
                    $category = ProductCategory::get()
                        ->filter("Title", trim($part))
                        ->first();

                    if($category)
                        $object->Categories()->add($category);
                }
            }

            // Find any Images (denoted by a 'ImageXX' column)
            if(strpos($key,'Image') !== false && $key != "Images") {
                $image = Image::get()
                    ->filter("Name", $value)
                    ->first();

                if($image)
                    $object->Images()->add($image);
            }

            // Alternativley look for the 'Images' field as a CSV
            if($key == "Images") {
                $parts = explode(',', $value);
                if(count($parts)) {
                    // First remove all Images
                    foreach($object->Images() as $image) {
                        $object->Images()->remove($image);
                    }

                    // Now re-add categories
                    foreach($parts as $part) {
                        $image = Image::get()
                            ->filter("Name", trim($part))
                            ->first();

                        if($image)
                            $object->Images()->add($image);
                    }
                }
            }
        }

        $this->extend("onAfterProcess", $record, $object);

        $object->destroy();
        unset($object);

        return $objID;
    }

}
