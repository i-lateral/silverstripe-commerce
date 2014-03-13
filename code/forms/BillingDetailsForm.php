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
                TextField::create('BillingFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)') . '*'),
                TextField::create('BillingSurname',_t('Commerce.SURNAME','Surname') . '*'),
                EmailField::create('BillingEmail',_t('Commerce.EMAIL','Email') . '*'),
                TextField::create('BillingPhone',_t('Commerce.PHONE','Phone Number'))
            )->addExtraClass('half');

        $address_fields = CompositeField::create(
                HeaderField::create('AddressHeader', _t('Commerce.ADDRESS','Address'), 2),
                TextField::create('BillingAddress1',_t('Commerce.ADDRESS1','Address Line 1') . '*'),
                TextField::create('BillingAddress2',_t('Commerce.ADDRESS2','Address Line 2')),
                TextField::create('BillingCity',_t('Commerce.CITY','City') . '*'),
                TextField::create('BillingPostCode',_t('Commerce.POSTCODE','Post Code') . '*'),
                CountryDropdownField::create(
                    'BillingCountry',
                    _t('Commerce.COUNTRY','Country') . '*',
                    null,
                    'GB'
                )->setAttribute("class",'countrydropdown dropdown btn')
            )->addExtraClass('half');

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
                '<a href="' . $back_url . '" class="btn commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'
            ),

            FormAction::create('doContinue', _t('Commerce.DeliverThisAddress','Deliver to this address'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next')
                ->addExtraClass('highlight'),

            FormAction::create('doSetDelivery', _t('Commerce.SetDeliveryAddress','Deliver to another address'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next')
        );

        $validator = new RequiredFields(
            'BillingFirstnames',
            'BillingSurname',
            'BillingAddress1',
            'BillingCity',
            'BillingPostCode',
            'BillingCountry',
            'BillingEmail'
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

        $order->DeliveryFirstnames = $data['BillingFirstnames'];
        $order->DeliverySurname    = $data['BillingSurname'];
        $order->DeliveryAddress1   = $data['BillingAddress1'];
        $order->DeliveryAddress2   = $data['BillingAddress2'];
        $order->DeliveryCity       = $data['BillingCity'];
        $order->DeliveryPostCode   = $data['BillingPostCode'];
        $order->DeliveryCountry    = $data['BillingCountry'];

        $order->write();

        Session::set('Order',$order);

        $url = Controller::join_links(
            BASE_URL,
            Payment_Controller::$url_segment
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

        Session::set('Order',$order);

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
        $order->PostageID   = Session::get('PostageID');
        $order->write(); // Write so we can setup our foreign keys

        // Loop through each session cart item and add that item to the order
        foreach(ShoppingCart::get()->Items() as $cart_item) {
            $order_item = new OrderItem();
            $order_item->Title          = $cart_item->Title;
            $order_item->Price          = $cart_item->Price;
            $order_item->Customisation  = serialize($cart_item->Customised);
            $order_item->Quantity       = $cart_item->Quantity;
            $order_item->write();

            $order->Items()->add($order_item);
        }

        return $order;
    }
}
