<?php

class Aino_Controller_Plugin_DefaultUser
    extends Zend_Controller_Plugin_Abstract
{
    private $_userId = null;

    public function __construct($userId) {
        $this->_userId = $userId;
    }

    /**
     * RouteShutdown
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $mapper = new Auth_Model_Mapper_Identity();
        $identity = $mapper->findById($this->_userId);


        if ($identity) {
            $auth = Zend_Auth::getInstance();
            $auth->getStorage()->write($identity);
        }
    }
}
