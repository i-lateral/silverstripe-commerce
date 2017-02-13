<?php

/**
 * Controller used to render the checkout process
 *
 */
class Checkout_Controller extends Commerce_Controller
{

    /**
     * Name of the current controller. Mostly used in templates for
     * targeted styling.
     *
     * @var string
     * @config
     */
    private static $class_name = "Checkout";

    /**
     * @var string
     * @config
     */
    private static $url_segment = "commerce/checkout";

    private static $allowed_actions = array(
        "billing",
        "delivery",
        "usememberaddress",
        "finish",
        "LoginForm",
        'BillingForm',
        'DeliveryForm',
        "PostagePaymentForm"
    );

    public function getClassName()
    {
        return self::config()->class_name;
    }

    public function init()
    {
        parent::init();

        // If no shopping cart doesn't exist, redirect to base
        if (!ShoppingCart::create()->getItems()->exists()) {
            return $this->redirect(ShoppingCart::config()->url_segment);
        }
    }

    /**
     * If user logged in, redirect to billing info, else show login, register
     * or "checkout as guest" options.
     *
     */
    public function index()
    {
        if (Member::currentUserID()) {
            return $this->redirect($this->Link('billing'));
        }

        $this->customise(array(
            'Title'     => _t('CommerceAccount.SignIn', "Sign in"),
            "Login"     => true,
            'Content'   => $this->renderWith("Commerce_Checkout_Login"),
            'LoginForm' => $this->LoginForm()
        ));

        $this->extend("onBeforeIndex");

        return $this->renderWith(array(
            'Checkout',
            'Commerce',
            'Page'
        ));
    }


    /**
     * Catch the default dilling information of the visitor
     *
     * @return array
     */
    public function billing()
    {
        $form = $this->BillingForm();

        // Pre populate form with member info
        if (Member::currentUserID()) {
            $form->loadDataFrom(Member::currentUser());
        }

        $this->customise(array(
            'Title'     => _t('Commerce.BillingDetails', "Billing Details"),
            'Form'      => $form
        ));

        $this->extend("onBeforeBilling");

        return $this->renderWith(array(
            'Checkout_billing',
            'Commerce',
            'Page'
        ));
    }


    /**
     * Use to catch the users delivery details, if different to their billing
     * details
     *
     * @var array
     */
    public function delivery()
    {
        $this->customise(array(
            'Title'     => _t('Commerce.DeliveryDetails', "Delivery Details"),
            'Form'      => $this->DeliveryForm()
        ));

        $this->extend("onBeforeDelivery");

        return $this->renderWith(array(
            'Checkout_delivery',
            'Commerce',
            'Page'
        ));
    }

    /**
     * User the address provided via the $ID param in the URL. The
     * $OtherID param is used to determine if the address is billing
     * or delivery.
     *
     * If no $ID or $OtherID is provided, we return an error.
     *
     * @return redirect
     */
    public function usememberaddress()
    {
        $allowed_otherids = array("billing","delivery");
        $id = $this->request->param("ID");
        $otherid = $this->request->param("OtherID");
        $data = array();
        $member = Member::currentUser();
        $address = MemberAddress::get()->byID($id);
        $action = "billing";

        // If our required details are not set, return a server error
        if (
            !$address ||
            !$member ||
            ($address && !$address->canView($member)) ||
            !in_array($otherid, $allowed_otherids)
        ) {
            return $this
                ->httpError(
                    404,
                    "There was an error selecting your address"
                );
        }

        // Set the session data
        if ($otherid == "billing") {
            $data["FirstName"]  = $address->FirstName;
            $data["Surname"]    = $address->Surname;
            $data["Address1"]   = $address->Address1;
            $data["Address2"]   = $address->Address2;
            $data["City"]       = $address->City;
            $data["PostCode"]   = $address->PostCode;
            $data["Country"]    = $address->Country;
            $data["Email"]      = $member->Email;
            $data["PhoneNumber"]= $member->PhoneNumber;
            $data["Company"]    = $member->Company;

            Session::set("Commerce.BillingDetailsForm.data", $data);
            $action = "delivery";
        }

        if ($otherid == "delivery") {
            $data['DeliveryFirstnames']  = $address->FirstName;
            $data['DeliverySurname']    = $address->Surname;
            $data['DeliveryAddress1']   = $address->Address1;
            $data['DeliveryAddress2']   = $address->Address2;
            $data['DeliveryCity']       = $address->City;
            $data['DeliveryPostCode']   = $address->PostCode;
            $data['DeliveryCountry']    = $address->Country;

            Session::set("Commerce.DeliveryDetailsForm.data", $data);
            $action = "finish";
        }

        $this->extend("onBeforeUseMemberAddress");

        return $this->redirect($this->Link($action));
    }


    /**
     * Final step, allowing user to select postage and payment method
     *
     * @return array
     */
    public function finish()
    {
        // Check the users details are set, if not, send them to the cart
        $billing_data = Session::get("Commerce.BillingDetailsForm.data");
        $delivery_data = Session::get("Commerce.DeliveryDetailsForm.data");

        if (!is_array($billing_data) && !is_array($delivery_data)) {
            return $this->redirect("index");
        }

        $form = $this->PostagePaymentForm();

        $this->customise(array(
            'Title'     => _t('Commerce.PostagePayment', "Postage and Payment"),
            'Form'      => $form
        ));

        $this->extend("onBeforeFinish");

        return $this->renderWith(array(
            'Checkout_finish',
            'Commerce',
            'Page'
        ));
    }

    /**
     * Generate a login form
     *
     * @return MemberLoginForm
     */
    public function LoginForm()
    {
        $form = CommerceLoginForm::create($this, 'LoginForm');
        $form->setAttribute("action", $this->Link("LoginForm"));

        $form
            ->Fields()
            ->add(HiddenField::create("BackURL")->setValue($this->Link()));

        $form
            ->Actions()
            ->dataFieldByName('action_dologin')
            ->addExtraClass("btn");

        $this->extend("updateLoginForm", $form);

        return $form;
    }

    /**
     * Form to capture the users billing details
     *
     * @return BillingDetailsForm
     */
    public function BillingForm()
    {
        $form = BillingDetailsForm::create($this, 'BillingForm')
            ->addExtraClass('forms')
            ->addExtraClass('columnar')
            ->addExtraClass('row');

        $data = Session::get("Commerce.BillingDetailsForm.data");
        if (is_array($data)) {
            $form->loadDataFrom($data);
        }

        $this->extend("updateBillingForm", $form);

        return $form;
    }

    /**
     * Form to capture users delivery details
     *
     * @return DeliveryDetailsForm
     */
    public function DeliveryForm()
    {
        $form = DeliveryDetailsForm::create($this, 'DeliveryForm')
            ->addExtraClass('forms')
            ->addExtraClass('columnar')
            ->addExtraClass('row');

        $data = Session::get("Commerce.DeliveryDetailsForm.data");
        if (is_array($data)) {
            $form->loadDataFrom($data);
        }

        $this->extend("updateDeliveryForm", $form);

        return $form;
    }

    /**
     * Form to find postage options and allow user to select payment
     *
     * @return PostagePaymentForm
     */
    public function PostagePaymentForm()
    {
        $form = PostagePaymentForm::create($this, "PostagePaymentForm")
            ->addExtraClass("forms");

        $this->extend("updatePostagePaymentForm", $form);

        return $form;
    }
}
