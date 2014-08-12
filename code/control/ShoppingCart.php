<?php

/**
 * Holder for items in the shopping cart and interacting with them, as
 * well as rendering these items into an interface that allows editing
 * of items,
 *
 * @author morven
 * @package commerce
 */
class ShoppingCart extends Commerce_Controller {

    /**
     * URL Used to access this controller
     *
     * @var string
     * @config
     */
    private static $url_segment = 'commerce/cart';

    /**
     * Name of the current controller. Mostly used in templates.
     *
     * @var string
     * @config
     */
    private static $class_name = "ShoppingCart";

    private static $allowed_actions = array(
        "remove",
        "emptycart",
        "clear",
        "update",
        "usediscount",
        "CartForm",
        "PostageForm",
        "DiscountForm"
    );

    /**
     * Determines if the shopping cart is currently enabled
     *
     * @var boolean
     * @config
     */
    protected static $enabled = true;

    /**
     * Overwrite the default title for this controller which is taken
     * from the translation files. This is used for Title and MetaTitle
     * variables in templates.
     *
     * @var string
     * @config
     */
    private static $title;

    /**
     * Track all items stored in the current shopping cart
     *
     * @var ArrayList
     */
    protected $items;

    /**
     * Track a discount object placed against this cart
     *
     * @var ArrayList
     */
    protected $discount;

    /**
     * Show the discount form on the shopping cart
     *
     * @var boolean
     * @config
     */
    private static $show_discount_form = false;


    /**
     * Getters and setters
     *
     */
    public function getClassName() {
        return self::config()->class_name;
    }

    public function getTitle() {
        return ($this->config()->title) ? $this->config()->title : _t("Commerce.CartName", "Shopping Cart");
    }

    public function getMetaTitle() {
        return $this->getTitle();
    }

    public function getShowDiscountForm() {
        return $this->config()->show_discount_form;
    }

    public function getItems() {
        return $this->items;
    }

    public function getDiscount() {
        return $this->discount;
    }

    public function setDiscount(Discount $discount) {
        $this->discount = $discount;
    }

    /**
     * Set postage that is available to the shopping cart based on the
     * country and zip code submitted
     *
     * @param $country 2 character country code
     * @param $code Zip or Postal code
     * @return ShoppingCart
     */
    public function setAvailablePostage($country, $code) {
        // Set postage data from commerce_controller and save into a session
        $postage_areas = $this->getPostageAreas($country, $code);
        Session::set("Commerce.AvailablePostage", $postage_areas);

        return $this;
    }

    /**
     * find out if the shopping cart is enabled
     *
     * @return Boolean
     */
    public static function isEnabled() {
        return self::config()->enabled;
    }

    /**
     * Shortcut for ShoppingCart::create, exists because create()
     * doesn't seem quite right.
     *
     * @return ShoppingCart
     */
    public static function get() {
        return ShoppingCart::create();
    }


    public function __construct() {
        // If items are stored in a session, get them now
        if(Session::get('Commerce.ShoppingCart.Items'))
            $this->items = unserialize(Session::get('Commerce.ShoppingCart.Items'));
        else
            $this->items = ArrayList::create();

        // If discounts stored in a session, get them, else create new list
        if(Session::get('Commerce.ShoppingCart.Discount'))
            $this->discount = unserialize(Session::get('Commerce.ShoppingCart.Discount'));

        // If we don't have any discounts, a user is logged in and he has
        // access to discounts through a group, add the discount here
        if(!$this->discount && Member::currentUserID()) {
            $member = Member::currentUser();
            $this->discount = $member->getDiscount();
            Session::set('Commerce.ShoppingCart.Discount', serialize($this->discount));
        }

        parent::__construct();
    }

   /**
    * Actions for this controller
    */

    /**
     * Default acton for the shopping cart
     */
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
            foreach($this->items as $item) {
                if($item->Key == $key)
                    $this->items->remove($item);
            }

