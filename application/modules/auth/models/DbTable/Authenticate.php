<?php

class Auth_Model_DbTable_Authenticate extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = 'token';


  /**
   * Tablename
   * @var string
   */
  protected $_name = 'authenticate';
}
