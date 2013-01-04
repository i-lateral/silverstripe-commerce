<?php
// Extentions
Object::add_extension('SiteConfig', 'Commerce_SiteConfig');

// If CMS installed, use this, if not, use Content Controller
if(class_exists('ContentController'))
    Object::add_extension('ContentController', 'Commerce_Controller');
else
    Object::add_extension('Controller', 'Commerce_Controller');
    
Object::add_extension('LeftAndMain', 'Commerce_LeftAndMain');

// Register Reports
SS_Report::register("ReportAdmin", "OrderReport");
