<?php

abstract class Api_Model_Mapper_Abstract
{
    /**
     * Find identity
     * @param integer $identityId
     * @return \Aino_Auth_Identity
     */
    protected function findIdentity($identityId)
    {
        $identityMapper = new Auth_Model_Mapper_Identity();
        $identity = $identityMapper->findById($identityId);

        if ($identity) {
            return $identity;
        }

        return null;
    }
}