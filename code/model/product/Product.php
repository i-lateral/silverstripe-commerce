<?php

class Product extends DataObject {
    private static $db = array(
        "Title"             => "Varchar(255)",
        "SKU"               => "Varchar(99)",
        "Quantity"          => "Int",
        "Price"             => "Decimal",
        "URLSegment"        => "Varchar",
        "Description"       => "HTMLText",
        "MetaDescription"   => "Text",
        "ExtraMeta"         => "HTMLText",
        "Sort"              => "Int",
        "PackSize"          => "Varchar",
        "Weight"            => "Int",
        "Disabled"          => "Boolean"
    );

    private static $has_many = array(
        'Attributes'    => 'ProductAttribute',
        'Customisations'=> 'ProductCustomisation'
    );

    private static $many_many = array(
        'Images'        => 'Image',
        "RelatedProducts"=>"Product"
    );

    private static $many_many_extraFields = array(
        'Images' => array('SortOrder' => 'Int')
    );

    private static $belongs_many_many = array(
        'Categories'    => 'ProductCategory'
    );

    private static $casting = array(
        'MenuTitle'         => 'Varchar',
        'CategoriesList'    => 'Varchar',
        'CMSThumbnail'      => 'Varchar',
        "PriceWithTax"      => 'Decimal',
        "Tax"               => 'Decimal',
        "TaxName"           => 'Varchar'
    );

    private static $summary_fields = array(
        'CMSThumbnail'  => 'Thumbnail',
        'Title'         => 'Title',
        'Quantity'      => 'Qty',
        'SKU'           => 'SKU',
        'Price'         => 'Price',
        'CategoriesList'=> 'Categories',
        "Disabled"      => "Disabled"
    );

    private static $searchable_fields = array(
        'Title',
        'SKU',
        'Description'
    );

    private static $default_sort = "\"Sort\" DESC, \"Title\" ASC";

    /**
     * Return a URL to link to this product (via Catalog_Controller)
     *
     * @return string URL to cart controller
     */
    public function Link($action = null){
        return Controller::join_links(
            BASE_URL,
            $this->URLSegment,
            $action
        );
    }

    /**
     * Return the absolute link to this product
     */
    public function AbsoluteLink($action = null) {
        return Director::absoluteURL($this->Link($action));
    }

    public function getMenuTitle() {
        return $this->Title;
    }

    /**
     * Get the amount of tax that the base price of this product produces as a
     * decimal.
     *
     * If tax is not set (or set to 0) then this returns 0.
     *
     * @return Decimal
     */
    public function getTax() {
        $config = SiteConfig::current_site_config();
        (float)$price = $this->Price;
        (float)$rate = $config->TaxRate;

        if($rate > 0)
            (float)$tax = ($price / 100) * $rate; // Get our tax amount from the price
        else
            (float)$tax = 0;

        return number_format($tax, 2);
    }

    /**
     * The price for this product including the percentage cost of the tax
     * (set in global config).
     *
     * This price is based on the tax rates set in the admin and whether or not
     * the siteconfig is set to include tax or not.
     *
     * @return Decimal
     */
    public function getPriceWithTax() {
        (float)$price = $this->Price;
        (float)$tax = $this->Tax;

        return number_format($price + $tax, 2);
    }

    /**
     * Determine if we need to show the product price with or without tax, based
     * on siteconfig
     *
     * @return Decimal
     */
    public function getFrontPrice() {
        $config = SiteConfig::current_site_config();

        if($config->TaxPriceInclude)
            return $this->getPriceWithTax();
        else
            return $this->Price;
    }

    /**
     * Return sorted images, if no images exist, create a new opbject set
     * with a blank product image in it.
     *
     * @return ArrayList
     */
    public function SortedImages(){
        if($this->Images()->exists())
            $images = $this->Images()->Sort('SortOrder');
        else {
            $images = new ArrayList();
            $default_image = new Image();
            $default_image->ID = -1;
            $default_image->Title = "No Image Available";
            $default_image->FileName = BASE_URL . '/commerce/images/no-image.png';
            $images->add($default_image);
        }

        return $images;
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
        $items = array();

        if($this->Categories()->exists()) {
            $items[] = $this;
            $category = $this->Categories()->first();

            foreach($category->parentStack() as $item) {
                $items[] = $item;
            }
        }

        $template = new SSViewer('BreadcrumbsTemplate');

        return $template->process($this->customise(new ArrayData(array(
            'Pages' => new ArrayList(array_reverse($items))
        ))));
    }

    public function getCMSThumbnail() {
        if($this->Images()->exists())
            return $this->Images()->first()->PaddedImage(50,50);
        else
            return '(No Image)';
    }

    /**
     * Determin if the product has more than one image
     *
     * return Boolean
     */
    public function HasMultipleImages() {
        if($this->Images()->exists() && $this->Images()->count() > 1)
            return true;
        else
            return false;
    }

