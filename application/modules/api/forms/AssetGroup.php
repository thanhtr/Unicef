<?php

class Api_Form_AssetGroup extends Zend_Form
{
    protected $_formName = 'assetGroup';

    public function init()
    {
        $config = new Zend_Config_Ini(
            realpath(dirname(__FILE__)) . "/configs/{$this->_formName}.ini"
        );

        $this->setConfig($config);
    }
}