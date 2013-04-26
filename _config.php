<?php
// Extentions
Object::add_extension('SiteConfig', 'Commerce_SiteConfig');
Object::add_extension('Image', 'Commerce_Image');

// If CMS installed, use this, if not, use Content Controller
if(class_exists('ContentController'))
    Object::add_extension('ContentController', 'Commerce_Controller');
else
    Object::add_extension('Controller', 'Commerce_Controller');
    
// If subsites is installed
if(class_exists('Subsite')) {
    Object::add_extension('Product', 'Subsites_CommerceObject');
    Object::add_extension('ProductCategory', 'Subsites_CommerceObject');
    Object::add_extension('Order', 'Subsites_CommerceObject');
}

Object::add_extension('LeftAndMain', 'Commerce_LeftAndMain');

// Register Reports
SS_Report::register("ReportAdmin", "OrderReport");
