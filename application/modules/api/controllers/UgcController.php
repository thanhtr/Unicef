<?php

class Api_UgcController extends Zend_Rest_Controller
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
                'base64',
                array(
                    'suffix' => 'base64',
                    'headers' => array('Content-Type' => 'text/plain')
                )
            )
            ->addContext(
                'jpg',
                array(
                    'suffix' => 'jpg',
                    'headers' => array('Content-Type' => 'image/jpg')
                )
            )
            ->addContext(
                'zip',
                array(
                    'suffix' => 'zip',
                    'headers' => array(
                        'content-disposition' =>
                            'attachment;filename=assets_' .
                            date('jS_M_Y-H:i:s') . '.zip',
                        'Content-Type' => 'application/x-zip-compressed'

                    )
                )
            )
            ->addContext(
                'dojo',
                array(
                    'suffix' => 'dojo',
                    'headers' => array('Content-Type' => 'text/html')
                )
            )
            ->addActionContext(
                'get',
                array('thumbnail', 'json', 'png', 'jpg', 'base64')
            )
            ->addActionContext(
                'index',
                array('zip', 'json')
            )
            ->addActionContext(
                'post',
                array('json', 'dojo')
            )
            ->addActionContext(
                'put',
                array('json', 'dojo')
            )
            ->addActionContext(
                'delete',
                array('json', 'dojo')
            )
            ->initContext();

    }

    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_Asset();
        $params = $this->_getAllParams();
        $params['ugc'] = true;
        $this->view->data = $mapper->fetchAll($params);
    }

    public function getAction()
    {
        $mapper = new Api_Model_Mapper_Asset();
        $asset = null;

        if ($this->_getParam('id')) {
            $asset = $mapper->find($this->_getParam('id'));
        } else if ($this->_getParam('thumbnail')) {
            $asset = $mapper->find($this->_getParam('thumbnail'));
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

        if ($asset) {
            $disp = $this->_getParam('download', false)?'attachment':'inline';

            $this->getResponse()->setHeader(
                'content-disposition',
                $disp . ';filename=' . $asset->getName()
            );
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }

        $this->view->data = $asset;
    }

    public function headAction()
    {
        $mapper = new Api_Model_Mapper_Asset();
        $asset = null;

        if ($this->_getParam('id')) {
            $asset = $mapper->find($this->_getParam('id'));
        } else if ($this->_getParam('thumbnail')) {
            $asset = $mapper->find($this->_getParam('thumbnail'));
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

        if ($asset) {
            $disp = $this->_getParam('download', false)?'attachment':'inline';

            $this->getResponse()->setHeader(
                'content-disposition',
                $disp . ';filename=' . $asset->getName()
            );
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }

        $this->view->data = null;
    }

    public function postAction()
    {
        $form = new Api_Form_Asset();

        $request = $this->getRequest();

        $asset = null;

        if ($form->isValid($request->getPost())) {

            $values = $form->getValues();

            $pathinfo = pathinfo($values['file']);
            $values['name'] = $values['name']?
                $values['name']:
                $pathinfo['filename'];
            $values['format'] = $pathinfo['extension'];

            $asset = Aino\Model\Asset::factory($values);

            $asset->setUgc(true);
            $filePath = $form->getElement('file')->getFileName(null, true);
            if ($asset->grabFile($filePath)) {
                $mapper = new Api_Model_Mapper_Asset();
                $asset = $mapper->save($asset);
            }
        } else {
            $asset = array(
                'error' => true,
                'messages' => $form->getMessages()
            );
        }

        $this->view->data = $asset;
    }

    public function putAction()
    {
    }

    public function deleteAction()
    {
        $mapper = new Api_Model_Mapper_Asset();
        $this->view->data = $mapper->delete($this->_getParam('id'));
    }
}
