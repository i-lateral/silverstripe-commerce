<?php

class ProductAttribute extends DataObject {
    public static $db = array(
        'Title'     => 'Varchar',
        'Content'   => 'Text'
    );
    
    public static $has_one = array(
        'Parent'    => 'Product'
    );
    
    public static $summary_fields = array(
        'Title',
        'Content'
    );
}
