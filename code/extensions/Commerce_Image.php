<?php
class Commerce_Image extends DataExtension {
	public static $belongs_many_many = array(
		'Products'		=> 'Product'
	);
}
