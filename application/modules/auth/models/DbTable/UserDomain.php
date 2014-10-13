<?php

class Auth_Model_DbTable_UserDomain extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = 'user_domain_id';


  /**
   * Tablename
   * @var string
   */
  protected $_name = 'user_domain';
}
