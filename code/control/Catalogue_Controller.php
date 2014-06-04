<?php

/**
 * Controller used to render pages in the catalogue (either categories or pages)
 *
 */
class Catalogue_Controller extends Commerce_Controller {

    private static $allowed_actions = array(
        'image',
        'AddItemForm'
    );

    /**
     * Find the current category via its URL
     *
     * @return ProductCategory
     *
     */
    public static function get_current_category() {
        $segment = Controller::curr()->request->param('URLSegment');
        $return = null;

        if($segment) {
            $return = ProductCategory::get()
                ->filter('URLSegment',$segment)
                ->first();
        }

        if(!$return) $return = ProductCategory::create();

        return $return;
    }

    /**
     * Find the current category via its URL
     *
     * @return Product
     *
     */
    public static function get_current_product() {
        $segment = Controller::curr()->request->param('URLSegment');
        $return = null;

        if($segment) {
            $return = Product::get()
                ->filter(array(
                    'URLSegment'=> $segment,
                    "Disabled"=> 0
                ))->first();
        }

        if(!$return) $return = Product::create();

        return $return;
    }

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
     * Return the title, description, keywords and language metatags.
     *
     * @todo Move <title> tag in separate getter for easier
     * customization and more obvious usage
     *
     * @param boolean|string $includeTitle Show default <title>-tag, set to false for custom templating
     * @param boolean $includeTitle Show default <title>-tag, set to false for custom templating
     * @return string The XHTML metatags
     */
    public function MetaTags($includeTitle = true) {
        $tags = "";
        if($includeTitle === true || $includeTitle == 'true') {
            $tags .= "<title>" . $this->Title . "</title>\n";
        }

        $generator = trim(Config::inst()->get('SiteTree', 'meta_generator'));
        if (!empty($generator)) {
            $tags .= "<meta name=\"generator\" content=\"" . Convert::raw2att($generator) . "\" />\n";
        }

        $charset = Config::inst()->get('ContentNegotiator', 'encoding');
        $tags .= "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$charset\" />\n";
        if($this->MetaDescription) {
            $tags .= "<meta name=\"description\" content=\"" . Convert::raw2att($this->MetaDescription) . "\" />\n";
        }
        if($this->ExtraMeta) {
            $tags .= $this->ExtraMeta . "\n";
        }

        if(Permission::check('CMS_ACCESS_CMSMain') && in_array('CMSPreviewable', class_implements($this)) && !$this instanceof ErrorPage) {
            $tags .= "<meta name=\"x-page-id\" content=\"{$this->ID}\" />\n";
            $tags .= "<meta name=\"x-cms-edit-link\" content=\"" . $this->CMSEditLink() . "\" />\n";
        }

        $this->extend('MetaTags', $tags);

        return $tags;
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

    /**
     * Action used to display an image for a product
     */
    public function image() {
        if(!($this->dataRecord instanceOf Product))
            return $this->redirect(BASE_URL);

        return $this->renderWith(array(
            "Commerce_product",
            "Commerce",
            "Page"
        ));
    }

    /**
     * The productimage action is used to determine the default image that will
     * appear related to a product
     *
     * @return Image
     */
    public function ProductImage() {
        $images = $this->SortedImages();
        $action = $this->request->param('Action');
        $id = $this->request->param('ID');

        $image = null;

        if($action && $action == "image" && $id)
            $image = $images->filter("ID",$id)->first();

        if(!$image)
            $image = $images->first();

        return $image;
    }

    public function AddItemForm() {
        if(ShoppingCart::config()->enabled) {
            $form = AddItemToCartForm::create($this, $this->dataRecord, "AddItemForm")
                ->addExtraClass('forms')
                ->addExtraClass('forms-columnar');

            $this->extend("updateAddItemForm", $form);

            return $form;
        } else
            return false;
    }


}
