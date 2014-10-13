<?php

class Aino_Application_Resource_Acl
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Acl
     */
    protected $_acl = null;

    /**
     * Get Acl object
     *
     * @return Zend_Acl
     */
    private function _getAcl()
    {
        if(is_null($this->_acl)) {
            $this->_acl = Aino_Acl::getInstance();
        }

        return $this->_acl;
    }

    /**
     * Initialize roles
     *
     * @param array $roles
     * @return \Application_Resource_Acl
     */
    private function _addRoles($roles)
    {
        $acl = $this->_getAcl();

        foreach($roles as $role) {
            $acl->addRole($role);
        }

        return $this;
    }

    /**
     * Initialize resources
     *
     * @param array $resources
     * @return \Application_Resource_Acl
     */
    private function _addRresources($resources)
    {
        $acl = $this->_getAcl();

        foreach($resources as $resource) {
            $acl->add(new Zend_Acl_Resource($resource));
        }

        return $this;
    }

    /**
     * Setup ACL
     *
     * @param array $allows
     * @return \Aino_Resource_Acl
     */
    private function _setupAcl($allows)
    {
        $acl = $this->_getAcl();

        foreach ($allows as $role => $resources) {
            $acl->allow($role, $resources);
        }

        return $this;
    }

    /**
     * Initialize
     *
     * @return Zend_Acl
     */
    public function init()
    {
        $options = $this->getOptions();
        $acl = $this->_getAcl();
        $acl->removeAll();
        $acl->removeRoleAll();

        $this->_addRoles($options['roles'])
             ->_addRresources($options['resources'])
             ->_setupAcl($options['allow']);

        return $this->_getAcl();
    }
}