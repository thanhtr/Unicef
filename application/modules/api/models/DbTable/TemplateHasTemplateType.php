<?php

class Api_Model_DbTable_TemplateHasTemplateType extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array(
      'template_id',
      'template_type_id'
  );


  /**
   * Tablename
   * @var string
   */
  protected $_name = 'template_has_template_type';

  protected $_referenceMap    = array(
      'template_type' => array(
          'columns' => 'template_type_id',
          'refTableClass' => 'Api_Model_DbTable_TemplateType',
          'refColumns' => 'template_type_id'
      ),
      'template' => array(
          'columns' => 'template_id',
          'refTableClass' => 'Api_Model_DbTable_Template',
          'refColumns' => 'template_id'
      )
  );
}
