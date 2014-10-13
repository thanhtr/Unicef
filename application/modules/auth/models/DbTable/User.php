<?php

class Auth_Model_DbTable_User extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = 'user_id';

  /**
   * Tablename
   * @var string
   */
  protected $_name = 'user';

}
