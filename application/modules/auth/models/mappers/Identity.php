<?php

class Auth_Model_Mapper_Identity extends Aino_Mapper_Abstract
{
    protected $_namespace = 'Auth_Model_DbTable_';

    protected $_dbTables = array(
        'domainHasRole' => null,
        'user' => null
    );

    private function _getUserTable()
    {
        return $this->_getTable('user');
    }

    private function _getAccessTable()
    {
        return $this->_getTable('domainHasRole');
    }

    private function _buildSelect($table)
    {
        $select = $table->select()->setIntegrityCheck(false);
        $select
            ->from(
                $table,
                array(
                    'id' => 'user_id',
                    'username' => 'username',
                    'status' => 'status'
                )
            )
            ->join(
                'user_role',
                '`user`.`user_role_id`=`user_role`.`user_role_id`',
                array(
                    'role' => 'name',
                    'roleId' => 'user_role_id'
                )
            )
            ->join(
                'user_domain',
                '`user`.`user_domain_id`=`user_domain`.`user_domain_id`',
                array(
                	'domain' => 'domain',
                	'domainId' => 'user_domain_id'
                )
            );

        return $select;
    }

    private function _buildIdentity($properties)
    {
        if ($properties) {
            $properties = $properties->toArray();
            $properties['email'] =
                $properties['username'] . '@' . $properties['domain'];

            return new Aino_Auth_Identity($properties);
        }

        return null;
    }

    /**
     * Find identity by email
     *
     * @param Aino_Email $email
     * @return Tod_Auth_Identity
     */
    public function find($email)
    {
        if (!$email instanceof Aino_Email) {
            $email = new Aino_Email($email);
        }

        $table = $this->_getUserTable();
        $select = $this->_buildSelect($table);
        $select
            ->where('`user`.`username`=?', $email->getUsername())
            ->where('`user_domain`.`domain`=?', $email->getDomain());

        $properties = $table->fetchRow($select);

        if ($properties) {
            return $this->_buildIdentity($properties);
        } else {
            return $this->getIdentityByDomainAccess($email);
        }
    }

    /**
     * Find Identity by user id
     *
     * @param integer $identityId
     * @return \Aino_Auth_Identity
     */
    public function findById($identityId)
    {
        $table = $this->_getUserTable();
        $select = $this->_buildSelect($table);
        $select
            ->where('`user`.`user_id`=?', $identityId);

        $properties = $table->fetchRow($select);

        return $this->_buildIdentity($properties);

    }

    /**
     * Save Identity
     *
     * @param Aino_Auth_Identity $identity
     * @return boolean
     */
    public function save($identity)
    {
        $table = $this->_getUserTable();
        $data = array(
            'username' => $identity->getUsername(),
            'user_role_id' => $identity->getRoleId(),
            'status' => $identity->getStatus(),
            'user_domain_id' => $identity->getDomainId()
        );

        $rowSet = $table->find($identity->getId());

        if ($rowSet->count()) {
             $row = $rowSet->current();
             $row->setFromArray($data);
        } else {
            $row = $table->createRow($data);
        }

        return $identity->setId($row->save());
    }

    public function getIdentityByDomainAccess($email)
    {
        if (!$email instanceof Aino_Email) {
            $email = new Aino_Email($email);
        }

        $table = $this->_getAccessTable();
        $select = $table->select()->setIntegrityCheck(false);

        $select
            ->from($table, array())
            ->join(
                'user_domain',
                '`domain_has_role`.`user_domain_id`=`user_domain`.`user_domain_id`',
                array(
                    'domainId' => 'user_domain_id',
                    'domain' => 'domain'
                )
            )
            ->join(
                'user_role',
                '`domain_has_role`.`user_role_id`=`user_role`.`user_role_id`',
                array(
                    'roleId' => 'user_role_id',
                    'role' => 'name'
                )
            )
            ->where(
                '`user_domain`.`domain`=?',
                $email->getDomain()
            );

        $properties = $table->fetchRow($select);

        if ($properties) {
            $properties = $properties->toArray();
            $properties['email'] = $email;
            $properties['status'] = Aino_Auth_Identity::STATUS_ACTIVE;
            $identity = new Aino_Auth_Identity($properties);
            $this->save($identity);
            return $identity;
        }

        return null;
    }

    public function delete($identity)
    {
        $table = $this->_getUserTable();
        return is_numeric(
            $table->delete(
                array('`user_id`=?' => $identity->getId())
            )
        );
    }

}
