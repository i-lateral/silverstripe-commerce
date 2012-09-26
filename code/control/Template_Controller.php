<?php
/**
 * Description of TemplateController
 *
 * @author morven
 */
class Template_Controller extends ContentController {
    public static $url_segment = 'tagtemplates'; 
    
    public function init() {
        parent::init();
    }
     
    public function index() {
        return array();
    }
    
    public function getTagTemplates() {
        $templates = DataObject::get('TagTemplate');
        
        return $templates;
    }
    
    /**
     * Find the current builder page and retrieve the URL
     * 
     * @return string
     */
    public function getBuilderLink() {
        $page = DataObject::get_one('BuilderPage');
        
        if($page)
            return $page->Link();
        else
            return false;
    }
}