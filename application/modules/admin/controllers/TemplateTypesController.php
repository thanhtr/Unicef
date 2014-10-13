<?php

class Admin_TemplateTypesController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_TemplateType();
        $this->view->templateTypes = $mapper->fetchAll();
    }

    public function editAction()
    {
        $mapper = new Api_Model_Mapper_TemplateType();
        $type = $mapper->find($this->_getParam('id'));

        $form = new Api_Form_TemplateType();

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $type = Aino\Model\Template\Type::factory($form->getValues());
            if ($mapper->save($type)) {
                $this->_redirect('/admin/template-types');
            }
        }


        if ($type) {
            $form->populate($type->toArray());
        }

        $this->view->form = $form;
    }
}
