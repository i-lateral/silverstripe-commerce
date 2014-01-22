<?php

/**
 * This class is only used if the CMS is installed, it ensures that the catalog
 * URL is mapped via a page in the CMS
 */
class Catalog extends Page {
    public static $icon = "commerce/images/product.png";

    private static $db = array(
        'Display'   => "Enum('Categories,Category,Products','Categories')"
    );

    private static $has_one = array(
        'Category' => 'ProductCategory'
    );

    public function CommerceChildren() {
        return ProductCategory::get();
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $display_types = array(
            'Categories'    => 'All Root Categories',
            'Category'      => 'One Categories Children',
            'Products'      => 'All Products'
        );

        $fields->addFieldToTab('Root.Main', DropDownField::create('Display', 'What will this catalog display?', $display_types), 'Content');

        if($this->Display == 'Category') $fields->addFieldToTab('Root.Main', TreeDropdownField::create("CategoryID", "Choose a category:", "ProductCategory"), 'Content');

        $fields->removeByName('Content');

        return $fields;
    }

    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        if(!Catalog::get()->first()) {
            $catalog = new Catalog();
            $catalog->Title = "Product Catalog";
            $catalog->URLSegment = "catalog";
            $catalog->Sort = 4;
            $catalog->write();
            $catalog->publish('Stage', 'Live');
            $catalog->flushCache();
            DB::alteration_message('Product Catalog created', 'created');
        }
    }
}

class Catalog_Controller extends Page_Controller {
    public static $url_slug = 'catalog';

    public static $allowed_actions = array(
        'AddItemForm'
    );

    public function init() {
        parent::init();

        Requirements::themedCSS("Commerce","commerce");
    }

    public function getRootCategories() {
        return ProductCategory::get()->filter('ParentID', 0);
    }

    public function getAllProducts() {
        return Product::get();
    }

    public function getCategoryChildren() {
        $category = ProductCategory::get()->filter('ID', $this->CategoryID)->first();

        return ($category) ? $category->ChildrenOrProducts() : false;
    }
    /*
    public function index() {
        if($this->request->Param('ID') && $this->request->Param('ProductID'))
            return $this->renderWith(array('Product', 'Page'));
        else
            return $this->renderWith(array('Categorys', 'Page'));
    }*/

    public function isProduct() {
        if(Controller::curr()->request->Param('ProductID'))
            return true;
        else
            return false;
    }

    public function getCategory() {
        return self::get_current_category();
    }

    public function getProduct() {
        return self::get_current_product();
    }

    /**
     * Create an array list of either current category children or products
     *
     */
    public function CategoriesOrProducts() {
        $category = $this->getCategory();
        $return = false;

        if($category->Children()->exists())
            $return = $category->Children();
        elseif($category->Products()->exists())
            $return = $category->Products();
        elseif($category->ID == 0)
            $return = Product::get();

        return $return;
    }
}
