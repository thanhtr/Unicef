<?php

/**
 * SVG Template mapper
 */
class Api_Model_Mapper_Asset extends Api_Model_Mapper_Abstract
{
    protected function buildAsset($assetRow)
    {
        $asset = Aino\Model\Asset::factory($assetRow);

        $asset->setUser($this->findIdentity($assetRow['user_id']));

        $groupRowset = $assetRow->findManyToManyRowset(
            'Api_Model_DbTable_AssetGroup',
            'Api_Model_DbTable_AssetGroupHasAsset'
        );

        foreach ($groupRowset as $groupRow) {
            $asset->addGroup(
                Aino\Model\Asset\Group::factory($groupRow)
            );
        }

        $componentMapper = new Api_Model_Mapper_Component();
        $components = $componentMapper->findAllForAsset($asset);

        $asset->setComponents($components);

        return $asset;
    }

    /**
     * Find asset by id.
     *
     * @param integer $assetId
     * @return \Aino_Model_Asset Null if not found
     */
    public function find($assetId)
    {
        $assetTable = new Api_Model_DbTable_Asset();

        $assetRowset = $assetTable->find($assetId);

        if (count($assetRowset)) {
            $assetRow = $assetRowset[0];
            return $this->buildAsset($assetRow);
        }

        return null;
    }

    /**
     * Find asset by name
     *
     * @param string $assetName
     * @return \Aino_Model_Asset Null if not found
     */
    public function findByName($assetName)
    {
        $assetTable = new Api_Model_DbTable_Asset();

        $select = $assetTable->select();
        $select->where('name=?', $assetName);

        $assetRow = $assetTable->fetchRow($select);

        if ($assetRow) {
            return $this->buildAsset($assetRow);
        }

        return null;
    }

    /**
     * Fetch all templates.
     *
     * @param array|string $typesArray Contains types for filtering resultset
     * @return \Aino_Model_Set
     */
    public function fetchAll($options = array())
    {
        $assetTable = new Api_Model_DbTable_Asset();

        $select = $assetTable->select();
        $select->order('name');

        $groupsArray = array();
        if (isset($options['groups'])) {
            $groupsArray = $options['groups'];

            if (!is_array($groupsArray)) {
                $groupsArray = explode(',', $groupsArray);
            }
        }

        $assetGroupTable = new Api_Model_DbTable_AssetGroupHasAsset();
        foreach ($groupsArray as $group) {
            if ($group instanceof Aino\Model\Asset\Group) {
                $group = $group->getName();
            }

            $groupSelect = $assetGroupTable->select()->setIntegrityCheck(false);
            $groupSelect
                ->from($assetGroupTable, array('asset_id'))
                ->joinLeft(
                    'asset_group',
                    '`asset_group`.`asset_group_id`=`asset_group_has_asset`.`asset_group_id`',
                    array()
                )
                ->where('asset_group.name=?', $group);
            $select->where("asset_id IN ({$groupSelect})");
        }

        if (isset($options['ugc']) && $options['ugc'] === true) {
            $auth = Zend_Auth::getInstance();

            if ($auth->hasIdentity()) {
                $select->orWhere(
                    'ugc=\'1\' AND user_id=?',
                    $auth->getIdentity()->getId()
                );
            }
        }

        $assetRowset = $assetTable->fetchAll($select);

        $assets = new Aino\Model\Set();
        foreach ($assetRowset as $assetRow) {
            $asset = $this->buildAsset($assetRow);

            $assets->addItem($asset);
        }

        return $assets;
    }

    protected function saveAssetGroupLinks($assetId, $assetGroups, $assetRow)
    {
        /*@TODO: If assetRow not specified try to find it*/

        $table = new Api_Model_DbTable_AssetGroupHasAsset();

        $where = $table->getAdapter()->quoteInto(
            'asset_id=?',
            $assetId
        );

        $table->delete($where);

        foreach ($assetGroups as $group) {
            $row = $table->createRow(
                array(
                    'asset_id' => $assetId,
                    'asset_group_id' => $group->getId(),
                )
            )->save();
        }
    }

    /**
     * Save template
     *
     * @param Aino_Model_Asset $asset
     * @return \Aino_Model_Asset
     */
    public function save(Aino\Model\Asset $asset)
    {
        $assetId = $asset->getId();
        $assetTable = new Api_Model_DbTable_Asset();
        $assetGroups = $asset->getGroups();
        $assetData = $asset->toArray(false);

        if ($assetId) {
            $assetRowset = $assetTable->find($assetId);
            if (count($assetRowset)) {
                $assetRow = $assetRowset[0];
                $assetData['modified'] = new Zend_Db_Expr('NOW()');
                $assetRow->setFromArray($assetData);
            } else {
                throw new Zend_Db_Exception('Invalid template id.');
            }
        } else {
            $auth = Zend_Auth::getInstance();

            if ($auth->hasIdentity()) {
                $assetData['user_id'] = $auth->getIdentity()->getId();
            } else {
                throw new Zend_Db_Exception(
                    'Unable to save template. No user found'
                );
            }

            $assetRow = $assetTable->createRow($assetData);
        }

        $assetId = $assetRow->save();
        if ($assetId) {
            $this->saveAssetGroupLinks($assetId, $assetGroups, $assetRow);

            $asset = $this->find($assetRow['asset_id']);
            $asset
                ->clearCache()
                ->clearThumbnailCache();

            return $asset;
        }

        return null;
    }

    /**
     * Delete template
     *
     * @param integer|Aino_Model_Asset $assetId
     * @return bool
     */
    public function delete($assetId)
    {
        if ($assetId instanceof Aino\Model\Asset) {
            $asset = $assetId;
            $assetId = $asset->getId();
        } else {
            $asset = $this->find($assetId);
        }

        $assetTable = new Api_Model_DbTable_Asset();
        $where = $assetTable->getAdapter()->quoteInto(
            'asset_id=?',
            $assetId
        );

        if ($assetTable->delete($where) && $asset->getPath()) {
            $asset->clearCache();
            $asset->clearThumbnailCache();
            return unlink($asset->getPath(true));
        }

        return false;
    }
}