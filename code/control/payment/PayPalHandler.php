<?php

class PayPalHandler extends CommercePaymentHandler {

    public function index() {
        $site = SiteConfig::current_site_config();
        $order = $this->order;

        // Setup the paypal gateway URL
        if(Director::isDev())
            $gateway_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        else
            $gateway_url = "https://www.paypal.com/cgi-bin/webscr";

        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            "callback",
            $this->payment_gateway->ID
        );

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

        $back_url = Controller::join_links(
            BASE_URL,
            Checkout_Controller::config()->url_segment,
            "finish"
        );

        $fields = new FieldList(
            // Account details
            HiddenField::create('business', null, $this->payment_gateway->BusinessID),
            HiddenField::create('item_name', null, $site->Title),
            HiddenField::create('cmd', null, "_cart"),
            HiddenField::create('paymentaction', null, "sale"),
            HiddenField::create('invoice', null, $order->OrderNumber),
            HiddenField::create('custom', null, $order->OrderNumber), //Track the order number in the paypal custom field
            HiddenField::create('upload', null, 1),
            HiddenField::create('discount_amount_cart', null, $order->Total),

            // Amount and Currency details
            HiddenField::create('amount', null, $order->Total),
            HiddenField::create('currency_code', null, $site->Currency()->GatewayCode),

            // Payee details
            HiddenField::create('first_name', null, $order->FirstName),
            HiddenField::create('last_name', null, $order->Surname),
            HiddenField::create('address1', null, $order->Address1),
            HiddenField::create('address2', null, $order->Address2),
            HiddenField::create('city', null, $order->City),
            HiddenField::create('zip', null, $order->PostCode),
            HiddenField::create('country', null, $order->Country),
            HiddenField::create('email', null, $order->Email),

            // Notification details
            HiddenField::create('return', null, $success_url),
            HiddenField::create('notify_url', null, $callback_url),
            HiddenField::create('cancel_return', null, $error_url)
        );

        $i = 1;

        foreach($order->Items() as $item) {
            $fields->add(HiddenField::create('item_name_' . $i, null, $item->Title));
            $fields->add(HiddenField::create('amount_' . $i, null, ($item->Price + $item->Tax)));
            $fields->add(HiddenField::create('quantity_' . $i, null, $item->Quantity));

            $i++;
        }

        // Add shipping as an extra product
        $fields->add(HiddenField::create('item_name_' . $i, null, _t("Commerce.POSTAGE", "Postage")));
        $fields->add(HiddenField::create('amount_' . $i, null, ($order->PostageCost + $order->PostageTax)));
        $fields->add(HiddenField::create('quantity_' . $i, null, "1"));

        $actions = FieldList::create(
            LiteralField::create('BackButton','<a href="' . $back_url . '" class="btn btn-red commerce-action-back">' . _t('Commerce.BACK','Back') . '</a>'),
            FormAction::create('Submit', _t('Commerce.CONFIRMPAY','Confirm and Pay'))
                ->addExtraClass('btn')
                ->addExtraClass('btn-green')
        );

        $form = Form::create($this,'Form',$fields,$actions)
            ->addExtraClass('forms')
            ->setFormMethod('POST')
            ->setFormAction($gateway_url);

        $this->extend('updateForm',$form);

        return array(
            "Title"     => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            "MetaTitle" => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            "Form"      => $form
        );
    }

    /**
     * Process the callback data from the payment provider
     */
    public function callback() {
        $data = $this->request->postVars();

        $success_url = Controller::join_links(
            BASE_URL,
            Payment_Controller::config()->url_segment,
            'complete'
        );

        $error_url = Controller::join_links(
            BASE_URL,
            Payment_Controller::config()->url_segment,
            'complete',
            'error'
        );

        // Check if CallBack data exists and install id matches the saved ID
        if(isset($data) && isset($data['custom']) && isset($data['payment_status'])) {
            $order = Order::get()->filter("OrderNumber", $data['custom'])->first();

            if($order) {
                $request = 'cmd=_notify-validate';

                foreach($data as $key => $value) {
                    $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
                }

                if(Director::isDev())
                    $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
                else
                    $paypal_url = "https://www.paypal.com/cgi-bin/webscr";

                $curl = curl_init($paypal_url);

                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($curl);

                if (!$response)
                    return false;

                if((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0) && isset($data['payment_status'])) {

                    switch($data['payment_status']) {
                        case 'Canceled_Reversal':
                            $order->Status = "canceled";
                            break;
                        case 'Completed':
                            $order->Status = "paid";
                            break;
                        case 'Denied':
                            $order->Status = "failed";
                            break;
                        case 'Expired':
                            $order->Status = "failed";
                            break;
                        case 'Failed':
                            $order->Status = "failed";
                            break;
                        case 'Pending':
                            $order->Status = "pending";
                            break;
                        case 'Processed':
                            $order->Status = "pending";
                            break;
                        case 'Refunded':
                            $order->Status = "canceled";
                            break;
                        case 'Reversed':
                            $order->Status = "canceled";
                            break;
                        case 'Voided':
                            $order->Status = "canceled";
                            break;
                    }
                }

                curl_close($curl);

                // Store all the data sent from the gateway in a json
                $order->GatewayData = json_encode($data);
                $order->PaymentID = $data["txn_id"];
                $order->write();
            }
        }

        return array();
    }

}
