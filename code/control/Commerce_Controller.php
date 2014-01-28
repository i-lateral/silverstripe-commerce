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
}
