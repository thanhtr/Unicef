<?php

class Aino_Controller_Plugin_AuthBroker
    extends Zend_Controller_Plugin_Abstract
{
    protected $_options = array();

    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->_options = $options;
        } else {
            $type = gettype($options);
            throw new Zend_Controller_Exception(
                str_replace(
                    '%type%',
                    $type,
                    "Invalid parameter type '%type%' supplied."
                )
            );
            return null;
        }

        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
    }

    public function getOption($key)
    {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return null;
    }

    public function useAuthLimit()
    {
        return (bool) $this->getOption('authLimit');
    }

    public function renewSessionLifetime()
    {
        $namespace = new Zend_Session_Namespace('Zend_Auth');
        $namespace->setExpirationSeconds($this->getOption('sessionLifetime'));
    }

    public function useIpAuth()
    {
        $ipAuth = $this->getOption('ipAuth');
        return (bool) isset($ipAuth['enabled']) && $ipAuth['enabled'];
    }

    public function isAllowedIp()
    {
        $ipAuth = $this->getOption('ipAuth');

        if (isset($ipAuth['ips'])) {
            return in_array(
                $this->getRequest()->getClientIp(),
                $ipAuth['ips']
            );
        }

        return false;
    }

    private function _setRequestParams($request)
    {
        foreach ($this->getOption('requestParams') as $param => $value) {
            $method = 'set' . ucfirst($param);
            if (method_exists($request, $method)) {
                $request->$method($value);
            }
        }

        return $request;
    }

    private static function _getMCAPath($request)
    {
        $pieces = array();
        $pieces[] = $request->getModuleName();
        $pieces[] = $request->getControllerName();
        $pieces[] = $request->getActionName();

        $path = implode('/', $pieces);
        return strtolower('/' . $path);
    }

    public function isRestrictedMCAPath($request)
    {
        $path = self::_getMCAPath($request);
        foreach ($this->getOption('restrictedMCAPaths') as $restrictedPath) {
            $restrictedPath = str_replace(
                array('/', '*'),
                array('\/', '.*'),
                $restrictedPath
            );
            $pattern = "/{$restrictedPath}/";

            if (preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * RouteShutdown
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();

        if ($this->useAuthLimit() &&
            (false == $auth->hasIdentity()) &&
            $this->isRestrictedMCAPath($request)) {

            if ($this->useIpAuth() && $this->isAllowedIp()) {
                return;
            }

            $this->_setRequestParams(
                $request,
                $this->getOption('requestParams')
            );
        } else if ($this->useAuthLimit()) {
            $this->renewSessionLifetime();
        }
    }
}
