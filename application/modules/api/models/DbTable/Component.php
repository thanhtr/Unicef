<?php

class Api_Model_DbTable_Component extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array(
      'component_id'
  );

  /**
   * Tablename
   * @var string
   */
  protected $_name = 'component';

  protected $_referenceMap    = array(
      'asset' => array(
          'columns' => 'asset_id',
          'refTableClass' => 'Api_Model_DbTable_Asset',
          'refColumns' => 'asset_id'
      )
  );
}
