<?php
 /**
  * Add interface to manage localisation settings through the CMS
  *
  * @package Commerce
  */
class LocaliseAdmin extends ModelAdmin {
    private static $url_segment = 'localisation';
    private static $menu_title = 'Localisation';
    private static $menu_priority = -1;

    private static $managed_models = array(
        'CommerceCurrency',
        'ProductWeight',
    );

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);

        return $form;
    }
}
