<?php

/**
 * Component mapper
 */
class Api_Model_Mapper_Component extends Api_Model_Mapper_Abstract
{
    protected function fetchOptions($modifierRow)
    {
        $optionRows = $modifierRow->findDependentRowset(
            'Api_Model_DbTable_ComponentModifierOption'
        );

        $options = array();
        foreach ($optionRows as $optionRow) {
            $optionName = $optionRow->name;

            if (isset($options[$optionName])) {
                if (false == is_array($options[$optionName])) {
                    $options[$optionName] = array(
                        $options[$optionName]
                    );
                }

                $options[$optionName][] = $optionRow->value;
            } else {
                $options[$optionName] = $optionRow->value;
            }
        }

        return $options;
    }

    protected function buildComponent($componentRow)
    {
        $component = Aino\Model\Asset\Component::factory($componentRow);

        $componentModifierRows = $componentRow->findDependentRowset(
            'Api_Model_DbTable_ComponentModifier'
        );

        foreach ($componentModifierRows as $modifierRow) {
            $className =
                'Aino\\Model\\Template\\Parameter\\Filter\\' .
                ucfirst($modifierRow->type);

            if (class_exists($className)) {
                $filter = new $className();
                $filter->setId($modifierRow['component_modifier_id']);
                $filter->setType($modifierRow->type);

                $filter->setOptions($this->fetchOptions($modifierRow));
                $component->addFilter($filter);

            } else {
                //@TODO Log filter class not found
            }
        }

        $assetGroupRows = $componentRow->findManyToManyRowset(
            'Api_Model_DbTable_AssetGroup',
            'Api_Model_DbTable_ComponentHasAssetGroup'
        );

        $component->setGroups($assetGroupRows);

        return $component;
    }

    public function find($componentId)
    {
        $componentTable = new Api_Model_DbTable_Component();
        $select = $componentTable->select()->where(
            'component_id=?',
            $componentId
        );

        $component = null;
        $componentRow = $componentTable->fetchRow($select);
        if ($componentRow) {
            $component = $this->buildComponent($componentRow);
        }

        return $component;
    }

    /**
     * Find Components for asset
     *
     * @param \Aino_Model_Asset $assetName
     * @return \Aino_Model_Asset_Component Null if not found
     */
    public function findAllForAsset($asset)
    {
        if ($asset instanceof Aino\Model\Asset) {
            $asset = $asset->getId();
        }

        $components = new Aino\Model\Set();
        if ($asset) {
            $componentTable = new Api_Model_DbTable_Component();
            $select = $componentTable->select()->setIntegrityCheck(false);
            $select
                ->from($componentTable)
                ->joinLeft(
                    'asset',
                    '`asset`.`asset_id`=`component`.`asset_id`',
                    array()
                )
                ->where('`asset`.`asset_id`=?', $asset);

            $componentRows = $componentTable->fetchAll($select);

            foreach ($componentRows as $componentRow) {
                $component = $this->buildComponent($componentRow);
                if($component) {
                    $components->addItem($component);
                }
            }
        }

        return $components;
    }

    protected function saveGroups($componentRow, $groups = array())
    {
        $assetGroupRows = $componentRow->findDependentRowset(
            'Api_Model_DbTable_ComponentHasAssetGroup'
        );

        foreach($assetGroupRows as $assetGroupRow) {
            $assetGroupRow->delete();
        }

        $data = array(
            'component_id' => $componentRow['component_id']
        );

        $table = new Api_Model_DbTable_ComponentHasAssetGroup();
        foreach ($groups as $group) {
            $data['asset_group_id'] = $group['id'];
            $row = $table->createRow($data);
            $row->save();
        }

        return $this;
    }

    /**
     * Save json
     *
     * @param string $json
     * @return \Aino_Model_Asset_Component
     */
    public function saveFromJson($json)
    {
        $data = Zend_Json::decode($json);

        if (isset($data['asset_id']) && isset($data['name'])) {
            $componentTable = new Api_Model_DbTable_Component();

            $select = $componentTable->select();
            $select
                ->where('asset_id=?', $data['asset_id'])
                ->where('name=?', $data['name']);

            $componentRow = $componentTable->fetchRow($select);

            if ($componentRow) {
                $componentRow->setFromArray($data);
            } else {
                $componentRow = $componentTable->createRow($data);
            }

            $componentId = $componentRow->save();
            if ($componentId) {
                if (isset($data['groups'])) {
                    $this->saveGroups($componentRow, $data['groups']);
                }

                $modifierRows = $componentRow->findDependentRowset(
                    'Api_Model_DbTable_ComponentModifier'
                );

                foreach ($modifierRows as $modifierRow) {
                    $modifierRow->delete();
                }

                $modifierTable = new Api_Model_DbTable_ComponentModifier();
                foreach ($data['filters'] as $filter) {
                    $modifierRow = $modifierTable->createRow($filter);
                    $modifierRow['component_id'] = $componentId;

                    if ($modifierRow->save()) {
                        $modifierId = $modifierRow['component_modifier_id'];
                        $optionTable =
                            new Api_Model_DbTable_ComponentModifierOption();
                        $sequence = 0;
                        foreach ($filter['options'] as $name => $value) {
                            $option = array(
                                'component_modifier_id' => $modifierId,
                                'sequence' => $sequence,
                                'name' => $name,
                                'value' => $value
                            );

                            if (is_array($value)) {
                                foreach ($value as $subValue) {
                                    $option['value'] = $subValue;
                                    $option['sequence'] = $sequence;
                                    $optionRow =
                                        $optionTable->createRow($option);
                                    $optionRow->save();
                                    $sequence++;
                                }
                            } else {
                                $optionRow =
                                    $optionTable->createRow($option);
                                $optionRow->save();
                                $sequence++;
                            }
                        }
                    }
                }

                return $this->find($componentId);
            }
        }
    }
}