<?php

class ProductColour extends DataObject
{
    private static $db = array(
        'Title'         => 'Varchar',
        'ColourCode'    => 'Varchar',
        'Quantity'      => 'Int'
    );

    private static $has_one = array(
        'Image'         => 'Image',
        'Parent'        => 'Product'
    );

    private static $summary_fields = array(
        'Title',
        'ColourCode',
        'Quantity'
    );
}
