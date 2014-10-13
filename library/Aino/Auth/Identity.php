<?php
use Aino\Model;
require_once 'Aino/Email.php';

class Aino_Auth_Identity extends Model
{
    const STATUS_ACTIVE = 'active';

    const STATUS_BANNED = 'banned';

    const STATUS_UNDEFINED = 'undefined';

    protected $toArrayBlacklist = array('url');

    /**
     * @var Aino_Email
     */
    protected $_email;

    /**
     * @var string
     */
    protected $_role;

    /**
     * @var integer
     */
    protected $_roleId;

    /**
     * @var integer
     */
    protected $_id;

    /**
     * @var integer
     */
    protected $_domainId;

    /**
     * @var string
     */
    protected $_status;

    public function __construct($properties = array())
    {
        if (is_array($properties) || ($properties instanceof ArrayAccess)) {
            $this->_setProperties($properties);
        } else {
            throw new Aino_Exception(
                'Properties param must be array.'
            );
        }
    }

    private function _setProperties($properties)
    {
        foreach ($properties as $propertyName => $value) {
            $method = 'set' . ucfirst($propertyName);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Get user email object
     *
     * @return Aino_Email
     */
    public function getEmail()
    {
        return $this->_email;
    }

    public function setEmail($email)
    {
        if ($email instanceof Aino_Email) {
            $this->_email = $email;
        } else {
            $this->_email = new Aino_Email($email);
        }

        return $this;
    }

    public function getDomain()
    {
        return $this->getEmail()->getDomain();
    }

    public function getUsername()
    {
        return $this->getEmail()->getUsername();
    }

    public function getRole()
    {
        return $this->_role;
    }

    public function setRole($role)
    {
        if (is_string($role)) {
           $this->_role = $role;
        } else {
           throw new Aino_Exception('Role must be string');
        }

        return $this;
    }

    public function setId($identityId)
    {
        if (is_numeric($identityId)) {
           $this->_id = $identityId;
        } else {
           throw new Aino_Exception('Id must be numeric integer');
        }

        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setRoleId($roleId)
    {
        if (is_numeric($roleId)) {
           $this->_roleId = $roleId;
        } else {
           throw new Aino_Exception('Id must be numeric integer');
        }

        return $this;
    }

    public function getRoleId()
    {
        return $this->_roleId;
    }

    public function getDomainId()
    {
        return $this->_domainId;
    }

    public function setDomainId($domainId)
    {
        if (is_numeric($domainId)) {
           $this->_domainId = $domainId;
        } else {
           throw new Aino_Exception('Id must be numeric integer');
        }

        return $this;
    }

    public function setStatus($status)
    {
        switch (strtolower($status)) {
            case self::STATUS_ACTIVE:
                $this->_status = self::STATUS_ACTIVE;
                break;
            case self::STATUS_BANNED:
                $this->_status = self::STATUS_BANNED;
                break;
            default:
                $this->_status = self::STATUS_UNDEFINED;
        }

        return $this;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function isActive()
    {
        return $this->getStatus() == self::STATUS_ACTIVE;
    }

    public function isBanned()
    {
        return $this->getStatus() == self::STATUS_BANNED;
    }
}
