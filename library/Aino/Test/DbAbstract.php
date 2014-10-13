<?php

//include_once 'DbTruncate.php';

abstract class Aino_Test_DbAbstract
    extends Zend_Test_PHPUnit_DatabaseTestCase
{
    private $_bootstrap;

    private $_connectionMock;

    protected function getConnection()
    {
        if($this->_connectionMock == null) {
            $bootstrap = $this->bootstrap->getBootstrap();
            $connection = $bootstrap->getResource('db');

            $this->_connectionMock = $this->createZendDbConnection(
                $connection,
                'digtest'
            );
        }

        return $this->_connectionMock;
    }

    protected function getDataSet()
    {
        return $this->createFlatXmlDataSet(
            TEST_DATA_DIR . "/full.xml"
        );
    }

    protected function setUp()
    {
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );

        $this->bootstrap->bootstrap();
        Zend_Controller_Front::getInstance()->setParam(
        	'bootstrap',
            $this->bootstrap
        );
        parent::setUp();
    }

    protected function getSetUpOperation()
    {
        return new PHPUnit_Extensions_Database_Operation_Composite(array(
            new Zend_Test_PHPUnit_Db_Operation_DeleteAll(),
            new Zend_Test_PHPUnit_Db_Operation_Insert()
        ));
    }
}