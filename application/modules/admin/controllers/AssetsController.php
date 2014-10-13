<?php

class Admin_AssetsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_Asset();
        $this->view->assets = $mapper->fetchAll();
    }

    public function editAction()
    {
        $mapper = new Api_Model_Mapper_Asset();
        $asset = $mapper->find($this->_getParam('id'));

        $form = new Api_Form_Asset();

        $request = $this->getRequest();
        if($request->isPost() && $form->isValid($request->getPost())) {
            $values = $form->getValues();

            $pathinfo = pathinfo($values['file']);
            $values['name'] = $values['name']?$values['name']:$pathinfo['filename'];
            $values['format'] = $pathinfo['extension'];

            if (!$asset) {
                $asset = Aino\Model\Asset::factory($values);
            } else {
                $asset->updateFromArray($values);
            }

            $asset->getGroups()->clearAll();
            foreach ($values['groups'] as $groupId) {
                $assetGroup = Aino\Model\Asset\Group::factory(
                    array('id' => $groupId)
                );

                $asset->getGroups()->addItem($assetGroup);
            }

            $filePath = $form->getElement('file')->getFileName(null, true);
            if (is_file($filePath)) {
                $asset->grabFile($filePath);
            }

            $asset = $mapper->save($asset);

            if($asset) {
                $this->_redirect('/admin/assets/');
            }
        }
        
        if ($asset) {
            $form->populate($asset->toArray());
            $this->view->asset = $asset;

            $assetGroups = $asset->getGroups();
            $selectValues = array();
            foreach ($assetGroups as $assetGroup) {
                $selectValues[] = $assetGroup->getId();
            }

            $form->getElement('groups')->setValue($selectValues);
        }

        $this->view->form = $form;
    }
}
