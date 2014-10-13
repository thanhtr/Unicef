<?php
use Aino\Model\Set;
use Aino\Model\Template\Type;

/**
 * SVG Template mapper
 */
class Api_Model_Mapper_TemplateType
{
    /**
     * Find template type by id.
     *
     * @param type $templateId
     * @return \Api_Model_Template_Type Null if not found
     */
    public function find($templateTypeId)
    {
        $templateTypeTable = new Api_Model_DbTable_TemplateType();
        $rowset = $templateTypeTable->find($templateTypeId);

        if ($rowset && count($rowset) == 1) {
            return Aino\Model\Template\Type::factory($rowset[0]);
        }

        return null;
    }

    /**
     * Fetch all template types.
     *
     * @return array
     */
    public function fetchAll()
    {
        $templateTypeTable = new Api_Model_DbTable_TemplateType();

        $rowset = $templateTypeTable->fetchAll(null, 'name');

        $templateTypes = new Set();
        foreach ($rowset as $row) {
            $templateType = Type::factory($row);

            $templateTypes->addItem($templateType);
        }

        return $templateTypes;
    }

    /**
     * Save template type
     *
     * @param Api_Model_TemplateType $template
     * @return \Api_Model_Template_Type
     */
    public function save(Aino\Model\Template\Type $templateType)
    {
        $table = new Api_Model_DbTable_TemplateType();

        if ($templateType->getId()) {
            $templateTypeRowset =
                $table->find($templateType->getId());

            if (count($templateTypeRowset) == 1) {
                $templateTypeRow = $templateTypeRowset[0];
                $templateTypeRow->setFromArray($templateType->toArray());
            }
        } else {
            $templateTypeRow = $table->createRow(
                $templateType->toArray(false)
            );
        }

        if ($templateTypeRow && $templateTypeRow->save()) {
            return Aino\Model\Template\Type::factory($templateTypeRow);
        }

        return null;
    }

    /**
     * Delete template type
     *
     * @param integer|Api_Model_Template_Type $templateId
     * @return bool
     */
    public function delete($templateTypeId)
    {
        if ($templateTypeId instanceof Aino\Model\Template\Type) {
            $templateTypeId = $templateTypeId->getId();
        }

        $table = new Api_Model_DbTable_TemplateType();
        $where = $table->getAdapter()->quoteInto(
            'template_type_id=?',
            $templateTypeId
        );

        return (bool) $table->delete($where);
    }
}