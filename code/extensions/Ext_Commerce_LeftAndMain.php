<?php

class Ext_Commerce_LeftAndMain extends LeftAndMainExtension
{
    public function init()
    {
        parent::init();

        Requirements::css('commerce/css/admin.css');
    }
}
