<?php

class Customize_ImageController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->viewRenderer('index');
    }

    public function intelligentAction()
    {
        $this->view->type = 'int';
        $this->view->downloadFormats = array('png');
    }

    public function globalAction()
    {
        $this->view->type = 'global';
        $this->view->downloadFormats = array('png','jpg');
    }

    public function unpublishedAction()
    {
        $this->view->type = 'unpub';
        $this->view->downloadFormats = array('png','jpg','pdf','svg');
    }
}
