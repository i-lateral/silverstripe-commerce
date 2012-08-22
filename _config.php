<?php
// Setup rules for content controllers
Director::addRules(100, array(
    Cart_Controller::$url_segment           => 'Cart_Controller',
    Template_Controller::$url_segment       => 'Template_Controller',
    Checkout_Controller::$url_segment       => 'Checkout_Controller',
    Summary_Controller::$url_segment        => 'Summary_Controller',
    OrderResponse_Controller::$url_segment  => 'OrderResponse_Controller'
));

// Extentions
Object::add_extension('Subsite', 'Commerce_Subsite');
Object::add_extension('ContentController', 'Commerce_Controller');

// Register Reports
SS_Report::register("ReportAdmin", "OrderReport");