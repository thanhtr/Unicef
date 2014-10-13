<?php

class Api_ComponentsController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->_helper->viewRenderer('index');
    }

    public function indexAction()
    {
    }

    public function getAction()
    {
        $mapper = new Api_Model_Mapper_Component();
        $component = $mapper->find($this->_getParam('id'));
        $this->view->data = $component;
    }

    public function postAction()
    {
        $mapper = new Api_Model_Mapper_Component();
        $this->view->data =
            $mapper->saveFromJson($this->getRequest()->getRawBody());

    }

    public function putAction()
    {
        $mapper = new Api_Model_Mapper_Component();
        $this->view->data =
            $mapper->saveFromJson(
                $this->getRequest()->getRawBody(),
                $this->_getParam('id')
            );
    }

    public function deleteAction()
    {
    }
}
