<?php

class Catalogue_Controller extends Controller {

    protected $dataRecord;

    private static $allowed_actions = array(
        'AddItemForm'
    );

    /**
     * Returns the associated database record
     */
    public function data() {
        return $this->dataRecord;
    }

    public function getDataRecord() {
        return $this->data();
    }

    public function setDataRecord($dataRecord) {
        $this->dataRecord = $dataRecord;
        return $this;
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

    /**
     * Returns a fixed navigation menu of the given level.
     * @return SS_List
     */
    public function getMenu($level = 1) {
        if(ClassInfo::exists("SiteTree")) {
            if($level == 1) {
                $result = SiteTree::get()->filter(array(
                    "ShowInMenus" => 1,
                    "ParentID" => 0
                ));

            } else {
                $parent = $this->data();
                $stack = array($parent);

                if($parent) {
                    while($parent = $parent->Parent) {
                        array_unshift($stack, $parent);
                    }
                }

                if(isset($stack[$level-2]) && !$stack[$level-2] instanceOf Product)
                    $result = $stack[$level-2]->Children();
            }

            $visible = array();

            // Remove all entries the can not be viewed by the current user
            // We might need to create a show in menu permission
            if(isset($result)) {
                foreach($result as $page) {
                    if($page->canView()) {
                        $visible[] = $page;
                    }
                }
            }

            return new ArrayList($visible);
        } else
            return new ArrayList();
    }

    public function Menu($level) {
        return $this->getMenu($level);
    }

    public function SiteConfig() {
        if(ClassInfo::exists("SiteConfig")) {
            if(method_exists($this->dataRecord, 'getSiteConfig')) {
                return $this->dataRecord->getSiteConfig();
            } else {
                return SiteConfig::current_site_config();
            }
        }
    }

    /**
     * Return the title, description, keywords and language metatags.
     *
     * @todo Move <title> tag in separate getter for easier customization and more obvious usage
     *
     * @param boolean|string $includeTitle Show default <title>-tag, set to false for custom templating
     * @param boolean $includeTitle Show default <title>-tag, set to false for
     *                              custom templating
     * @return string The XHTML metatags
     */
    public function MetaTags($includeTitle = true) {
        $tags = "";
        if($includeTitle === true || $includeTitle == 'true') {
            $tags .= "<title>" . Convert::raw2xml(($this->MetaTitle)
                ? $this->MetaTitle
                : $this->Title) . "</title>\n";
        }

        $charset = ContentNegotiator::get_encoding();

        if(Permission::check('CMS_ACCESS_CMSMain') && in_array('CMSPreviewable', class_implements($this))) {
            $tags .= "<meta name=\"x-page-id\" content=\"{$this->ID}\" />\n";
            $tags .= "<meta name=\"x-cms-edit-link\" content=\"" . $this->CMSEditLink() . "\" />\n";
        }

        $this->extend('MetaTags', $tags);

        return $tags;
    }
}
