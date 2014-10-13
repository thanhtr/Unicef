<?php

class Api_Form_Asset extends Zend_Form
{
    protected $_formName = 'asset';

    public function init()
    {
        $config = new Zend_Config_Ini(
            realpath(dirname(__FILE__)) . "/configs/{$this->_formName}.ini"
        );

        $this->setConfig($config);

        $this->getElement('file')->setDestination(
            APPLICATION_PATH . '/../data/tmp/'
        );

        $validators = $this->getElement('file')->getValidators();
        if (isset($validators['Zend_Validate_File_IsImage'])) {
            $validators['Zend_Validate_File_IsImage']->setMessage(
                'Virheellinen kuvaformaatti. Kuvan tulee olla jpg- tai png-formaatissa'
            );
        }

        $groupsElement = $this->getElement('groups');
        if ($groupsElement) {
            $assetGroupMapper = new Api_Model_Mapper_AssetGroup();

            foreach ($assetGroupMapper->fetchAll() as $assetGroup) {
                $groupsElement->addMultiOption(
                    $assetGroup->getId(),
                    $assetGroup->getName()
                );
            }
        }
    }
}