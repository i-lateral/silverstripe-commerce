<?php
// Extentions
Object::add_extension('SiteConfig', 'Commerce_SiteConfig');
Object::add_extension('ContentController', 'Commerce_Controller');
Object::add_extension('LeftAndMain', 'Commerce_LeftAndMain');

// Register Reports
SS_Report::register("ReportAdmin", "OrderReport");
