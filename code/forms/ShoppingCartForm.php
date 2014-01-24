<?php
/**
 * Description of CartForm
 *
 * @author morven
 */
class Commerce_ShoppingCartForm extends Form {
    protected $cart;

    public function __construct($controller, $name = "Commerce_ShoppingCartForm") {
        // Set shopping cart
        $this->cart = ShoppingCart::get();

        $postage_areas = SiteConfig::current_site_config()->PostageAreas();
        $payment_methods = SiteConfig::current_site_config()->PaymentMethods();

        // Deal with setting up postage areas
        if($postage_areas->exists()) {
            $postage_map = $postage_areas->map('ID','Location');
            $postage_value = Session::get('PostageID');
        } else {
            $postage_map = array();
            $postage_value = 0;
        }

        // Deal with payment methods
        if($payment_methods->exists()) {
            $payment_map = $payment_methods->map('ID','Label');
            $payment_value = $payment_methods->filter('Default',1)->first()->ID;
        } else {
            $payment_map = array();
            $payment_value = 0;
        }

        $fields = new FieldList(
            // Postage
            HeaderField::create('PostageHeading', _t('Commerce.POSTAGE', 'Postage'), 2),
            DropdownField::create('Postage', _t('Commerce.CARTLOCATION', 'Please choose location to post to'), $postage_map)
                ->addExtraClass('btn')
                ->setValue($postage_value)
                ->setEmptyString(_t('Commerce.PLEASESELECT','Please Select')),

            // Payment Gateways
            HeaderField::create('PaymentHeading', _t('Commerce.PAYMENT', 'Payment'), 2),
            OptionsetField::create('PaymentMethod', _t('Commerce.PAYMENTSELECTION', 'Please choose how you would like to pay'), $payment_map, $payment_value)
        );

        $actions = new FieldList(
            FormAction::create('doEmpty', _t('Commerce.CARTEMPTY','Empty Cart'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-red'),
            FormAction::create('doUpdate', _t('Commerce.CARTUPDATE','Update Cart'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-blue')
        );

        if($payment_methods->exists()) {
            $actions->add(FormAction::create('doCheckout', _t('Commerce.CARTPROCEED','Proceed to Checkout'))
                ->addExtraClass('btn')
                ->addExtraClass('highlight')
            );
        }

        $validator = new RequiredFields(
            'Postage',
            'PaymentMethod'
        );

        parent::__construct($controller, $name, $fields, $actions, $validator);

        // If postage is in session, overwrite default error message
        if($postage_value) $fields->dataFieldByName('Postage')->setError(null,null);
    }

    public function forTemplate() {
        return $this->renderWith(array(
            $this->class,
            'Form'
        ));
    }

    public function getCart() {
        return $this->cart;
    }

    /**
     * Get the currency for the current sub site
     *
     * @return string
     */
    public function getCurrencySymbol() {
        return (SiteConfig::current_site_config()->Currency()) ? SiteConfig::current_site_config()->Currency()->HTMLNotation : false;
    }

    /**
     * Action that will check each item in the existing cart, and update the
     * quantity if required.
     *
     * If the quantity is set to 0, then the item is removed from the cart.
     *
     * @param type $data
     * @param type $form
     */
    private function update_cart($data) {
        foreach($this->cart->Items() as $cart_item) {
            foreach($data as $key => $value) {
                $sliced_key = explode("_", $key);
                if($sliced_key[0] == "Quantity") {
                    if(isset($cart_item) && ($cart_item->Key == $sliced_key[1])) {
                        if($value > 0) {
                            $this->cart->update($cart_item->Key,$value);
                        } else
                            $this->cart->remove($cart_item->Key);
                    }
                }
            }
        }

        $this->cart->save();

        // If set, update Postage
        if($data['Postage'])
            Session::set('PostageID', $data['Postage']);
    }

    public function getItems() {
        $items = new ArrayList();

        foreach($this->cart->Items() as $item) {
            // Create a list for customisations, with some casting added
            $customised_list = new ArrayList();

            foreach($item->Customised as $customised) {
                $customised_list->add(new ArrayData(array(
                    'Title' => DBField::create_field('Varchar', $customised->Title),
                    'Value' => nl2br(Convert::raw2xml($customised->Value), true),
                    'ClassName' => Convert::raw2url($customised->Title)
                )));
            }

            $items->add(new ArrayData(array(
                'Key' => $item->Key,
                'Title' => DBField::create_field('Varchar', $item->Title),
                'Description' => nl2br(Convert::raw2xml($item->Description), true),
                'Customised' => $customised_list,
                'Price' => DBField::create_field('Decimal', $item->Price),
                'Quantity' => DBField::create_field('Int', $item->Quantity),
                'Image' => Image::get()->byID($item->ImageID),
            )));
        }

        return $items;
    }

    /**
     * Generate a total cost from all the items in the cart session.
     *
     * @return Int
     */
    public function getCartTotal() {
        $total = $this->cart->TotalPrice();

        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            $total += PostageArea::get()->byID(Session::get('PostageID'))->Cost;

        return money_format('%i',$total);

    }

    public function getPostageCost() {
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0) {
            return money_format(
                '%i',
                DataObject::get_by_id('PostageArea',
                Session::get('PostageID'))->Cost
            );
        } else
            return false;
    }

    /**
     * Action that will update cart
     *
     * @param type $data
     * @param type $form
     */
    public function doUpdate($data) {
        $this->update_cart($data);

        $this->controller->redirectBack();
    }

    /**
     * Action that will update cart and move to checkout
     *
     * @param type $data
     * @param type $form
     */
    public function doCheckout($data) {
        $this->update_cart($data);

        Session::set('PaymentMethod', $data['PaymentMethod']);

        $this->controller->redirect(Controller::join_links(
            BASE_URL,
            Checkout_Controller::$url_segment
        ));
    }

    /**
     * Action that will clear shopping cart and associated sessions
     *
     */
    public function doEmpty() {
        $this->cart->clear();

        Session::clear('PostageID');
        unset($_SESSION['PostageID']);

        Session::clear('PaymentMethod');
        unset($_SESSION['PaymentMethod']);

        return $this->controller->redirectBack();
    }
}
