<?php

/**
 * Controller used to render pages in the catalogue (either categories or pages)
 *
 */
class Catalogue_Controller extends Commerce_Controller {

    private static $allowed_actions = array(
        'AddItemForm'
    );

    /**
     * Return the link to this controller, but force the expanded link to be returned so that form methods and
     * similar will function properly.
     *
     * @return string
     */
    public function Link($action = null) {
        return $this->data()->Link(($action ? $action : true));
    }

    /**
     * The ContentController will take the URLSegment parameter from the URL and use that to look
     * up a SiteTree record.
     */
    public function __construct($dataRecord = null) {
        $this->dataRecord = $dataRecord;
        $this->failover = $this->dataRecord;
        parent::__construct();
    }

    public function index() {
        $first = ($this->dataRecord instanceOf Product) ? "Commerce_product" : "Commerce_category";

        return $this->renderWith(array(
            $first,
            "Commerce",
            "Page"
        ));
    }

    public function AddItemForm() {
        if(ShoppingCart::isEnabled()) {
            $form = AddItemToCartForm::create($this, $this->dataRecord, "AddItemForm")
                ->addExtraClass('commerce-additemtocartform')
                ->addExtraClass('forms')
                ->addExtraClass('forms-columnar');

            $this->extend("updateAddItemForm", $form);

            return $form;
        } else
            return false;
    }
}
