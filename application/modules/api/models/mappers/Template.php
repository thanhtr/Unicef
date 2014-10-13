<?php
use Aino\Model\Set;
use Aino\Model\Template;

/**
 * SVG Template mapper
 */
class Api_Model_Mapper_Template extends Api_Model_Mapper_Abstract
{
    /**
     * Find template by id.
     *
     * @param type $templateId
     * @return \Aino_Model_Template Null if not found
     */
    public function find($templateId)
    {
        $templateTable = new Api_Model_DbTable_Template();

        $templateRowset = $templateTable->find($templateId);

        if (count($templateRowset)) {
            $templateRow = $templateRowset[0];
            $template = Template::factory($templateRow);

            $template->setUser($this->findIdentity($templateRow['user_id']));

            $typeRowset = $templateRow->findManyToManyRowset(
                'Api_Model_DbTable_TemplateType',
                'Api_Model_DbTable_TemplateHasTemplateType'
            );

            foreach ($typeRowset as $typeRow) {
                $template->addType(
                    Template\Type::factory($typeRow)
                );
            }

            return $template;
        }

        return null;
    }

    /**
     * Fetch all templates.
     *
     * @param array|string $typesArray Contains types for filtering resultset
     * @return \Aino_Model_Template
     */
    public function fetchAll($typesArray = array())
    {
        $templateTable = new Api_Model_DbTable_Template();

        $select = $templateTable->select()->setIntegrityCheck(false);
        $select->order('name');
        $select
            ->distinct()
            ->from($templateTable)
            ->joinLeft(
                'template_has_template_type',
                '`template_has_template_type`.`template_id`=`template`.`template_id`',
                array()
            )
            ->joinLeft(
                'template_type',
                '`template_has_template_type`.`template_type_id`=`template_type`.`template_type_id`',
                array()
            );

        if (!is_array($typesArray)) {
            $typesArray = array($typesArray);
        }

        foreach ($typesArray as $type) {
            if ($type instanceof Aino\Model\Template\Type) {
                $type = $type->getName();
            }

            $select->orWhere('`template_type`.`name`=?', $type);
        }

        $templateRowset = $templateTable->fetchAll($select);

        $templates = new Set();
        foreach ($templateRowset as $templateRow) {
            $template = Template::factory($templateRow);
            $template->setUser($this->findIdentity($templateRow['user_id']));

            $templateTypeRowset = $templateRow->findManyToManyRowset(
                'Api_Model_DbTable_TemplateType',
                'Api_Model_DbTable_TemplateHasTemplateType'
            );

            foreach ($templateTypeRowset as $templateTypeRow) {
                $template->addType(
                    Template\Type::factory($templateTypeRow)
                );
            }

            $templates->addItem($template);
        }

        return $templates;
    }

    /**
     * Save template
     *
     * @param Aino_Model_Template $template
     * @return \Aino_Model_Template
     */
    public function save(Aino\Model\Template $template)
    {
        $templateId = $template->getId();
        $templateTable = new Api_Model_DbTable_Template();
        $templateData = $template->toArray(false);

        if ($templateId) {
            $templateRowset = $templateTable->find($templateId);
            if (count($templateRowset)) {
                $templateRow = $templateRowset[0];
                $templateData['modified'] = new Zend_Db_Expr('NOW()');
                $templateRow->setFromArray($templateData);
                $templateRow['svg'] = $template->getSvg(false);
            } else {
                throw new Zend_Db_Exception('Invalid template id.');
            }
        } else {
            $auth = Zend_Auth::getInstance();

            if ($auth->hasIdentity()) {
                $templateData['user_id'] = $auth->getIdentity()->getId();
            } else {
                throw new Zend_Db_Exception('Unable to save template.');
            }

            $templateRow = $templateTable->createRow($templateData);
            $templateRow['svg'] = $template->getSvg(false);
        }

        $templateId = $templateRow->save();
        if ($templateId) {
            $template->setId($templateId);
            $this->saveTemplateTypeLinks($template);
            $template = $this->find($templateRow['template_id']);
            $template->clearThumbnailCache();

            return $template;
        }

        return null;
    }

    protected function saveTemplateTypeLinks($template)
    {
        $table = new Api_Model_DbTable_TemplateHasTemplateType();
        $templateId = $template->getId();

        $where = $table->getAdapter()->quoteInto(
            'template_id=?',
            $templateId
        );

        $table->delete($where);

        foreach ($template->getTypes() as $type) {
            $table->createRow(
                array(
                    'template_id' => $templateId,
                    'template_type_id' => $type->getId(),
                )
            )->save();
        }

        return $this;
    }

    /**
     * Delete template
     *
     * @param integer|Aino_Model_Template $templateId
     * @return bool
     */
    public function delete($templateId)
    {
        if ($templateId instanceof Aino\Model\Template) {
            $templateId = $templateId->getId();
        }

        $templateTable = new Api_Model_DbTable_Template();
        $where = $templateTable->getAdapter()->quoteInto(
            'template_id=?',
            $templateId
        );

        return (bool) $templateTable->delete($where);
    }

    /**
     * @return \Api_Model_Mapper_Template
     */
    private function log($templateId, $userId, $parameters, $format)
    {
        $printLog = new Api_Model_DbTable_PrintLog();
        $data = array(
            'template_id' => $templateId,
            'user_id' => $userId,
            'parameters' => serialize($parameters),
            'format' => $format
        );

        return (bool) $printLog->createRow($data)->save();
    }

    /**
     *
     * @param \Aino_Model_Template $template
     * @return bool
     */
    public function updateQuota($template, $parameters, $format)
    {
        $userTable = new Auth_Model_DbTable_User();

        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $userId = $auth->getIdentity()->getId();
        } else {
            return false;
        }

        $userRows = $userTable->find($userId);
        if(count($userRows) == 1) {
            $userRow = $userRows[0];

            $this->log(
                $template->getId(),
                $userRow['user_id'],
                $parameters,
                $format
            );

            if (false == $userRow['use_quota']) {
                return true;
            } else if ($userRow['quota'] > 0) {
                $userRow['quota'] = $userRow['quota'] - 1;
                $userId = $userRow->save();
                return true;
            }

            return false;
        }

        return false;
    }
}