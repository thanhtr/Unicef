<?php

class Api_Model_Mapper_AssetGroup extends Api_Model_Mapper_Abstract
{
    /**
     * Find asset group by id.
     *
     * @param integer $assetId
     * @return \Aino_Model_Asset_Group Null if not found
     */
    public function find($assetGroupId)
    {
        $assetGroupTable = new Api_Model_DbTable_AssetGroup();

        $assetGroupRowset = $assetGroupTable->find($assetGroupId);

        if (count($assetGroupRowset)) {
            $assetGroupRow = $assetGroupRowset[0];
            $assetGroup = Aino\Model\Asset\Group::factory($assetGroupRow);

            return $assetGroup;
        }

        return null;
    }

    /**
     * Find asset group by name
     *
     * @param string $assetName
     * @return \Aino_Model_Asset_Group Null if not found
     */
    public function findByName($assetGroupName)
    {
        $assetGroupTable = new Api_Model_DbTable_AssetGroup();

        $select = $assetGroupTable->select();
        $select->where('name=?', $assetGroupName);

        $assetGroupRow = $assetGroupTable->fetchRow($select);

        if ($assetGroupRow) {
            $assetGroup = Aino\Model\Asset\Group::factory($assetGroupRow);

            return $assetGroup;
        }

        return null;
    }

    /**
     * Fetch all asset groups.
     *
     * @return \Aino_Model_Set
     */
    public function fetchAll()
    {
        $assetGroupTable = new Api_Model_DbTable_AssetGroup();

        $assetGroupRowset = $assetGroupTable->fetchAll(null, 'name');

        $assetGroups = new Aino\Model\Set();
        foreach ($assetGroupRowset as $assetGroupRow) {
            $assetGroup = Aino\Model\Asset\Group::factory($assetGroupRow);

            $assetGroups->addItem($assetGroup);
        }

        return $assetGroups;
    }

    /**
     * Save asset group
     *
     * @param Aino_Model_Asset $asset
     * @return \Aino_Model_Asset
     */
    public function save(Aino\Model\Asset\Group $assetGroup)
    {
        $assetGroupTable = new Api_Model_DbTable_AssetGroup();

        if ($assetGroup->getId()) {
            $assetGroupRowset =
                $assetGroupTable->find($assetGroup->getId());

            if (count($assetGroupRowset) == 1) {
                $assetGroupRow = $assetGroupRowset[0];
                $assetGroupRow->setFromArray($assetGroup->toArray());
            }
        } else {
            $assetGroupRow =
                $assetGroupTable->createRow($assetGroup->toArray(false));
        }

        if ($assetGroupRow && $assetGroupRow->save()) {
            return Aino\Model\Asset\Group::factory($assetGroupRow);
        }

        return null;
    }

    /**
     * Delete asset group
     *
     * @param integer|Aino_Model_Asset $assetId
     * @return bool
     */
    public function delete($assetGroupId)
    {
        if ($assetGroupId instanceof Aino\Model\Asset\Group) {
            $assetGroupId = $assetGroupId->getId();
        }

        $assetGroupTable = new Api_Model_DbTable_AssetGroup();
        $where = $assetGroupTable->getAdapter()->quoteInto(
            'asset_group_id=?',
            $assetGroupId
        );

        return (bool) $assetGroupTable->delete($where);
    }
}