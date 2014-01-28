<?php
// Extentions
SiteConfig::add_extension('Ext_Commerce_SiteConfig');
Image::add_extension('Ext_Commerce_Image');
Controller::add_extension('Ext_Commerce_Controller');
Group::add_extension('Ext_Commerce_Group');
Member::add_extension('Ext_Commerce_Member');
Users_Account_Controller::add_extension('Ext_Commerce_UsersController');

// If subsites is installed
if(class_exists('Subsite')) {
    Product::add_extension('Ext_Subsites_CommerceObject');
    ProductCategory::add_extension('Ext_Subsites_CommerceObject');
    Order::add_extension('Ext_Subsites_CommerceObject');
}

LeftAndMain::add_extension('Ext_Commerce_LeftAndMain');
