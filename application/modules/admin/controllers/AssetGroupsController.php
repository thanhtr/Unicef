<?php

class Admin_AssetGroupsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_AssetGroup();
        $this->view->assetGroups = $mapper->fetchAll();
    }

    public function editAction()
    {
        $assetGroupMapper = new Api_Model_Mapper_AssetGroup();
        $assetGroup = $assetGroupMapper->find($this->_getParam('id'));

        $form = new Api_Form_AssetGroup();

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $assetGroup = Aino\Model\Asset\Group::factory($form->getValues());
            if ($assetGroupMapper->save($assetGroup)) {
                $this->_redirect('/admin/asset-groups');
            }
        }


        if ($assetGroup) {
            $form->populate($assetGroup->toArray());
        }

        $this->view->form = $form;
    }
}
