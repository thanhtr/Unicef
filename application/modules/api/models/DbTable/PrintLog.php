<?php

class Api_Model_DbTable_PrintLog extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array('print_log_id');


  /**
   * Tablename
   * @var string
   */
  protected $_name = 'print_log';

  protected $_referenceMap    = array(
      'user' => array(
          'columns' => 'user_id',
          'refTableClass' => 'Auth_Model_DbTable_User',
          'refColumns' => 'user_id'
      ),
      'template' => array(
          'columns' => 'template_id',
          'refTableClass' => 'Api_Model_DbTable_Template',
          'refColumns' => 'template_id'
      )
  );
}
