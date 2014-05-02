<?php

/**
 * Top level controller that all commerce controllers should extend. There are
 * some methods that have to be taken from ContentController to allow the
 * Commerce module to operate with just the core framework, or with the CMS.
 *
 * Currently this class acts pretty much as just a container for these classes.
 */
abstract class Commerce_Controller extends Controller {

    /**
     * The URL segment that is matched from the routing rules. This MUST be set
     * on your extension class, as it is used to generate $Link()
     *
     * @var String
     */
    public static $url_segment;

    protected $dataRecord;

    /**
     * Returns the associated database record
     */
    public function data() {
        return $this->dataRecord;
    }

    public function getDataRecord() {
        return $this->data();
    }

    public function setDataRecord($dataRecord) {
        $this->dataRecord = $dataRecord;
        return $this;
    }

    public function Link($action = null) {
        return Controller::join_links(
            BASE_URL,
            $this->stat('url_segment'),
            $action
        );
    }

    /**
     * Init actions that happen globally to all commerce interfaces.
     *
     * At the moment this is used to set config where database access is required
     */
    public function init() {
        // Set the default currency symbol
        if($siteconfig = SiteConfig::current_site_config()) {
            Currency::config()->currency_symbol = $siteconfig->Currency()->HTMLNotation;
        }

        parent::init();
    }

    /**
     * Function to find relevent postage rates, based on supplied country and
     * zip/postal code data.
     *
     * We make this a function as part of Commerce Controller, as there are
     * several points in the payment/order process that will make use of this
     * functionality
     *
     * @param $country String listing the country to search, this has to be an ISO 3166 code
     * @param $zipcode String listing the zip/postage code to filter by
     */
    public function getPostageAreas($country, $zipcode) {
        $return = new ArrayList();
        $countries = new ArrayList();

        $all_rates = $this
            ->SiteConfig()
            ->PostageAreas();

        // First find all area's for this country directly (no wildcards)
        foreach($all_rates as $rate) {
            if(!(strpos(strtolower($rate->Country), strtolower($country)) === false))
                $countries->add($rate);
        }

        // If we have no countries in the list specificly, then check for wildcards
        if(!$countries->exists()) {
            foreach($all_rates as $rate) {
                if($rate->Country == "*") $countries->add($rate);
            }
        }

        // If we have a list of countries check them for post codes
        foreach($countries as $rate) {
            $rate_codes = explode(",",$rate->ZipCode);

            foreach($rate_codes as $rate_to_check) {
                $curr_length = strlen($rate_to_check);
                if(strtolower(substr($zipcode, 0, $curr_length)) == strtolower($rate_to_check)) {
                    $return->add($rate);
                }
            }
        }

        // If we still don't have anything to return, check or list of countries
        // for a wildcard
        if(!$return->exists()) {
            foreach($countries as $rate) {
                if($rate->ZipCode == "*") $return->add($rate);
            }
        }

        return $return;
    }

    /**
     * Process and render search results. This has been hacked a bit to load
     * products into the list (if they exists). Will need to come up with a more
     * elegant solution to dealing with complex searches of objects though.
     *
     * @param array $data The raw request data submitted by user
     * @param SearchForm $form The form instance that was submitted
     * @param SS_HTTPRequest $request Request generated for this action
     */
    public function results($data, $form, $request) {
        $results = $form->getResults();

        // For the moment this will also need to be added to your
        // Page_Controller::results() method (until a more elegant solution can
        // be found
        if(class_exists("Product")) {
            $products = Product::get()->filterAny(array(
                "Title:PartialMatch" => $data["Search"],
                "SKU" => $data["Search"],
                "Description:PartialMatch" => $data["Search"]
            ));

            $results->merge($products);
        }

        $results = $results->sort("Title","ASC");

        $data = array(
            'Results' => $results,
            'Query' => $form->getSearchQuery(),
            'Title' => _t('SearchForm.SearchResults', 'Search Results')
        );

        return $this
            ->owner
            ->customise($data)
            ->renderWith(array(
                'Page_results',
                'SearchResults',
                'Page'
            ));
    }
}
