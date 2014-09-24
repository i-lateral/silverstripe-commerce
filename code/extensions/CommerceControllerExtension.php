<?php
/**
 * Extension for Content Controller that provide methods such as cart link and category list
 * to templates
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package commerce
 */
class CommerceControllerExtension extends Extension {

    /**
     * @return void
     */
    public function onBeforeInit() {
        if(class_exists('Subsite') && Subsite::currentSubsite()) {
            // Set the location
            i18n::set_locale(Subsite::currentSubsite()->Language);
        }
    }
}
