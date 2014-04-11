<?php
/**
 * Description of CheckoutForm
 *
 * @author morven
 */
class BillingDetailsForm extends Form {
    public function __construct($controller, $name = "BillingDetailsForm") {

        $personal_fields = CompositeField::create(
                HeaderField::create('PersonalHeader', _t('Commerce.PersonalDetails','Personal Details'), 2),
                TextField::create('FirstName',_t('Commerce.FIRSTNAMES','First Name(s)') . '*'),
                TextField::create('Surname',_t('Commerce.SURNAME','Surname') . '*'),
                EmailField::create('Email',_t('Commerce.EMAIL','Email') . '*'),
                TextField::create('Phone',_t('Commerce.PHONE','Phone Number'))
            )->addExtraClass('unit-50');

        $address_fields = CompositeField::create(
                HeaderField::create('AddressHeader', _t('Commerce.ADDRESS','Address'), 2),
                TextField::create('Address1',_t('Commerce.ADDRESS1','Address Line 1') . '*'),
                TextField::create('Address2',_t('Commerce.ADDRESS2','Address Line 2')),
                TextField::create('City',_t('Commerce.CITY','City') . '*'),
                TextField::create('PostCode',_t('Commerce.POSTCODE','Post Code') . '*'),
                CountryDropdownField::create(
                    'Country',
                    _t('Commerce.COUNTRY','Country') . '*',
                    null,
                    'GB'
                )->setAttribute("class",'countrydropdown dropdown btn')
            )->addExtraClass('unit-50');

        $fields= FieldList::create(
            $personal_fields,
            $address_fields
        );

        $back_url = Controller::join_links(
            BASE_URL,
            ShoppingCart_Controller::$url_segment
        );

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'
            ),

            FormAction::create('doSetDelivery', _t('Commerce.SetDeliveryAddress','Deliver to another address'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next'),

            FormAction::create('doContinue', _t('Commerce.DeliverThisAddress','Deliver to this address'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next')
                ->addExtraClass('btn-green')
        );

        $validator = new RequiredFields(
            'FirstName',
            'Surname',
            'Address1',
            'City',
            'PostCode',
            'Country',
            'Email'
        );

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    /**
     * Method used to save all data to an order and redirect to the order
     * summary page
     *
     * @param $data Form data
     *
     * @return Redirect
     */
    public function doContinue($data) {
        $order = $this->save_data_to_order($data);

        $order->DeliveryFirstnames = $data['FirstName'];
        $order->DeliverySurname    = $data['Surname'];
        $order->DeliveryAddress1   = $data['Address1'];
        $order->DeliveryAddress2   = $data['Address2'];
        $order->DeliveryCity       = $data['City'];
        $order->DeliveryPostCode   = $data['PostCode'];
        $order->DeliveryCountry    = $data['Country'];

        $order->write();

        Session::set('Commerce.Order',$order);

        $url = Controller::join_links(
            Director::absoluteBaseUrl(),
            Commerce_Payment_Controller::$url_segment
        );

        return $this
            ->controller
            ->redirect($url);
    }

    /**
     * Method used to save data (without delivery info) to an order and redirect
     * to the delivery details page
     *
     * @param $data Form data
     *
     * @return Redirect
     */
    public function doSetDelivery($data) {
        $order = $this->save_data_to_order($data);

        Session::set('Commerce.Order',$order);

        $url = $this
            ->controller
            ->Link("delivery");

        return $this
            ->controller
            ->redirect($url);
    }

    /**
     * Method that is responsible for saving subbmited checkout data into an
     * order object
     *
     * @param type $data Array of data submitted
     *
     * @return Order Object
     */
    private function save_data_to_order($data) {
        // Work out if an order prefix string has been set in siteconfig
        $config = SiteConfig::current_site_config();
        $order_prefix = ($config->OrderPrefix) ? $config->OrderPrefix . '-' : '';

        // Save form data into an order object
        $order = new Order();
        $this->saveInto($order);
        $order->Status      = 'incomplete';

        // Load postage data
        $postage = PostageArea::get()->byID(Session::get('Commerce.PostageID'));
        $order->PostageType = $postage->Location;
        $order->PostageCost = $postage->Cost;

        // Add any tax that is needed for postage
        $order->PostageTax = ($config->TaxRate > 0) ? ((float)$postage->Cost / 100) * $config->TaxRate : 0;

        // If user logged in, track it against an order
        if(Member::currentUserID()) $order->CustomerID = Member::currentUserID();

        $order->write(); // Write so we can setup our foreign keys

        // Loop through each session cart item and add that item to the order
        foreach(ShoppingCart::get()->Items() as $cart_item) {
            $order_item = new OrderItem();
            $order_item->Title          = $cart_item->Title;
            $order_item->SKU            = $cart_item->SKU;
            $order_item->Price          = $cart_item->Price;
            $order_item->Tax            = $cart_item->Tax;
            $order_item->Customisation  = serialize($cart_item->Customised);
            $order_item->Quantity       = $cart_item->Quantity;
            $order_item->write();

            $order->Items()->add($order_item);
        }

        return $order;
    }
}
