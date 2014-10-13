<?php

class Admin_MapperController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_Asset;
        $this->view->group = $this->_getParam('group');
    }
}
