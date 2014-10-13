<?php

/**
 *
 * @category BridgeApp
 * @package App
 * @subpackage Auth
 * @author fieinsaar
 */
class Auth_Model_Token
    implements Zend_Auth_Adapter_Interface
{
    const SALT_LENGTH = 19;

    const HASH_TYPE = 'sha256';

    const HYPHENATION_LENGHT = 8;

    /**
     * @var Aino_Auth_Identity
     */
    private $_identity = null;

    /**
     * @var string
     */
    private $_token = null;

    /**
     * @var string
     */
    private $_clientIp = null;

    public function __construct($identity)
    {
        $this->setIdentity($identity);
    }

    /**
     * Hyphenate string.
     *
     * Used for hyphenate token string
     * @param string $string
     * @return string
     */
    private static function _hyphenate($string)
    {
        $splittedArray = str_split($string, self::HYPHENATION_LENGHT);
        return implode('-', $splittedArray);
    }

    /**
     * Remove hyphens (-) from string
     *
     * Used with the token string
     * @param string $string
     * @return string
     */
    private static function _stripHyphens($string)
    {
        return str_replace('-', '', $string);
    }

    public function generateToken($salt = null)
    {
        $token = '';
        $clientIp = $this->getClientIp();
        $identity = $this->getIdentity();

        if ($salt == null) {
            $salt = md5(uniqid('', true));
        } else {
            $salt = self::_stripHyphens($salt);
        }

        $salt = substr($salt, 0, self::SALT_LENGTH);
        $token = Zend_Crypt::hash(
            self::HASH_TYPE,
            $salt . $clientIp . (string) $identity->getEmail()
        );

        //Lets hyphonate the string only for distraction
        $token = self::_hyphenate($salt . $token);
        $this->setToken($token);

        return $token;
    }

    public function setIdentity($identity)
    {
        if ($identity instanceof Aino_Auth_Identity) {
            $this->_identity = $identity;
        } else {
            require_once 'Aino/Exception.php';
            throw new Aino_Exception(
                "Identity object must be instance of 'Aino_Auth_Identity'."
            );
        }

        return $this;
    }

    /**
     * Get Identity
     *
     * @return Aino_Auth_Identity
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    public function getClientIp()
    {
        if (null === $this->_clientIp) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            if ($request) {
                $clientIp = $request->getClientIp(true);
            } else {
                $clientIp = false;
            }

            $this->_clientIp = $clientIp?$clientIp:'127.0.0.1';
        }

        return $this->_clientIp;
    }

    public function setToken($token)
    {
        if (is_string($token)) {
            $this->_token = $token;
        } else {
            require_once 'Aino/Exception.php';
            throw new Aino_Exception(
                "Token must be string."
            );
        }

        return $this;
    }

    public function getToken()
    {
        if (null === $this->_token) {
            $this->setToken($this->generateToken());
        }

        return $this->_token;
    }

    /**
     * Get uri for authentication with the generated token
     *
     * @return string
     */
    public function getAuthenticationUri()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $scheme = $request->getScheme();
        $host = $request->getHttpHost();
        $baseUrl = $request->getBaseUrl();
        $token = $this->getToken();

        return "{$scheme}://{$host}{$baseUrl}/auth/token?q={$token}";
    }


    public function authenticate()
    {
        $token = $this->getToken();
        $identity = $this->getIdentity();

        switch (true) {
            case ($token === $this->generateToken($token)):
                $result = new Zend_Auth_Result(
                    Zend_Auth_Result::SUCCESS,
                    $identity
                );
                break;
            default:
                // Token does not match
                $result = new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                    null
                );
                break;
        }

        return $result;
    }
}