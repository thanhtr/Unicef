<?php

class Api_TemplatesController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->_helper->viewRenderer('index');
        $this->_helper->getHelper('contextSwitch')
            ->setAutoJsonSerialization(false)
            ->addContext(
                'png',
                array(
                    'suffix' => 'png',
                    'headers' => array('Content-Type' => 'image/png')
                )
            )
            ->addContext(
                'thumbnail',
                array(
                    'suffix' => 'thumbnail',
                    'headers' => array('Content-Type' => 'image/png')
                )
            )
            ->addContext(
                'pdf',
                array(
                    'suffix' => 'pdf',
                    'headers' => array('Content-Type' => 'application/pdf')
                )
            )
            ->addContext(
                'svg',
                array(
                    'suffix' => 'svg',
                    'headers' => array('Content-Type' => 'image/svg+xml')
                )
            )
            ->addContext(
                'jpg',
                array(
                    'suffix' => 'jpg',
                    'headers' => array('Content-Type' => 'image/jpg')
                )
            )
            ->addActionContext(
                'get',
                array('thumbnail', 'svg','json','png', 'pdf', 'jpg')
            )
            ->initContext();

    }

    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_Template();
        $this->view->data = $mapper->fetchAll(
            $this->_getParam('types', array())
        );
    }

    public function getAction()
    {
        $mapper = new Api_Model_Mapper_Template();
        $template = null;

        $isPrint = $this->_getParam('print', false);

        if ($this->_getParam('id')) {
            $template = $mapper->find($this->_getParam('id'));
            $ext = $this->_helper
                ->getHelper('contextSwitch')->getCurrentContext();
            if ($this->_getParam('download', false) && $template) {
                $filename = preg_replace(
                    '/[\/\\ *&%^"]/',
                    '_',
                    $template->getName()
                );
                $this->getResponse()->setHeader(
                    'content-disposition',
                    'attachment;filename=' . $filename . '.' . $ext
                );
            }
        } else if ($this->_getParam('thumbnail')) {
            $isPrint = false;
            $template = $mapper->find($this->_getParam('thumbnail'));
            $this->_helper->getHelper('contextSwitch')
                ->initContext('thumbnail');
            $this->view->size = pathinfo(
                $this->_getParam(
                    'size',
                    Aino\Model\Image::THUMBNAIL_SMALL
                ),
                PATHINFO_FILENAME
            );
        }

        if ($template) {
            $template->setUseCache($this->_getParam('useCache', true));
            $parameters = $this->_getParam('parameters', array());
            if (is_array($parameters)) {
                $template->populateParameters($parameters);
            } else {
                $parameters = Zend_Json::decode($parameters);
                $template->populateParameters($parameters);
            }

            if ($isPrint) {
                $template->removeDraftElements();
                $hasQuota = $mapper->updateQuota(
                    $template,
                    $parameters,
                    $ext
                );
                if(false == $hasQuota) {
                    $this->_redirect('default/error/quota-limit-reached');
                }
            }
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }

        $this->view->data = $template;
    }

    public function headAction()
    {
        $mapper = new Api_Model_Mapper_Template();
        $template = null;

        $isPrint = $this->_getParam('print', false);

        if ($this->_getParam('id')) {
            $template = $mapper->find($this->_getParam('id'));
            $ext = $this->_helper
                ->getHelper('contextSwitch')->getCurrentContext();
            if ($this->_getParam('download', false) && $template) {
                $filename = preg_replace(
                    '/[\/\\ *&%^"]/',
                    '_',
                    $template->getName()
                );
                $this->getResponse()->setHeader(
                    'content-disposition',
                    'attachment;filename=' . $filename . '.' . $ext
                );
            }
        } else if ($this->_getParam('thumbnail')) {
            $isPrint = false;
            $template = $mapper->find($this->_getParam('thumbnail'));
            $this->_helper->getHelper('contextSwitch')
                ->initContext('thumbnail');
            $this->view->size = pathinfo(
                $this->_getParam(
                    'size',
                    Aino\Model\Image::THUMBNAIL_SMALL
                ),
                PATHINFO_FILENAME
            );
        }

        if ($template) {
            $template->setUseCache($this->_getParam('useCache', true));
            $parameters = $this->_getParam('parameters', array());
            if (is_array($parameters)) {
                $template->populateParameters($parameters);
            } else {
                $parameters = Zend_Json::decode($parameters);
                $template->populateParameters($parameters);
            }

            if ($isPrint) {
                $template->removeDraftElements();
                $hasQuota = $mapper->updateQuota(
                    $template,
                    $parameters,
                    $ext
                );
                if(false == $hasQuota) {
                    $this->_redirect('default/error/quota-limit-reached');
                }
            }
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }

        $this->view->data = null;
    }

    public function postAction()
    {
        ;
    }

    public function putAction()
    {
        ;
    }

    public function deleteAction()
    {
        $mapper = new Api_Model_Mapper_Template();
        $this->view->data = $mapper->delete($this->_getParam('id'));
    }
}
