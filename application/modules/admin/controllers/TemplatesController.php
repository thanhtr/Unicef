<?php
use Aino\Model\Template;
class Admin_TemplatesController extends \Zend_Controller_Action
{
    public function indexAction()
    {
        $mapper = new Api_Model_Mapper_Template;
        $this->view->templates = $mapper->fetchAll();
    }

    public function editAction()
    {
        $mapper = new Api_Model_Mapper_Template;
        $template = $mapper->find($this->_getParam('id'));
        $form = new Api_Form_Template();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            if (!$template) {
                $template = new Template();
            }
            $values = $form->getValues();

            $template
                ->setName($values['name'])
                ->setDescription($values['description']);

            $filePath = $form->getElement('file')->getFileName(null, true);
            if (is_file($filePath)) {
                $template->setSvg(file_get_contents($filePath));
                unlink($filePath);
            }

            if (isset($values['types'])) {
                $template->getTypes()->clearAll();
                foreach ($values['types'] as $typeId) {
                    $type = Template\Type::factory(
                        array('id' => $typeId)
                    );

                    $template->getTypes()->addItem($type);
                }
            }

            $template = $mapper->save($template);
            if ($template) {
                $this->_redirect('/admin/templates/');
            }
        } else if ($template) {
            $form->populate($template->toArray());
        }

        $typesElement = $form->getElement('types');

        if ($template) {
            $templateTypes = $template->getTypes();
            $selectValues = array();
            foreach ($templateTypes as $templateType) {
                $selectValues[] = $templateType->getId();
            }

            $typesElement->setValue($selectValues);
        }

        $this->view->template = $template;
        $this->view->form = $form;
    }
}
