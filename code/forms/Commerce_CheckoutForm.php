<?php
/**
 * Form that is used to track the details of the person who is placing this
 * order.
 *
 * @author morven
 */
class Commerce_CheckoutForm extends Form {

    public function __construct($controller, $name = "Commerce_CheckoutForm") {
        // If cart is empty, re-direct to homepage
        if(!ShoppingCart::get()->Items())
            $this->redirect(BASE_URL);

        $fields = FieldList::create(

            // Billing Details
            HeaderField::create(
                'BillingHeader',
                _t('Commerce.BILLINGDETAILS','Billing Details'),
                2
            ),
            LiteralField::create("BillingOpen", '<div class="units-row">'),
            FieldGroup::create(
                TextField::create('FirstName',_t('Commerce.FIRSTNAMES','First Name(s)') . '*'),
                TextField::create('Surname',_t('Commerce.SURNAME','Surname') . '*'),
                EmailField::create('Email',_t('Commerce.EMAIL','Email') . '*'),
                TextField::create('PhoneNumber',_t('Commerce.PHONE','Phone Number'))
            )->addExtraClass('unit-50'),
            FieldGroup::create(
                TextField::create('Address1',_t('Commerce.ADDRESS1','Address Line 1') . '*'),
                TextField::create('Address2',_t('Commerce.ADDRESS2','Address Line 2')),
                TextField::create('City',_t('Commerce.CITY','City') . '*'),
                TextField::create('PostCode',_t('Commerce.POSTCODE','Post Code') . '*'),
                CountryDropdownField::create('Country',_t('Commerce.COUNTRY','Country') . '*')
                    ->addExtraClass('btn')
            )->addExtraClass('unit-50'),
            LiteralField::create("BillingClose", '</div>'),
            LiteralField::create("BillingDivider", '<hr/>'),

            // Delivery details
            HeaderField::create(
                "DeliveryHeader",
                _t('Commerce.DELIVERYDETAILS','Delivery Details') . ' (' . _t('Commerce.IFDIFFERENT','if different') . ')'
            ),
            ToggleCompositeField::create(
                "Delivery",
                _t("Commerce.EDITDELIVERYDETAILS","Edit delivery details"),
                array(
                    LiteralField::create("DeliveryOpen", '<div class="units-row">'),
                    FieldGroup::create(
                        TextField::create('DeliveryFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)')),
                        TextField::create('DeliverySurname',_t('Commerce.SURNAME','Surname')),
                        TextField::create('DeliveryAddress1',_t('Commerce.ADDRESS1','Address Line 1')),
                        TextField::create('DeliveryAddress2',_t('Commerce.ADDRESS2','Address Line 2'))
                    )->addExtraClass('unit-50'),
                    FieldGroup::create(
                        TextField::create('DeliveryCity',_t('Commerce.CITY','City')),
                        TextField::create('DeliveryPostCode',_t('Commerce.POSTCODE','Post Code')),
                        CountryDropdownField::create('DeliveryCountry',_t('Commerce.COUNTRY','Country'))->addExtraClass('btn')
                    )->addExtraClass('unit-50'),
                    LiteralField::create("DeliveryClose", '</div>')
                )
            )
        );

        $cart_url = Controller::join_links(
            BASE_URL,
            ShoppingCart_Controller::$url_segment
        );

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $cart_url . '" class="btn btn-red">' . _t('Commerce.BACK','Back') . '</a>'
            ),
            FormAction::create('doPost', _t('Commerce.PAYMENTDETAILS','Enter Payment Details'))
                ->addExtraClass('btn')
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

    public function doPost($data) {
        $order = $this->save_data_to_order();

        Session::set('Order',$order);

        $payment_url = Controller::join_links(
            BASE_URL,
            Payment_Controller::$url_segment
        );

        return $this->controller->redirect($payment_url);
    }

    /**
     * Method that is responsible for saving subbmited checkout data into an
     * order object
     *
     * @param type $form Form submitted
     * @return Order Object
     */
    private function save_data_to_order() {
        // Work out if an order prefix string has been set in siteconfig
        $config = SiteConfig::current_site_config();
        $order_prefix = ($config->OrderPrefix) ? $config->OrderPrefix . '-' : '';

        // Check if delivery details are set. If not, set to billing details.
        $formData = $this->getData();

        $delivery_address = '';

        foreach($formData as $key => $value) {
            if($key == 'DeliveryCountry' && !$delivery_address)
                $formData[$key] = $formData[str_replace('Delivery', 'Billing', $key)];

            if(strstr($key, 'Delivery')) {
                if(!$value)
                    $formData[$key] = $formData[str_replace('Delivery', 'Billing', $key)];
                else
                    $delivery_address .= $value;
            }
        }

        $this->loadDataFrom($formData);

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
