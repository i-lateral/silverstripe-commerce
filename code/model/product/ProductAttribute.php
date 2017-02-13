<?php

class ProductAttribute extends DataObject
{
    private static $db = array(
        'Title'     => 'Varchar',
        'Content'   => 'Varchar',
        'Sort'      => 'Int'
    );

    private static $has_one = array(
        'Parent'    => 'Product'
    );

    private static $summary_fields = array(
        'Title',
        'Content'
    );
}
