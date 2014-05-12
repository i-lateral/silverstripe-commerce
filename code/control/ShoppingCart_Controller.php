<?php
/**
 * Description of ShoppingCart_Controller
 *
 * @author morven
 */
class ShoppingCart_Controller extends Commerce_Controller {
    public static $url_segment = 'commerce/cart';

    private static $allowed_actions = array(
        "add",
        "remove",
        "empty",
        "clear",
        "update",
        "CartForm",
        "PostageForm",
        "doGetPostage",
        "doSavePostage"
    );

    public function init() {
        parent::init();
    }

    public function index() {
        $cart_copy = (SiteConfig::current_site_config()->CartCopy) ? SiteConfig::current_site_config()->CartCopy : '';

        $this->customise(array(
            'ClassName' => "ShoppingCart",
            'Title'     => _t('Commerce.CARTNAME', 'Shopping Cart'),
            'MetaTitle' => _t('Commerce.CARTNAME', 'Shopping Cart'),
            'Content'   => $cart_copy
        ));

        return $this->renderWith(array(
            'ShoppingCart',
            'Commerce',
            'Page'
        ));
    }

    /**
     * Remove a product from ShoppingCart Via its ID.
     *
     * @param ID product ID
     */
    public function remove() {
        $key = $this->request->param('ID');

        if(!empty($key)) {
            $cart = ShoppingCart::get();
            $cart->remove($key);
            $cart->save();
        }

        return $this->redirectBack();
    }


    public function CartForm() {
        $form = ShoppingCartForm::create($this, 'CartForm')
            ->addExtraClass('forms');

        $this->extend("updateShopppingCartForm", $form);

        return $form;
    }

    /**
     *
     *
     */
    public function PostageForm() {
        // Setup user postal details
        $fields = new FieldList(
            CompositeField::create(
                CountryDropdownField::create('Country',_t('Commerce.COUNTRY','Country'))
                    ->setAttribute("class",'countrydropdown dropdown btn'),
                TextField::create("ZipCode",_t('Commerce.ZipCode',"Zip/Postal Code"))
            )->addExtraClass("unit-50")
        );

        // Add initial actions
        $actions = new FieldList(
            CompositeField::create(
                FormAction::create("doGetPostage", _t('Commerce.Search',"Search"))
                    ->addExtraClass('btn')
            )->addExtraClass("unit-50")
        );

        // If we have stipulated a search, then see if we have any results
        if($rates = Session::get("Commerce.AvailablePostage")) {
            $fields->add(
                CompositeField::create(
                    OptionsetField::create(
                        "PostageID",
                        _t('Commerce.SelectPostage',"Select Postage"),
                        $rates->map()
                    )
                )->addExtraClass("unit-50")
            );

            $actions->add(
                CompositeField::create(
                    FormAction::create("doSavePostage", _t('Commerce.Confirm',"Confirm"))
                        ->addExtraClass('btn')
                        ->addExtraClass('btn-green')
                )->addExtraClass("unit-50")
            );
        }

        $required = RequiredFields::create(array(
            "Country",
            "ZipCode"
        ));

        $form = Form::create($this, 'PostageForm', $fields, $actions, $required)
            ->addExtraClass('forms')
            ->addExtraClass('forms-columnar');

        // Check if the form has been re-posted and load data
        $data = Session::get("Form_PostageForm.data");
        if(is_array($data)) $form->loadDataFrom($data);

        // Check if the postage area has been set, if so, Set Postage ID
        $data = array();
        $data["PostageID"] = Session::get("Commerce.PostageID");
        if(is_array($data)) $form->loadDataFrom($data);

        $this->extend("updatePostageForm", $form);

        return $form;
    }

    /**
     * Search and find applicable postage rates based on submitted data
     *
     * @param $data
     * @param $form
     */
    public function doGetPostage($data, $form) {
        $country = $data["Country"];
        $codes = $data["ZipCode"];

        $postage_areas = $this->getPostageAreas($country, $codes);

        // Set our postage data into a session so the form can get the relevent fields
        Session::set("Commerce.AvailablePostage", $postage_areas);

        // Set the form pre-populate data before redirecting
        Session::set("Form_PostageForm.data", $data);

        $url = Controller::join_links($this->Link(),"#Form_{$form->Name}");

        return $this->redirect($url);
    }

    /**
     * Save applicable postage data to session
     *
     * @param $data
     * @param $form
     */
    public function doSavePostage($data, $form) {
        Session::set("Commerce.PostageID", $data["PostageID"]);

        $url = Controller::join_links($this->Link(),"#Form_{$form->Name}");

        return $this->redirect($url);
    }
}
