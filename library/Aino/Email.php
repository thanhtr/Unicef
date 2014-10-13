<?php

require_once 'Zend/Validate/EmailAddress.php';

class Aino_Email
{

    private $_username;

    private $_domain;

    public function __construct($emailAddress)
    {
        $validator = new Zend_Validate_EmailAddress();
        if ($validator->isValid($emailAddress)) {
            list($username, $domain) = explode('@', $emailAddress);
            $this
                ->_setUsername($username)
                ->_setDomain($domain);
        } else {
            require_once 'Todui/Exception.php';
            throw new Aino_Exception(
                "Emailaddress '{$emailAddress}' is not valid."
            );
        }
    }

    public function __toString()
    {
        return $this->getUsername() . "@" . $this->getDomain();
    }

    private function _setUsername($username)
    {
        $this->_username = strtolower($username);
        return $this;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    private function _setDomain($domain)
    {
        $this->_domain = strtolower($domain);
        return $this;
    }

    public function getDomain()
    {
        return $this->_domain;
    }
}
