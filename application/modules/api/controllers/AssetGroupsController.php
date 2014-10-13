<?php

class Api_AssetGroupsController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->_helper->viewRenderer('index');
        $this->_helper->getHelper('contextSwitch')
            ->setAutoJsonSerialization(false)
            ->initContext();

    }

    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_AssetGroup();
        $this->view->data = $mapper->fetchAll();
    }

    public function getAction()
    {
        $mapper = new Api_Model_Mapper_AssetGroup();
        $assetGroupId = null;
        $assetGroup = null;

        if ($this->_getParam('id')) {
            $assetGroup = $mapper->find($this->_getParam('id'));
        }

        if (is_null($assetGroup)) {
            $this->getResponse()->setHttpResponseCode(404);
        }

        $this->view->data = $assetGroup;
    }

    public function postAction()
    {
        $this->view->data = null;
    }

    public function putAction()
    {
        $this->view->data = null;
    }

    public function deleteAction()
    {
        $mapper = new Api_Model_Mapper_AssetGroup();
        $this->view->data = $mapper->delete($this->_getParam('id'));
    }
}