    public function getCategoriesList() {
        $list = '';

        if($this->Categories()->exists()){
            foreach($this->Categories() as $category) {
                $list .= $category->Title;
                $list .= ', ';
            }
        }

        return $list;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName('Sort');
        $fields->removeByName('Quantity');
        $fields->removeByName('PackSize');
        $fields->removeByName('Weight');
        $fields->removeByName('SKU');
        $fields->removeByName('Images');
        $fields->removeByName('RelatedProducts');
        $fields->removeByName('MetaDescription');
        $fields->removeByName('ExtraMeta');

        // Use to display autogenerated URL
        $url_field = TextField::create('URLSegment')
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $fields->addFieldToTab('Root.Main', $url_field, 'Price');

        // Add disble product field
        $fields->addFieldToTab('Root.Main', CheckboxField::create(
            "Disabled",
            _t("Commerce.DisableProduct", "Disable this product?")
        ));

        $fields->addFieldToTab(
            'Root.Main',
            HTMLEditorField::create('Description')
                ->setRows(20)
                ->addExtraClass('stacked')
        );

        // Additional product info
        $additional_field = ToggleCompositeField::create('AdditionalData', 'Additional Data',
            array(
                NumericField::create("Quantity", $this->fieldLabel('Quantity')),
                TextField::create("SKU", $this->fieldLabel('SKU')),
                TextField::create("Sort", $this->fieldLabel('Sort')),
                TextField::create("PackSize", $this->fieldLabel('PackSize')),
                TextField::create("Weight", $this->fieldLabel('Weight'))
            )
        )->setHeadingLevel(4);

        $meta_field = ToggleCompositeField::create('Metadata', _t('SiteTree.MetadataToggle', 'Metadata'),
            array(
                TextareaField::create("MetaDescription", $this->fieldLabel('MetaDescription')),
                TextareaField::create("ExtraMeta",$this->fieldLabel('ExtraMeta'))
            )
        )->setHeadingLevel(4);

        $fields->addFieldToTab('Root.Main', $additional_field);
        $fields->addFieldToTab('Root.Main', $meta_field);

        // Once product is saved, deal with more complex associations
        if($this->ID) {
            $sortable_field = SortableUploadField::create(
                'Images',
                'Images to use with this product',
                $this->Images()
            );

            $fields->addFieldToTab('Root.Images', $sortable_field);

            // Deal with product features
            $add_button = new GridFieldAddNewInlineButton('toolbar-header-left');
            $add_button->setTitle('Add Attribute');

            $attributes_field = new GridField(
                'Attributes',
                '',
                $this->Attributes(),
                GridFieldConfig::create()
                    ->addComponent(new GridFieldButtonRow('before'))
                    ->addComponent(new GridFieldToolbarHeader())
                    ->addComponent(new GridFieldTitleHeader())
                    ->addComponent(new GridFieldEditableColumns())
                    ->addComponent(new GridFieldDeleteAction())
                    ->addComponent($add_button)
                    ->addComponent(new GridFieldOrderableRows('Sort'))
            );

            $fields->addFieldToTab('Root.Attributes', $attributes_field);

            // Deal with customisations
            $add_button = new GridFieldAddNewButton('toolbar-header-left');
            $add_button->setButtonName('Add Customisation');

            $custom_config = GridFieldConfig::create()->addComponents(
                new GridFieldToolbarHeader(),
                $add_button,
                new GridFieldSortableHeader(),
                new GridFieldDataColumns(),
                new GridFieldPaginator(20),
                new GridFieldEditButton(),
                new GridFieldDeleteAction(),
                new GridFieldDetailForm(),
                new GridFieldOrderableRows('Sort')
            );
            $custom_field = GridField::create('Customisations', '', $this->Customisations(), $custom_config);
            $fields->addFieldToTab('Root.Customisations', $custom_field);

            $related_field = GridField::create(
                'RelatedProducts',
                "",
                $this->RelatedProducts(),
                GridFieldConfig_RelationEditor::create()
            );

            $fields->addFieldToTab('Root.Related', $related_field);
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function getCMSValidator() {
        return new RequiredFields(array("Title","Price"));
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

        // If no images are set, add our default image (if it exists)
        if(!$this->Images()->exists()) {
            $image = Image::get()
                ->filter('Name','no-image.png')
                ->first();

            if($image) {
                $this->Images()->add($image->ID);
            }
        }
    }

    public function onBeforeDelete() {
        // Delete all attributes when this opbect is deleted
        foreach($this->Attributes() as $attribute) {
            $attribute->delete();
        }

        // Delete all customisations when this opbect is deleted
        foreach($this->Customisations() as $cusomisation) {
            $cusomisation->delete();
        }

        parent::onBeforeDelete();
    }

    public function canView($member = false) {
        return true;
    }

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}
