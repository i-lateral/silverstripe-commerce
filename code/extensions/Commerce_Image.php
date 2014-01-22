<?php
class Commerce_Image extends DataExtension {
    private static $belongs_many_many = array(
        'Products'      => 'Product'
    );
}
