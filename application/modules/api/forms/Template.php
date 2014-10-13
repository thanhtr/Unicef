<?php

class Api_Form_Template extends Zend_Form
{
    protected $_formName = 'template';

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
                'Invalid filetype. SVG is the only supported filetype',
                'fileIsImageFalseType'
            );
        }

        $typesElement = $this->getElement('types');
        if ($typesElement) {
            $typesMapper = new Api_Model_Mapper_TemplateType();
            $templateTypes = $typesMapper->fetchAll();

            foreach ($templateTypes as $templateType) {
                $typesElement->addMultiOption(
                    $templateType->getId(),
                    $templateType->getName()
                );
            }
        }
    }
}