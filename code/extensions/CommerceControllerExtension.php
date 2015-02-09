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
        // Check if we are runing a dev build, if so check if DB needs
        // upgrading
        $controller = $this->owner->request->param("Controller");
        $action = $this->owner->request->param("Action");
        
        // Only check if the DB needs upgrading on a dev build
        if($controller == "DevelopmentAdmin" && $action == "build" && CommerceUpgrader::check()) {
            $upgraded = CommerceUpgrader::upgrade();
            
            if(!$upgraded) user_error("Could not upgrade the Commerce module, please check the documentation on upgrading.");
        }
        
        if(class_exists('Subsite') && Subsite::currentSubsite()) {
            // Set the location
            i18n::set_locale(Subsite::currentSubsite()->Language);
        }
    }
}
