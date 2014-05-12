<?php

/**
 * CommerceURLController determins what part of Silverstripe (framework, Commerce
 * or CMS) will handle the current URL.
 *
 * @package commerce
 * @subpackage control
 */
class CommerceURLController extends Controller {
    public function init() {
        parent::init();
    }

    public function handleRequest(SS_HTTPRequest $request, DataModel $model) {
        $this->pushCurrent();
        $this->urlParams = $request->allParams();
        $this->request = $request;
        $this->response = new SS_HTTPResponse();
        $this->setDataModel($model);
        $urlsegment = $request->param('URLSegment');

        $this->extend('onBeforeInit');

        $this->init();

        $this->extend('onAfterInit');

        // First check products against URL segment
        if($product = Product::get()->filter(array('URLSegment'=>$urlsegment,'Disabled'=>0))->first()) {
            $controller = Catalogue_Controller::create($product);
        } elseif($category = ProductCategory::get()->filter('URLSegment',$urlsegment)->first()) {
            $controller = Catalogue_Controller::create($category);
        } else {
            // If CMS is installed
            if(class_exists('ModelAsController')) $controller = ModelAsController::create();
        }

        $result = $controller->handleRequest($request, $model);

        $this->popCurrent();

        return $result;
    }
}
