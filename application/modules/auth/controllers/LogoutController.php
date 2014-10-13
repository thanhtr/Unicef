<?php

class Auth_LogoutController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $auht = Zend_Auth::getInstance();
        $auht->clearIdentity();
        $this->_redirect('/');
    }
}