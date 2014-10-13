<?php

class Api_Model_DbTable_AssetGroupHasAsset extends Zend_Db_Table_Abstract
{
  /**
   * @var string
   */
  protected $_primary = array(
      'asset_id',
      'asset_group_id'
  );


  /**
   * Tablename
   * @var string
   */
  protected $_name = 'asset_group_has_asset';

  protected $_referenceMap    = array(
      'asset_group' => array(
          'columns' => 'asset_group_id',
          'refTableClass' => 'Api_Model_DbTable_AssetGroup',
          'refColumns' => 'asset_group_id'
      ),
      'asset' => array(
          'columns' => 'asset_id',
          'refTableClass' => 'Api_Model_DbTable_Asset',
          'refColumns' => 'asset_id'
      )
  );
}
