<?php

require_once 'Zend/Acl.php';

class Aino_Acl extends Zend_Acl
{
    private static $_instance = null;

    /**
     * Get ACL instance
     *
     * @return Aino_Acl
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Check if the user is allowed to the resource
     *
     * @param type $resource
     * @param type $privilege
     * @return boolean
     */
    public function amIAllowedTo($resource = null, $privilege = null)
    {
        $auth = Zend_Auth::getInstance();

        $role = null;
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $role = $identity->getRole();
        }

        return parent::isAllowed($role, $resource, $privilege);
    }
}
