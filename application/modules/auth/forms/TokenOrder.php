<?php

class Auth_Form_TokenOrder extends Zend_Form
{
    private $_formName = 'tokenOrder';

    public function init()
    {
        $config = new Zend_Config_Ini(
            realpath(dirname(__FILE__)) . "/configs/{$this->_formName}.ini"
        );

        $this->setConfig($config);
    }
}
