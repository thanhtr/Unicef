<?php

class Default_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->setLayout('public');
    }
    public function indexAction()
    {
    }
}

