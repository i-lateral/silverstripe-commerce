<?php
class Commerce_Image extends DataExtension {
	public static $has_one = array(
		'ParentProduct'		=> 'Product'
	);
}