            $this->save();
        }

        return $this->redirectBack();
    }

    /**
     * Action that will clear shopping cart and associated sessions
     *
     */
    public function emptycart() {
        $this->extend("onBeforeEmpty");
        $this->removeAll();
        $this->save();

        return $this->redirectBack();
    }


    /**
     * Action used to add a discount to the users session via a URL.
     * This is preferable to using the dicount form as disount code
     * forms seem to provide a less than perfect user experience
     *
     */
    public function usediscount() {
        $this->extend("onBeforeUseDiscount");

        $code_to_search = $this->request->param("ID");

        if(!$code_to_search)
            return $this->httpError(404, "Page not found");

        // First check if the discount is already added (so we don't
        // query the DB if we don't have to).
        if(!$this->discount || ($this->discount && $this->discount->Code != $code_to_search)) {
            $code = Discount::get()
                ->filter("Code", $code_to_search)
                ->exclude("Expires:LessThan", date("Y-m-d"))
                ->first();

            if($code) $this->discount = $code;
        }

        // If discount is set, save cart
        if($this->discount) $this->save();

        return $this
            ->customise(array(
                "Discount" => $this->discount
            ))->renderWith(array(
                'ShoppingCart_discount',
                'Commerce',
                'Page'
            ));
    }

    /**
     * Add a product to the shopping cart via its ID number.
     *
     * @param Item Product Object you wish to add
     * @param Quantity number of this item to add
     * @param Customise array of custom options for this product, needs to be a
     *        multi dimensional array with each item of format:
     *          -  "Title" => (str)"Item title"
     *          -  "Value" => (str)"Item Value"
     *          -  "ModifyPrice" => (float)"Modification to price"
     */
    public function add(Product $add_item, $quantity = 1, $customise = array()) {
        $added = false;
        $config = SiteConfig::current_site_config();

        // Make a string to match id's against ones already in the cart
        $product_key = ($customise) ? (int)$add_item->ID . ':' . base64_encode(serialize($customise)) : (int)$add_item->ID;

        // Check if the add call is trying to add an item already in the cart,
        // if so update the current quantity
        foreach($this->items as $item) {
            // If an instance of this is already in the shopping basket, increase
            if($item->Key == $product_key) {
                $this->update($item->Key, ($item->Quantity + $quantity));
                $added = true;
            }
        }

        // If no update was sucessfull, update records
        if(!$added) {
            $custom_data = new ArrayList();
            $price = $add_item->Price;
            (float)$tax_rate = $config->TaxRate;

            foreach($customise as $custom_item) {
                $custom_data->add(new ArrayData(array(
                    'Title' => ucwords(str_replace(array('-','_'), ' ', $custom_item["Title"])),
                    'Value' => $custom_item["Value"],
                    'ModifyPrice' => $custom_item['ModifyPrice']
                )));

                // If a customisation modifies price, adjust the price
                $price = (float)$price + (float)$custom_item['ModifyPrice'];
            }

            // Now, caclulate tax based on the new modified price and tax rate
            if($tax_rate > 0)
                (float)$tax = ($price / 100) * $tax_rate; // Get our tax amount from the price
            else
                (float)$tax = 0;

            $item_to_add = ArrayData::create(array(
                'Key'           => $product_key,
                'ProductID'     => $add_item->ID,
                'Title'         => $add_item->Title,
                'SKU'           => $add_item->SKU,
                'Description'   => $add_item->Description,
                'Weight'        => $add_item->Weight,
                'Price'         => number_format($price,2),
                'Tax'           => number_format($tax, 2),
                'Customised'    => $custom_data,
                'Image'         => $add_item->Images()->first(),
                'Quantity'      => $quantity
            ));

            $this->extend("onBeforeAdd", $item_to_add);

            $this->items->add($item_to_add);

            $this->extend("onAfterAdd");
        }
    }

    /**
     * Find an existing item and update its quantity
     *
     * @param Item
     * @param Quantity
     */
    public function update($item_key, $quantity) {
        foreach($this->items as $item) {
            if ($item->Key === $item_key) {
                $this->extend("onBeforeUpdate", $item);

                $item->Quantity = $quantity;

                $this->extend("onAfterUpdate", $item);
                return true;
            }
        }

        $this->save();

        return false;
     }

    /**
     * Empty the shopping cart object of all items.
     *
     */
    public function removeAll() {
        foreach($this->items as $item) {
            $this->items->remove($item);
        }
    }

    /**
     * Save the current products list and postage to a session.
     *
     */
    public function save() {
        Session::clear("Commerce.PostageID");

        // Save cart items
        Session::set(
            "Commerce.ShoppingCart.Items",
            serialize($this->items)
        );

        // Save cart discounts
        Session::set(
            "Commerce.ShoppingCart.Discount",
            serialize($this->discount)
        );

        // Update available postage
        if($data = Session::get("Form.Form_PostageForm.data")) {
            $country = $data["Country"];
            $code = $data["ZipCode"];
            $this->setAvailablePostage($country, $code);
        }
    }

    /**
     * Clear the shopping cart object and destroy the session. Different to
     * empty, as that retains the session.
     *
     */
    public function clear() {
        Session::clear('Commerce.ShoppingCart.Items');
        Session::clear('Commerce.ShoppingCart.Discount');
        Session::clear("Commerce.PostageID");
    }

    /**
     * Find the total weight of all items in the shopping cart
     *
     * @return Float
     */
    public function TotalWeight() {
        $total = 0;

        foreach($this->items as $item) {
            $total = $total + ($item->Weight * $item->Quantity);
        }

        return $total;
    }

    /**
     * Find the total quantity of items in the shopping cart
     *
     * @return Int
     */
    public function TotalItems() {
        $total = 0;

        foreach($this->items as $item) {
            $total = $total + $item->Quantity;
        }

        return $total;
    }

    /**
     * Find the cost of all items in the cart, without any tax.
     *
     * @return Float
     */
    public function SubTotalCost() {
        $total = 0;

        foreach($this->items as $item) {
            $total = $total + ($item->Quantity * $item->Price);
        }

        return number_format($total,2);
    }

    /**
     * Get the cost of postage
     *
     */
    public function PostageCost() {
        if($postage = PostageArea::get()->byID(Session::get("Commerce.PostageID")))
            $cost = $postage->Cost;
        else
            $cost = 0;

        return number_format($cost,2);
    }

    /**
     * Find the total discount based on discount items added.
     *
     * @return Float
     */
    public function DiscountAmount() {
        $total = 0;
        $discount = $this->discount;

        if($discount) {
            if($discount->Type == "Fixed")
                $total += $discount->Amount;
            elseif($discount->Type == "Percentage" && $discount->Amount)
                $total += (($discount->Amount / 100) * $this->SubTotalCost());
        }

        return number_format($total,2);
    }

    /**
     * Find the total cost of tax for the items in the cart, as well as shipping
     * (if set)
     *
     * @return Float
     */
    public function TaxCost() {
        // Add any tax that is needed for postage
        $config = SiteConfig::current_site_config();
        $total = 0;

        if($config->TaxRate > 0) {
            // Find tax on items
            foreach($this->items as $item) {
                if($item->Tax > 0) $total += ($item->Quantity * $item->Tax);
            }

            // Now find tax on postage
            $postage = $this->PostageCost();
            $total += ($postage > 0) ? ((float)$postage / 100) * $config->TaxRate : 0;
        }

        return  number_format($total,2);
    }

    /**
     * Find the total cost of for all items in the cart, including tax and
     * shipping (if applicable)
     *
     * @return Float
     */
    public function TotalCost() {
        $total = str_replace(",","",$this->SubTotalCost());
        $discount = str_replace(",","",$this->DiscountAmount());
        $postage = str_replace(",","",$this->PostageCost());
        $tax = str_replace(",","",$this->TaxCost());

        // If discount is less than 0, then set to 0
        $total_with_discount = (((float)$total - (float)$discount) < 0) ? 0 : ((float)$total - (float)$discount);

        $total = $total_with_discount + (float)$postage + (float)$tax;

        return number_format($total,2);
    }


    /**
     * Form responsible for listing items in the shopping cart and
     * allowing management (such as addition, removal, etc)
     *
     * @return Form
     */
    public function CartForm() {
        $fields = new FieldList();

        $actions = new FieldList(
            FormAction::create('doUpdate', _t('Commerce.CartUpdate','Update Cart'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-blue')
        );

        $form = Form::create($this, "CartForm", $fields, $actions)
            ->addExtraClass("forms")
            ->setTemplate("ShoppingCartForm");

        $this->extend("updateCartForm", $form);

        return $form;
    }

    /**
     * Form that allows you to add a discount code which then gets added
     * to the cart's list of discounts.
     *
     * @return Form
     */
    public function DiscountForm() {
        $fields = new FieldList(
            TextField::create(
                "DiscountCode",
                _t("Commerce.DiscountCode", "Discount Code")
            )->setAttribute(
                "placeholder",
                _t("Commerce.EnterDiscountCode", "Enter a discount code")
            )
        );

        $actions = new FieldList(
            FormAction::create('doAddDiscount', _t('Commerce.Add','Add'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-blue')
        );

        $form = Form::create($this, "DiscountForm", $fields, $actions)
            ->addExtraClass("forms");

        $this->extend("updateDiscountForm", $form);

        return $form;
    }

    /**
     * Form responsible for estimating shipping based on location and
     * postal code
     *
     * @return Form
     */
    public function PostageForm() {
        $available_postage = Session::get("Commerce.AvailablePostage");

        // Setup default postage fields
        $country_select = CompositeField::create(
            CountryDropdownField::create('Country',_t('Commerce.Country','Country'))
                ->setAttribute("class",'countrydropdown dropdown btn'),
            TextField::create("ZipCode",_t('Commerce.ZipCode',"Zip/Postal Code"))
        )->addExtraClass("size1of2")
        ->addExtraClass("unit")
        ->addExtraClass("unit-50");

        // If we have stipulated a search, then see if we have any results
        // otherwise load empty fieldsets
        if($available_postage) {
            $search_text = _t('Commerce.Update',"Update");

            $postage_select = CompositeField::create(
                OptionsetField::create(
                    "PostageID",
                    _t('Commerce.SelectPostage',"Select Postage"),
                    $available_postage->map()
                )
            )->addExtraClass("size1of2")
            ->addExtraClass("unit")
            ->addExtraClass("unit-50");

            $confirm_action = CompositeField::create(
                FormAction::create("doSavePostage", _t('Commerce.Confirm',"Confirm"))
                    ->addExtraClass('btn')
                    ->addExtraClass('btn-green')
            )->addExtraClass("size1of2")
            ->addExtraClass("unit")
            ->addExtraClass("unit-50");
        } else {
            $search_text = _t('Commerce.Search',"Search");
            $postage_select = CompositeField::create()
                ->addExtraClass("size1of2")
                ->addExtraClass("unit")
                ->addExtraClass("unit-50");
            $confirm_action = CompositeField::create()
                ->addExtraClass("size1of2")
                ->addExtraClass("unit")
                ->addExtraClass("unit-50");
        }

        // Set search field
        $search_action = CompositeField::create(
            FormAction::create("doGetPostage", $search_text)
                ->addExtraClass('btn')
        )->addExtraClass("size1of2")
        ->addExtraClass("unit")
        ->addExtraClass("unit-50");


        // Setup fields and actions
        $fields = new FieldList(
            CompositeField::create($country_select,$postage_select)
                ->addExtraClass("line")
                ->addExtraClass("units-row-end")
        );

        $actions = new FieldList(
            CompositeField::create($search_action,$confirm_action)
                ->addExtraClass("line")
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
        $data = Session::get("Form.{$form->FormName()}.data");
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
     * Action that will update cart
     *
     * @param type $data
     * @param type $form
     */
    public function doUpdate($data, $form) {
        foreach($this->items as $cart_item) {
            foreach($data as $key => $value) {
                $sliced_key = explode("_", $key);
                if($sliced_key[0] == "Quantity") {
                    if(isset($cart_item) && ($cart_item->Key == $sliced_key[1])) {
                        if($value > 0) {
                            $this->update($cart_item->Key, $value);
                        } else
                            $this->remove($cart_item->Key);
                    }
                }
            }
        }

        $this->save();

        return $this->redirectBack();
    }

    /**
     * Action that will find a discount based on the code
     *
     * @param type $data
     * @param type $form
     */
    public function doAddDiscount($data, $form) {
        $code_to_search = $data['DiscountCode'];

        // First check if the discount is already added (so we don't
        // query the DB if we don't have to).
        if(!$this->discount || ($this->discount && $this->discount->Code != $code_to_search)) {
            $code = Discount::get()
                ->filter("Code", $code_to_search)
                ->exclude("Expires:LessThan", date("Y-m-d"))
                ->first();

            if($code) $this->discount = $code;
        }

        $this->save();

        return $this->redirectBack();
    }

    /**
     * Search and find applicable postage rates based on submitted data
     *
     * @param $data
     * @param $form
     */
    public function doGetPostage($data, $form) {
        $country = $data["Country"];
        $code = $data["ZipCode"];

        $this->setAvailablePostage($country, $code);

        // Set the form pre-populate data before redirecting
        Session::set("Form.{$form->FormName()}.data", $data);

        $url = Controller::join_links($this->Link(),"#{$form->FormName()}");

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

        $url = Controller::join_links($this->Link(),"#{$form->FormName()}");

        return $this->redirect($url);
    }
}
