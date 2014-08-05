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
        "Weight"            => "Decimal",
        "Disabled"          => "Boolean"
    );

    private static $has_many = array(
        "Attributes"    => "ProductAttribute",
        "Customisations"=> "ProductCustomisation"
    );

    private static $many_many = array(
        "Images"        => "Image",
        "RelatedProducts"=>"Product"
    );

    private static $many_many_extraFields = array(
        'Images' => array('SortOrder' => 'Int')
    );

    private static $belongs_many_many = array(
        "Categories"    => "ProductCategory"
    );

    private static $casting = array(
        "MenuTitle"         => "Varchar",
        "CategoriesList"    => "Varchar",
        "ImagesList"        => "Varchar",
        "CMSThumbnail"      => "Varchar",
        "PriceWithTax"      => "Decimal",
        "Tax"               => "Decimal",
        "TaxName"           => "Varchar"
    );

    private static $summary_fields = array(
        "CMSThumbnail"  => "Thumbnail",
        "Title"         => "Title",
        "Quantity"      => "Qty",
        "SKU"           => "SKU",
        "Price"         => "Price",
        "Weight"        => "Weight",
        "CategoriesList"=> "Categories",
        "Disabled"      => "Disabled"
    );

    /**
     * Fields used for CSV Export
     *
     * @config
     */
    private static $export_fields = array(
        "Title"         => "Title",
        "Quantity"      => "Qty",
        "SKU"           => "SKU",
        "Price"         => "Price",
        "Weight"        => "Weight",
        "CategoriesList"=> "Categories",
        "ImagesList"    => "Images",
        "Disabled"      => "Disabled"
    );

    private static $searchable_fields = array(
        "Title",
        "SKU",
        "Description"
    );

    private static $default_sort = "\"Sort\" ASC, \"Title\" ASC";

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
        return $this->SortedImages()->first()->PaddedImage(50,50);
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
        $categories = $this->Categories();
        $i = 1;

        if($categories->exists()){
            foreach($categories as $category) {
                $list .= $category->Title;
                if($i < $categories->count()) $list .= ',';
                $i++;
            }
        }

        return $list;
    }

    public function getImagesList() {
        $list = '';
        $images = $this->SortedImages();
        $i = 1;

        if($images->exists()){
            foreach($images as $image) {
                $list .= $image->Name;
                if($i < $images->count()) $list .= ',';
                $i++;
            }
        }

        return $list;
    }

    public function getCMSFields() {
        $fields = new FieldList(
            $rootTab = new TabSet("Root",
                // Main Tab Fields
                $tabMain = new Tab('Main',
                    TextField::create("Title", $this->fieldLabel('Title')),
                    TextField::create("URLSegment", $this->fieldLabel('URLSegment')),
                    NumericField::create("Price", $this->fieldLabel('Price')),
                    HTMLEditorField::create('Description', $this->fieldLabel('Price'))
                        ->setRows(20)
                        ->addExtraClass('stacked'),
                    ToggleCompositeField::create('AdditionalData', 'Additional Data',
                        array(
                            NumericField::create("Quantity", $this->fieldLabel('Quantity')),
                            TextField::create("SKU", $this->fieldLabel('SKU')),
                            TextField::create("PackSize", $this->fieldLabel('PackSize')),
                            TextField::create("Weight", $this->fieldLabel('Weight'))
                        )
                    )->setHeadingLevel(4),
                    ToggleCompositeField::create('Metadata', _t('CommerceAdmin.MetadataToggle', 'Metadata'),
                        array(
                            $metaFieldDesc = TextareaField::create("MetaDescription", $this->fieldLabel('MetaDescription')),
                            $metaFieldExtra = TextareaField::create("ExtraMeta",$this->fieldLabel('ExtraMeta'))
                        )
                    )->setHeadingLevel(4),
                    CheckboxField::create("Disabled",$this->fieldLabel('Disabled'))
                ),
                $tabImages = new Tab('Images',
                    SortableUploadField::create('Images', $this->fieldLabel('Images'), $this->Images())
                )
            )
        );

        // Help text for MetaData on page content editor
        $metaFieldDesc
            ->setRightTitle(
                _t(
                    'CommerceAdmin.MetaDescHelp',
                    "Search engines use this content for displaying search results (although it will not influence their ranking)."
                )
            )
            ->addExtraClass('help');
        $metaFieldExtra
            ->setRightTitle(
                _t(
                    'CommerceAdmin.MetaExtraHelp',
                    "HTML tags for additional meta information. For example &lt;meta name=\"customName\" content=\"your custom content here\" /&gt;"
                )
            )
            ->addExtraClass('help');


        // Once product is saved, deal with more complex associations
        if($this->ID) {
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

    /**
     * Returns TRUE if this object has a URLSegment value that does not conflict with any other objects. This methods
     * checks for:
     *   - A page with the same URLSegment that has a conflict.
     *   - Conflicts with actions on the parent page.
     *   - A conflict caused by a root page having the same URLSegment as a class name.
     *
     * @return bool
     */
    public function validURLSegment() {
        $objects_to_check = array("SiteTree", "Product", "ProductCategory");

        $segment = Convert::raw2sql($this->URLSegment);

        foreach($objects_to_check as $classname) {
            $return = $classname::get()
                ->filter(array(
                    "URLSegment"=> $segment,
                    "ID:not"    => $this->ID
                ));

            if($return->exists()) return false;
        }

        return true;
    }

    /**
     * Generate a URL segment based on the title provided.
     *
     * If {@link Extension}s wish to alter URL segment generation, they can do so by defining
     * updateURLSegment(&$url, $title).  $url will be passed by reference and should be modified.
     * $title will contain the title that was originally used as the source of this generated URL.
     * This lets extensions either start from scratch, or incrementally modify the generated URL.
     *
     * @param string $title Page title.
     * @return string Generated url segment
     */
    public function generateURLSegment($title){
        $filter = URLSegmentFilter::create();
        $t = $filter->filter($title);

        // Fallback to generic page name if path is empty (= no valid, convertable characters)
        if(!$t || $t == '-' || $t == '-1') $t = "page-$this->ID";

        // Hook for extensions
        $this->extend('updateURLSegment', $t, $title);

        return $t;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        // If there is no URLSegment set, generate one from Title
        if((!$this->URLSegment || $this->URLSegment == 'new-page') && $this->Title) {
            $this->URLSegment = $this->generateURLSegment($this->Title);
        } else if($this->isChanged('URLSegment', 2)) {
            // Do a strict check on change level, to avoid double encoding caused by
            // bogus changes through forceChange()
            $filter = URLSegmentFilter::create();
            $this->URLSegment = $filter->filter($this->URLSegment);
            // If after sanitising there is no URLSegment, give it a reasonable default
            if(!$this->URLSegment) $this->URLSegment = "page-$this->ID";
        }

        // Ensure that this object has a non-conflicting URLSegment value.
        $count = 2;
        while(!$this->validURLSegment()) {
            $this->URLSegment = preg_replace('/-[0-9]+$/', null, $this->URLSegment) . '-' . $count;
            $count++;
        }

        // If no images are set, add our default image (if it exists)
        if(!$this->Images()->exists()) {
            $config = SiteConfig::current_site_config();
            if ($config->NoProductImageID){
                $image = $config->NoProductImage();
            } else {
                $image = Image::get()
                    ->filter('Name','no-image.png')
                    ->first();
            }

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
