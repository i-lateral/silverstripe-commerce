<?php

/**
 * Generic holder for commerce module config and generic functions
 * 
 * @author i-lateral (http://www.i-lateral.com)
 * @package commerce
 */
class Commerce extends ViewableData {

    /**
     * List of weight units available for use
     * 
     * @var array
     * @config
     */
    private static $weight_units = array(
        "oz",
        "lb",
        "g",
        "kg"
    ); 
    
    /**
     * List of currency units available
     * 
     * @var array
     * @config
     */
    private static $currency_units = array(
        "GBP" => "£",
        "EUR" => "€",
        "USD" => "$",
        "AUD" => "$",
        "NZD" => "$",
        "CAD" => "$",
        "HKD" => "$"
    );
}
