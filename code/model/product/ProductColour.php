<?php

class ProductColour extends DataObject {
    public static $db = array(
        'Title'         => 'Varchar',
        'ColourCode'    => 'Varchar',
        'Quantity'      => 'Int'
    );
    
    public static $has_one = array(
        'Image'         => 'Image',
        'Parent'        => 'Product'
    );
    
    public static $summary_fields = array(
        'Title',
        'ColourCode',
        'Quantity'
    );
}
