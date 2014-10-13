<?php

class Api_Model_DbTable_ComponentModifier extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array(
      'component_modifier_id',
      'component_id'
  );

  /**
   * Tablename
   * @var string
   */
  protected $_name = 'component_modifier';

  protected $_referenceMap    = array(
      'component' => array(
          'columns' => 'component_id',
          'refTableClass' => 'Api_Model_DbTable_Component',
          'refColumns' => 'component_id'
      )
  );
}
