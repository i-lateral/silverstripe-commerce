<?php

class Product extends DataObject {
    private static $db = array(
        'Title'         => 'Varchar',
        'SKU'           => 'Varchar(99)',
        'Quantity'      => 'Int',
        'Price'         => 'Decimal',
        'URLSegment'    => 'Varchar',
        'Description'   => 'HTMLText',
        'Sort'          => 'Int',
        'PackSize'      => 'Varchar',
        'Weight'        => 'Int'
    );

    private static $has_many = array(
        'Attributes'    => 'ProductAttribute',
        'Customisations'=> 'ProductCustomisation'
    );

    private static $many_many = array(
        'Images'        => 'Image'
    );

    private static $many_many_extraFields = array(
        'Images' => array('SortOrder' => 'Int')
    );

    private static $belongs_many_many = array(
        'Categories'    => 'ProductCategory'
    );

    private static $casting = array(
        'CategoriesList'    => 'Varchar',
        'CMSThumbnail'      => 'Varchar'
    );

    private static $summary_fields = array(
        'CMSThumbnail'  => 'Thumbnail',
        'Title'         => 'Title',
        'Quantity'      => 'Qty',
        'SKU'           => 'SKU',
        'Price'         => 'Price',
        'CategoriesList'=> 'Categories'
    );

    private static $default_sort = "\"Title\" ASC";

    /**
     * Return a URL to link to this product (via Catalog_Controller)
     *
     * @return string URL to cart controller
     */
    public function Link(){
        if(Controller::curr()->request->Param('ID'))
            $cat_url = Controller::curr()->request->Param('ID');
        elseif($this->Categories()->First())
            $cat_url = $this->Categories()->First()->URLSegment;
        else
            $cat_url = 'product';

        return Controller::join_links(BASE_URL , $this->URLSegment);
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
     * Overwrite default image and load a not found image if not found
     *
     */
    /*public function getImages() {

    }*/

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

        // Use to display autogenerated URL
        $url_field = TextField::create('URLSegment')
            ->setReadonly(true)
            ->performReadonlyTransformation();

        $fields->addFieldToTab('Root.Main', $url_field, 'Price');

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

        $fields->addFieldToTab('Root.Main', $additional_field);

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
