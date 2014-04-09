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

    private static $casting = array(
        "MenuTitle"     => "Varchar",
        "AllProducts"   => "ArrayList"
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

    public function getMenuTitle() {
        return $this->Title;
    }

    /**
     * Returns TRUE if this is the currently active category that is being used
     * to handle a request.
     *
     * @return bool
     */
    public function isCurrent() {
        if($this->ID)
            return $this->ID == Catalogue_Controller::get_current_category()->ID;
        else
            return $this === Catalogue_Controller::get_current_category();
    }

    /**
     * Check if this category is in the currently active section (e.g. it is
     * either current or one of it's children or products is currently being
     * viewed).
     *
     * @return bool
     */
    public function isSection() {
        // First check if we are currently viewing a product
        $product = Catalogue_Controller::get_current_product();

        if($product->ID && $product->Categories()->exists()) {
            $ancestors = $product->Categories()->first()->getAncestors()->column();
            $ancestors[] = $product->Categories()->first()->ID;
        } else {
            // Get a map of ancestors
            $ancestors = Catalogue_Controller::get_current_category()->getAncestors()->column();

            if($this->isCurrent()) $ancestors[] = $this->ID;
        }

        return in_array($this->ID,$ancestors) ? true : false;
    }

    /**
     * Return "link", "current" or section depending on if this page is the current page, or not on the current page but
     * in the current section.
     *
     * @return string
     */
    public function LinkingMode() {
        if($this->isCurrent()) {
            return 'current';
        } elseif($this->isSection()) {
            return 'section';
        } else {
            return 'link';
        }
    }

    /**
     * Return "link" or "section" depending on if this is the current section.
     *
     * @return string
     */
    public function LinkOrSection() {
        return $this->isSection() ? 'section' : 'link';
    }

    /**
     * Return a breadcrumb trail for this product (which accounts for parent
     * categories)
     *
     * @param int $maxDepth The maximum depth to traverse.
     *
     * @return string The breadcrumb trail.
     */
    public function Breadcrumbs($maxDepth = 20) {
        $template = new SSViewer('BreadcrumbsTemplate');

        return $template->process($this->customise(new ArrayData(array(
            'Pages' => new ArrayList(array_reverse($this->parentStack()))
        ))));
    }

    /**
     * Returns the category in the current stack of the given level.
     * Level(1) will return the category item that we're currently inside, etc.
     */
    public function Level($level) {
        $parent = $this;
        $stack = array($parent);
        while($parent = $parent->Parent) {
            array_unshift($stack, $parent);
        }

        return isset($stack[$level-1]) ? $stack[$level-1] : null;
    }

    /**
     * Get a list of all products from this category and it's children
     * categories.
     *
     * @return ArrayList
     */
    public function AllProducts() {
        $products = new ArrayList();

        // First add all products from this category
        foreach($this->Products() as $product) {
            $products->add($product);
        }

        // Now loop each child product
        foreach($this->Children() as $child) {
            // First add all products from this category
            foreach($child->Products() as $product) {
                $products->add($product);
            }
        }

        return $products;
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
