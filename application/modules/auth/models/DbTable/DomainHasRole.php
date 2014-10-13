<?php

class Auth_Model_DbTable_DomainHasRole extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array('user_domain_id', 'user_role_id');

  /**
   * Tablename
   * @var string
   */
  protected $_name = 'domain_has_role';
}
