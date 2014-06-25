<?php
/**
 * Description of CheckoutForm
 *
 * @author morven
 */
class DeliveryDetailsForm extends Form {
    public function __construct($controller, $name = "DeliveryDetailsForm") {

        $personal_fields = CompositeField::create(
                HeaderField::create('PersonalHeader', _t('Commerce.PersonalDetails','Personal Details'), 2),
                TextField::create('DeliveryFirstnames',_t('Commerce.FirstName','First Name(s)') . '*'),
                TextField::create('DeliverySurname',_t('Commerce.Surname','Surname') . '*')
            )->setName("PersonalFields")
            ->addExtraClass('unit-50');

        $address_fields = CompositeField::create(
                HeaderField::create('AddressHeader', _t('Commerce.Address','Address'), 2),
                TextField::create('DeliveryAddress1',_t('Commerce.Address1','Address Line 1') . '*'),
                TextField::create('DeliveryAddress2',_t('Commerce.Address2','Address Line 2')),
                TextField::create('DeliveryCity',_t('Commerce.City','City') . '*'),
                TextField::create('DeliveryPostCode',_t('Commerce.PostCode','Post Code') . '*'),
                CountryDropdownField::create(
                    'DeliveryCountry',
                    _t('Commerce.Country','Country')
                )->setAttribute("class",'countrydropdown dropdown btn')
            )->setName("AddressFields")
            ->addExtraClass('unit-50');

        $fields= FieldList::create(
            CompositeField::create(
                $personal_fields,
                $address_fields
            )->setName("DeliveryFields")
            ->addExtraClass('units-row')
        );

        $back_url = $controller->Link();

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'
            ),

            FormAction::create('doContinue', _t('Commerce.PostageDetails','Select Postage'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next')
                ->addExtraClass('btn-green')
        );

        $validator = new RequiredFields(
            'DeliveryFirstnames',
            'DeliverySurname',
            'DeliveryAddress1',
            'DeliveryCity',
            'DeliveryPostCode',
            'DeliveryCountry'
        );

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    public function doContinue($data) {
        Session::set("Commerce.DeliveryDetailsForm.data",$data);

        $url = $this
            ->controller
            ->Link("finish");

        return $this
            ->controller
            ->redirect($url);
    }
}
