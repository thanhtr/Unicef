<?php

class Api_Model_DbTable_ComponentHasAssetGroup extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array(
      'component_id',
      'asset_group_id'
  );


  /**
   * Tablename
   * @var string
   */
  protected $_name = 'component_has_asset_group';

  protected $_referenceMap    = array(
      'component' => array(
          'columns' => 'component_id',
          'refTableClass' => 'Api_Model_DbTable_Component',
          'refColumns' => 'component_id'
      ),
      'asset_group' => array(
          'columns' => 'asset_group_id',
          'refTableClass' => 'Api_Model_DbTable_AssetGroup',
          'refColumns' => 'asset_group_id'
      )
  );
}
