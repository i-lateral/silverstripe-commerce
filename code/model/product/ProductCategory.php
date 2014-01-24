<?php

class ProductCategory extends DataObject {
    private static $db = array(
        'Title'         => 'Varchar',
        'URLSegment'    => 'Varchar',
        'Sort'          => 'Int'
    );

    private static $has_one = array(
        'Parent'        => 'ProductCategory'
    );

    private static $many_many = array(
        'Products'      => 'Product'
    );

    private static $many_many_extraFields = array(
        'Products' => array('SortOrder' => 'Int')
    );

    private static $extensions = array(
        "Hierarchy"
    );

    private static $summary_fields = array(
        'Title'         => 'Title',
        'URLSegment'    => 'URLSegment'
    );

    private static $default_sort = "\"Sort\" DESC";

    /**
    * Return a URL to link to this catagory (via Catalog_Controller)
    *
    * @return string URL to cart controller
    */
    public function Link() {
        return Controller::join_links(BASE_URL , $this->URLSegment);
    }

    /**
     * Returns the product in the current page stack of the given level.
     * Level(1) will return the main menu item that we're currently inside, etc.
     */
    public function Level($level) {
        $parent = $this;
        $stack = array($parent);
        while($parent = $parent->Parent) {
            array_unshift($stack, $parent);
        }

        return isset($stack[$level-1]) ? $stack[$level-1] : null;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName('Sort');
        $fields->removeByName('Products');

        $url_field = TextField::create('URLSegment')
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $products_field = GridField::create(
            "Products",
            "",
            $this->Products(),
            new GridFieldConfig_RelationEditor()
        );

        $parent_field = TreeDropdownField::create('ParentID', 'Parent Category', 'ProductCategory')
            ->setLabelField("Title");

        // Add fields to the CMS
        $fields->addFieldToTab('Root.Main', TextField::create('Title'));
        $fields->addFieldToTab('Root.Main', $url_field);
        $fields->addFieldToTab('Root.Main', $parent_field);
        $fields->addFieldToTab("Root.Main", HeaderField::create("ProductsHeader", "Products in this category"));
        $fields->addFieldToTab('Root.Main', $products_field);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        // Only call on first creation, ir if title is changed
        if(($this->ID == 0) || $this->isChanged('Title') || !($this->URLSegment)) {
            // Set the URL Segment, so it can be accessed via the controller
            $filter = URLSegmentFilter::create();
            $t = $filter->filter($this->Title);

            // Fallback to generic name if path is empty (= no valid, convertable characters)
            if(!$t || $t == '-' || $t == '-1') $t = "category-{$this->ID}";

            // Ensure that this object has a non-conflicting URLSegment value.
            $existing_cats = ProductCategory::get()->filter('URLSegment',$t)->count();
            $existing_products = Product::get()->filter('URLSegment',$t)->count();
            $existing_pages = (class_exists('SiteTree')) ? SiteTree::get()->filter('URLSegment',$t)->count() : 0;

            $count = (int)$existing_cats + (int)$existing_products + (int)$existing_pages;

            $this->URLSegment = ($count) ? $t . '-' . ($count + 1) : $t;
        }
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();

        if($this->Children()) {
            foreach($this->Children() as $child) {
                $child->delete();
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
        $tags .= "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$charset\" />\n";

        if(Permission::check('CMS_ACCESS_CMSMain') && in_array('CMSPreviewable', class_implements($this))) {
            $tags .= "<meta name=\"x-page-id\" content=\"{$this->ID}\" />\n";
            $tags .= "<meta name=\"x-cms-edit-link\" content=\"" . $this->CMSEditLink() . "\" />\n";
        }

        $this->extend('MetaTags', $tags);

        return $tags;
    }

    public function canView($member = false) {
        return true;
    }

    public function canCreate($member = false) {
        return true;
    }

    public function canEdit($member = false) {
        return true;
    }

    public function canDelete($member = false) {
        return true;
    }
}
