<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    function _initRestRoute() {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        $restRoute = new Zend_Rest_Route($front, array(), array('api'));
        $router->addRoute('rest', $restRoute);
    }

    /**
     * Configure navigation
     */
    protected function _initNavigationPages()
    {
        $config = new Zend_Config_Ini(
            APPLICATION_PATH . "/configs/navigation.ini"
        );

        $this->bootstrap('navigation');
        $navigation = $this->getResource('navigation');
        $navigation->addPages($config);

        $this->bootstrap('acl');
        Zend_View_Helper_Navigation_Menu::setDefaultAcl(
            $this->getResource('acl')
        );

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            Zend_View_Helper_Navigation_Menu::setDefaultRole(
                $identity->getRole()
            );
        } else {
            Zend_View_Helper_Navigation_Menu::setDefaultRole('admin');
        }
    }
}

