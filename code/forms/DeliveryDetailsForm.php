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
                TextField::create('DeliveryFirstnames',_t('Commerce.FIRSTNAMES','First Name(s)') . '*'),
                TextField::create('DeliverySurname',_t('Commerce.SURNAME','Surname') . '*')
            )->addExtraClass('unit-50');

        $address_fields = CompositeField::create(
                HeaderField::create('AddressHeader', _t('Commerce.ADDRESS','Address'), 2),
                TextField::create('DeliveryAddress1',_t('Commerce.ADDRESS1','Address Line 1') . '*'),
                TextField::create('DeliveryAddress2',_t('Commerce.ADDRESS2','Address Line 2')),
                TextField::create('DeliveryCity',_t('Commerce.CITY','City') . '*'),
                TextField::create('DeliveryPostCode',_t('Commerce.POSTCODE','Post Code') . '*'),
                CountryDropdownField::create(
                    'DeliveryCountry',
                    _t('Commerce.COUNTRY','Country')
                )->setAttribute("class",'countrydropdown dropdown btn')
            )->addExtraClass('unit-50');

        $fields= FieldList::create(
            $personal_fields,
            $address_fields
        );

        $back_url = $controller->Link();

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'
            ),

            FormAction::create('doContinue', _t('Commerce.PAYMENTDETAILS','Enter Payment Details'))
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

    public function doContinue($data, $form) {
        $order = Session::get("Commerce.Order");

        $this->saveInto($order);
        $order->write();

        Session::set('Commerce.Order', $order);

        $url = Controller::join_links(
            Director::absoluteBaseUrl(),
            Payment_Controller::$url_segment
        );

        return $this
            ->controller
            ->redirect($url);
    }
}
