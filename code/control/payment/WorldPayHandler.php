<?php

class WorldPayHandler extends CommercePaymentHandler
{

    /**
     * Default action
     */
    public function index()
    {
        // Setup payment gateway form
        $site = SiteConfig::current_site_config();
        $order = $this->order;

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            "callback",
            $this->payment_gateway->ID
        );

        $back_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Checkout_Controller::config()->url_segment,
            "finish"
        );

        $fields = FieldList::create(
            // Account details
            HiddenField::create('instId', null, $this->payment_gateway->InstallID),
            HiddenField::create('cartId', null, $order->OrderNumber),
            HiddenField::create('MC_callback', null, $callback_url),

            // Amount and Currency details
            HiddenField::create('amount', null, $order->Total),
            HiddenField::create('currency', null, $site->Currency()->GatewayCode),

            // Payee details
            HiddenField::create('name', null, $order->FirstName . " " . $order->Surname),
            HiddenField::create('address1', null, $order->Address1),
            HiddenField::create('address2', null, $order->Address2),
            HiddenField::create('town', null, $order->City),
            HiddenField::create('region', null, $order->State),
            HiddenField::create('postcode', null, $order->PostCode),
            HiddenField::create('country', null, $order->Country),
            HiddenField::create('email', null, $order->Email)
        );

        if ($this->payment_gateway->GatewayMessage) {
            $fields->add(HiddenField::create('desc', null, $this->payment_gateway->GatewayMessage));
        }

        if ($curr_local = str_replace("_", "-", i18n::get_locale())) {
            $fields->add(HiddenField::create('lang', null, $curr_local));
        }

        if (Director::isDev()) {
            $fields->add(HiddenField::create('testMode', null, '100'));
        }

        $actions = FieldList::create(
            LiteralField::create('BackButton', '<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.Back', 'Back') . '</a>'),
            FormAction::create('Submit', _t('Commerce.ConfirmPay', 'Confirm and Pay'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-green')
        );

        $form = Form::create($this, 'Form', $fields, $actions)
            ->addExtraClass('forms')
            ->setFormMethod('POST')
            ->setFormAction($this->payment_gateway->GatewayURL());

        $this->extend('updateForm', $form);


        return array(
            "Title"     => _t('Commerce.CheckoutSummary', "Summary"),
            "MetaTitle" => _t('Commerce.CheckoutSummary', "Summary"),
            "Form"      => $form
        );
    }

    /**
     * Retrieve and process order data from the request
     */
    public function callback()
    {
        $data = $this->request->postVars();

        $success_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            'complete'
        );

        $error_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            'complete',
            'error'
        );

        $vars = array(
            "SiteConfig" => SiteConfig::current_site_config(),
            "RedirectURL" => $error_url
        );

        // Check if CallBack data exists and install id matches the saved ID
        if (
            isset($data) && // Data and order are set
            (isset($data['instId']) && isset($data['cartId']) && isset($data['transStatus']) && isset($data["callbackPW"])) && // check required
            $this->payment_gateway->InstallID == $data['instId'] && // The current install ID matches the postback ID
            $this->payment_gateway->ResponsePassword == $data["callbackPW"]
        ) {
            $order = Order::get()
                ->filter('OrderNumber', $data['cartId'])
                ->first();

            $order_status = $data['transStatus'];

            if ($order) {
                if ($order_status == 'Y') {
                    $order->Status = 'paid';
                    $vars["RedirectURL"] = $success_url;
                } else {
                    $order->Status = 'failed';
                }

                // Store all the data sent from the gateway in a json
                $order->GatewayData = json_encode($data);
                $order->write();
            }
        }

        return $this->renderWith(array("Payment_WorldPay"), $vars);
    }
}
