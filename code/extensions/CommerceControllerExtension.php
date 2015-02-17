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
            user_error("The commerce module requires you manually upgrade your database. Please run dev/tasks/CommerceUpgrade1To2Task or check the documentation.", E_USER_ERROR);
        }
    }
}
