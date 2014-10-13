<?php

class Default_ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('public');
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        $log = $this->getLog();
        if ($log) {
            $log->log(
                $this->view->message . "\n" . $errors->exception,
                $priority
            );
            $log->log(
                'Request Parameters' . var_export(
                    $errors->request->getParams(),
                    true
                ),
                $priority
            );
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->json(
                array('message' => $this->view->message)
            );
        }
        $this->view->request   = $errors->request;
    }

    public function quotaLimitReachedAction()
    {
        $this->getResponse()->setHttpResponseCode(403);
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->json(
                array('message' => 'Your print quota limit has been reached.')
            );
        }
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}

