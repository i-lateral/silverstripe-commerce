<?php
/**
 * Description of CheckoutForm
 *
 * @author morven
 */
class PostagePaymentForm extends Form
{
    public function __construct($controller, $name = "PostagePaymentForm")
    {

        // Get delivery data and postage areas from session
        $delivery_data = Session::get("Commerce.DeliveryDetailsForm.data");
        $country = $delivery_data['DeliveryCountry'];
        $postcode = $delivery_data['DeliveryPostCode'];
        $postage_areas = $controller->getPostageAreas($country, $postcode);

        // Loop through all postage areas and generate a new list
        $postage_array = array();
        foreach ($postage_areas as $area) {
            $area_currency = new Currency("Cost");
            $area_currency->setValue($area->Cost);
            $postage_array[$area->ID] = $area->Title . " (" . $area_currency->Nice() . ")";
        }

        $postage_id = (Session::get('Commerce.PostageID')) ? Session::get('Commerce.PostageID') : 0;

        // Setup postage fields
        $postage_field = CompositeField::create(
            HeaderField::create("PostageHeader", _t('Commerce.Postage', "Postage")),
            OptionsetField::create(
                "PostageID",
                _t('Commerce.PostageSelection', 'Please select your prefered postage'),
                $postage_array
            )->setValue($postage_id)
        )->setName("PostageFields")
        ->addExtraClass("unit")
        ->addExtraClass("size1of2")
        ->addExtraClass("unit-50");

        // Get available payment methods and setup payment
        $payment_methods = SiteConfig::current_site_config()->PaymentMethods();

        // Deal with payment methods
        if ($payment_methods->exists()) {
            $payment_map = $payment_methods->map('ID', 'Label');
            $payment_value = $payment_methods->filter('Default', 1)->first()->ID;
        } else {
            $payment_map = array();
            $payment_value = 0;
        }

        $payment_field = CompositeField::create(
            HeaderField::create('PaymentHeading', _t('Commerce.Payment', 'Payment'), 2),
            OptionsetField::create(
                'PaymentMethodID',
                _t('Commerce.PaymentSelection', 'Please choose how you would like to pay'),
                $payment_map,
                $payment_value
            )
        )->setName("PaymentFields")
        ->addExtraClass("unit")
        ->addExtraClass("size1of2")
        ->addExtraClass("unit-50");

        $fields = FieldList::create(
            CompositeField::create(
                $postage_field,
                $payment_field
            )->setName("PostagePaymentFields")
            ->addExtraClass("units-row")
            ->addExtraClass("line")
        );

        $back_url = $controller->Link("billing");

        $actions = FieldList::create(
            LiteralField::create(
                'BackButton',
                '<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.Back', 'Back') . '</a>'
            ),

            FormAction::create('doContinue', _t('Commerce.PaymentDetails', 'Enter Payment Details'))
                ->addExtraClass('btn')
                ->addExtraClass('commerce-action-next')
                ->addExtraClass('btn-green')
        );

        $validator = RequiredFields::create(array(
            "PostageID",
            "PaymentMethod"
        ));

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    public function doContinue($data)
    {
        Session::set('Commerce.PaymentMethodID', $data['PaymentMethodID']);
        Session::set("Commerce.PostageID", $data["PostageID"]);

        $url = Controller::join_links(
            Director::absoluteBaseUrl(),
            Payment_Controller::config()->url_segment
        );

        return $this
            ->controller
            ->redirect($url);
    }
}
