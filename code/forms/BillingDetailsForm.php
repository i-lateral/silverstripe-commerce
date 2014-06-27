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
                TextField::create('FirstName',_t('Commerce.FirstName','First Name(s)') . '*'),
                TextField::create('Surname',_t('Commerce.Surname','Surname') . '*'),
                TextField::create("Company",_t('Commerce.Company',"Company")),
                EmailField::create('Email',_t('Commerce.Email','Email') . '*'),
                TextField::create('PhoneNumber',_t('Commerce.Phone','Phone Number'))
            )->setName("PersonalFields")
            ->addExtraClass('unit')
            ->addExtraClass('size1of2')
            ->addExtraClass('unit-50');

        $address_fields = CompositeField::create(
                HeaderField::create('AddressHeader', _t('Commerce.Address','Address'), 2),
                TextField::create('Address1',_t('Commerce.Address1','Address Line 1') . '*'),
                TextField::create('Address2',_t('Commerce.Address2','Address Line 2')),
                TextField::create('City',_t('Commerce.City','City') . '*'),
                TextField::create('PostCode',_t('Commerce.PostCode','Post Code') . '*'),
                CountryDropdownField::create(
                    'Country',
                    _t('Commerce.Country','Country') . '*',
                    null,
                    'GB'
                )->setAttribute("class",'countrydropdown dropdown btn')
            )->setName("AddressFields")
            ->addExtraClass('unit')
            ->addExtraClass('size1of2')
            ->addExtraClass('unit-50');

        $fields= FieldList::create(
            CompositeField::create(
                $personal_fields,
                $address_fields
            )->setName("BillingFields")
            ->addExtraClass('line')
            ->addExtraClass('units-row')
        );

        $back_url = Controller::join_links(
            BASE_URL,
            ShoppingCart::config()->url_segment
        );

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.Back','Back') . '</a>'
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
        // Set delivery details based billing details
        $delivery_data = array();
        $delivery_data['DeliveryFirstnames'] = $data['FirstName'];
        $delivery_data['DeliverySurname']    = $data['Surname'];
        $delivery_data['DeliveryAddress1']   = $data['Address1'];
        $delivery_data['DeliveryAddress2']   = $data['Address2'];
        $delivery_data['DeliveryCity']       = $data['City'];
        $delivery_data['DeliveryPostCode']   = $data['PostCode'];
        $delivery_data['DeliveryCountry']    = $data['Country'];

        // Save both sets of data to sessions
        Session::set("Commerce.BillingDetailsForm.data",$data);
        Session::set("Commerce.DeliveryDetailsForm.data",$delivery_data);

        $url = $this
            ->controller
            ->Link("finish");

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
        // Save billing data to sessions
        Session::set("Commerce.BillingDetailsForm.data",$data);

        $url = $this
            ->controller
            ->Link("delivery");

        return $this
            ->controller
            ->redirect($url);
    }
}
