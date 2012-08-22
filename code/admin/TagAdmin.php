<?php
/**
 * Description of TemplateAdmin
 *
 * @author morven
 */
class TagAdmin extends ModelAdmin {
    public static $url_segment = 'tags';
    public static $menu_title = 'Tags';
    public static $menu_priority = 5;
    
    protected $resultsTableClassName = 'TableListField';
    
    static $managed_models = array(
        'TagTemplate',
        'TagColour'
    );

    static $model_importers = array(
    );
    
    public function init() {
        parent::init();
        
        Requirements::javascript('commerce/js/TagAdmin.js');
    }
}