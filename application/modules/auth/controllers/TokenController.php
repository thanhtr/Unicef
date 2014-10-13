<?php

class Auth_TokenController extends Zend_Controller_Action
{
    const TOKEN_QUERY_KEY = 'q';

    public function init()
    {
        $this->_helper->layout->setLayout('auth');
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $uiTextModel = new Auth_Model_UiText();

        $form = new Auth_Form_TokenOrder();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $email = $request->getParam('email');
            $clientIp = $request->getClientIp(true);

            //For phpunit tests
            $clientIp = $clientIp?$clientIp:'127.0.0.1';

            $identityMapper = new Auth_Model_Mapper_Identity();

            $identity = $identityMapper->find($email);

            if ($identity && $identity->isActive()) {
                $token = new Auth_Model_Token($identity);
                $tokenModelMapper = new Auth_Model_Mapper_Token();

                $token->generateToken();
                $tokenModelMapper->save($token);

                $uiTextModel->sendTokenEmail(
                    $email,
                    $token->getAuthenticationUri()
                );

                $this->view->message = $uiTextModel->getMessage(
                    Auth_Model_UiText::TOKEN_SENT
                );
                $this->view->messageClass = Auth_Model_UiText::TOKEN_SENT;

                $form = null;
            } else {
                $this->getResponse()->setHttpResponseCode(401);
                $this->view->message =
                    $uiTextModel->getMessage(
                        Auth_Model_UiText::ACCESS_DENIED
                    );
                $this->view->messageClass = Auth_Model_UiText::ACCESS_DENIED;
            }
        }

        $this->view->form = $form;
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $tokenMapper = new Auth_Model_Mapper_Token();
        $token = $tokenMapper->find(
            $request->getParam(self::TOKEN_QUERY_KEY, false)
        );

        if ($token &&
            Zend_Auth::getInstance()->authenticate($token)->isValid()
        ) {
            $this->_redirect('/');
        } else {
            $this->getResponse()->setHttpResponseCode(401);
            $uiTextModel = new Auth_Model_UiText();
            $this->view->messageClass = Auth_Model_UiText::ACCESS_DENIED;
            $this->view->message =
                $uiTextModel->getMessage(
                    Auth_Model_UiText::TOKEN_NOT_VALID
                );
        }

        $this->view->form = new Auth_Form_TokenOrder();
        $this->render('login');
    }
}