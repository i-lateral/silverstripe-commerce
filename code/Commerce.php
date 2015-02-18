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
    private static $currency_codes = array(
        "GBP" => "United Kingdom Pound",
        "EUR" => "Euro",
        "USD" => "United States Dollar",
        "AUD" => "Australia Dollar",
        "NZD" => "New Zealand Dollar",
        "CAD" => "Canadian Dollar",
        "HKD" => "Hong Kong Dollar",
        "JPY" => "Japanese Yen",
        "TRY" => "Turkey Lira",
        "INR" => "Indian Rupee"
    );

    /**
     * Conversions of currency codes to their actual symbol
     *
     * @var array
     * @config
     */
    private static $currency_symbols = array(
        "GBP" => "£",
        "EUR" => "€",
        "USD" => "$",
        "AUD" => "$",
        "NZD" => "$",
        "CAD" => "$",
        "HKD" => "$",
        "JPY" => "¥",
        "TRY" => "₤",
        "INR" => "₹"
    );

    /**
     * Conversions of currency codes to HTML
     *
     * @var array
     * @config
     */
    private static $currency_html = array(
        "GBP" => "&pound;",
        "EUR" => "&euro;",
        "USD" => "$",
        "AUD" => "$",
        "NZD" => "$",
        "CAD" => "$",
        "HKD" => "$",
        "JPY" => "&yen;",
        "TRY" => "&#8356;",
        "INR" => "&#x20B9;"
    );
}
