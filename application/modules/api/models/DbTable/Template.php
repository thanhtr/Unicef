<?php

class Api_Model_DbTable_Template extends Zend_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_primary = 'template_id';


    /**
    * Tablename
    * @var string
    */
    protected $_name = 'template';

    protected $_referenceMap    = array(
        'user' => array(
            'columns' => 'user_id',
            'refTableClass' => 'Auth_Model_DbTable_User',
            'refColumns' => 'user_id'
        )
    );
}
