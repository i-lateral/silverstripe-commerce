<?php

/**
 * Simple extension that enables the edit link to work when a gridfield is made
 * selectable
 *
 * @package Commerce 
 */
class GridField_EditSelectable implements GridField_HTMLProvider {
    protected $targetFragment;

	protected $buttonName;

	public function setButtonName($name) {
		$this->buttonName = $name;
		return $this;
	}

	public function __construct($targetFragment = 'before') {
	    Requirements::javascript('commerce/js/GridField_EditSelectable.js');
	}
	
	/**
	 * return an empty array 
	 */
	public function getHTMLFragments($gridField) {		
		return array();
	}
}
