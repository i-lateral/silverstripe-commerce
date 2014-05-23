<?php
/**
 * Description of ShoppingCart_Controller
 *
 * @author morven
 */
class ShoppingCart_Controller extends Commerce_Controller {

    /**
     * Name of the current controller. Mostly used in templates for
     * targeted styling.
     *
     * @var string
     * @config
     */
    private static $class_name = "ShoppingCart";

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

    public function getClassName() {
        return self::config()->class_name;
    }

    public function init() {
        parent::init();
    }

    public function index() {
        $this->extend("onBeforeIndex");

        return $this->renderWith(array(
            'ShoppingCart',
            'Commerce',
            'Page'
        ));
    }

    /**
     * Remove a product from ShoppingCart Via its ID. This action
     * expects an ID to be sent through the URL that matches a specific
     * key added to an item in the cart
     *
     * @return Redirect
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


    /**
     * Form responsible for listing items in the shopping cart and
     * allowing management (such as addition, removal, etc)
     *
     * @return ShoppingCartForm
     */
    public function CartForm() {
        $form = ShoppingCartForm::create($this, 'CartForm')
            ->addExtraClass('forms');

        $this->extend("updateShopppingCartForm", $form);

        return $form;
    }

    /**
     * Form responsible for estimating shipping based on location and
     * postal code
     *
     * @return Form
     */
    public function PostageForm() {
        // Setup default postage fields
        $country_select = CompositeField::create(
            CountryDropdownField::create('Country',_t('Commerce.COUNTRY','Country'))
                ->setAttribute("class",'countrydropdown dropdown btn'),
            TextField::create("ZipCode",_t('Commerce.ZipCode',"Zip/Postal Code"))
        )->addExtraClass("unit-50");

        $search_action = CompositeField::create(
            FormAction::create("doGetPostage", _t('Commerce.Search',"Search"))
                ->addExtraClass('btn')
        )->addExtraClass("unit-50");


        // If we have stipulated a search, then see if we have any results
        // otherwise load empty fieldsets
        if($rates = Session::get("Commerce.AvailablePostage")) {
            $postage_select = CompositeField::create(
                OptionsetField::create(
                    "PostageID",
                    _t('Commerce.SelectPostage',"Select Postage"),
                    $rates->map()
                )
            )->addExtraClass("unit-50");

            $confirm_action = CompositeField::create(
                FormAction::create("doSavePostage", _t('Commerce.Confirm',"Confirm"))
                    ->addExtraClass('btn')
                    ->addExtraClass('btn-green')
            )->addExtraClass("unit-50");
        } else {
            $postage_select = CompositeField::create()->addExtraClass("unit-50");
            $confirm_action = CompositeField::create()->addExtraClass("unit-50");
        }


        // Setup fields and actions
        $fields = new FieldList(
            CompositeField::create($country_select,$postage_select)
                ->addExtraClass("units-row-end")
        );

        $actions = new FieldList(
            CompositeField::create($search_action,$confirm_action)
                ->addExtraClass("units-row-end")
        );

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

        // Extension call
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
