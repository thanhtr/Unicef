<?php

class Auth_Model_DbTable_UserRole extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = 'user_role_id';

  /**
   * Tablename
   * @var string
   */
  protected $_name = 'user_role';
}
