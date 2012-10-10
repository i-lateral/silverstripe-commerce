<?php
/**
 * This component provides a button for marking all selected orders as
 * dispatched
 *
 * @package commerce
 * @subpackage gridfield
 */
class GridField_DispatchedButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler  {
    protected $targetFragment;

	protected $buttonName;

	public function setButtonName($name) {
		$this->buttonName = $name;
		return $this;
	}

	public function __construct($targetFragment = 'before') {
	    Requirements::javascript('commerce/js/GridField_DispatchedButton.js');
		$this->targetFragment = $targetFragment;
	}

    /**
	 * Place the print button in a <p> tag below the field
	 */
	public function getHTMLFragments($gridField) {
		$button = new GridField_FormAction(
			$gridField, 
			'dispatch', 
			_t('COMMERCE.MarkDispatch', 'Mark Dispatch'),
			'dispatch',
			null
		);
		$button->addExtraClass('gridfield-button-dispatch');
		$button->addExtraClass('no-ajax');
		
		return array(
			$this->targetFragment => '<p class="grid-print-button">' . $button->Field() . '</p>', 
		);
	}
	
	/**
	 * Dispatched is an action button
	 */
	public function getActions($gridField) {
		return array('dispatch');
	}
	
	/**
	 * it is also a URL
	 */
	function getURLHandlers($gridField) {
		return array(
			'dispatch' => 'handleDispatch',
		);
	}
	
	function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        if($actionName == 'dispatch') {
            $ids = $data['IDS'];        
			return $this->handleDispatch($gridField, $ids);
		}
	}

	/**
	 * Handle the print, for both the action button and the URL
 	 */
	public function handleDispatch($gridField, $ids = null) {
	    if(isset($ids)) {
	        $ids = explode(',', $ids);
	        $records = Order::get()->filter('ID', $ids);
	        
            foreach($records as $record) {
                $record->Status = 'dispatched';
                $record->write();
            }
        }
        
        return;
	}
}
