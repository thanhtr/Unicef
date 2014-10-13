<?php

class Api_Model_DbTable_ComponentModifierOption extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array(
      'component_modifier_option_id',
      'component_modifier_id'
  );

  /**
   * Tablename
   * @var string
   */
  protected $_name = 'component_modifier_option';

  protected $_referenceMap    = array(
      'component_modifier' => array(
          'columns' => 'component_modifier_id',
          'refTableClass' => 'Api_Model_DbTable_ComponentModifier',
          'refColumns' => 'component_modifier_id'
      )
  );
}
