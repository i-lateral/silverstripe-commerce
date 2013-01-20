<?php

/**
 * Adds some basic subsites options that can be added to all commerce objects
 */
class Subsites_CommerceObject extends DataExtension {		
	public static $has_one=array(
		'Subsite' => 'Subsite', // The subsite that this page belongs to
	);

	function onBeforeWrite() {
		if(!$this->owner->SubsiteID) $this->owner->SubsiteID = Subsite::currentSubsiteID();
	}

	/**
	 * Return a piece of text to keep DataObject cache keys appropriately specific
	 */
	function cacheKeyComponent() {
		return 'subsite-'.Subsite::currentSubsiteID();
	}
}
