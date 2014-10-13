<?php

class Aino_Application_Resource_Auth
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Strategy pattern: initialize resource
     *
     * @return mixed
     */
    public function init()
    {
        $options = $this->getOptions();

        $front = Zend_Controller_Front::getInstance();

        if ($options['authLimit']) {
            $plugin = new Aino_Controller_Plugin_AuthBroker();
            $plugin->setOptions($options);
            $front->registerPlugin($plugin);
        } else if (isset($options['defaultUserId'])) {
            $plugin = new Aino_Controller_Plugin_DefaultUser(
                $options['defaultUserId']
            );
            $front->registerPlugin($plugin);
        }
    }
}
