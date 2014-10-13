<?php

/**
 *
 * @category BridgeApp
 * @package App
 * @subpackage Auth
 * @author fieinsaar
 */
class Auth_Model_Mapper_Token extends Aino_Mapper_Abstract
{
    protected $_namespace = 'Auth_Model_DbTable_';

    protected $_dbTables = array('token' => null);


    /**
     * Get token table object
     *
     * @return Zend_Db_Table_Abstract
     */
    private function _getTokenTable()
    {
        return $this->_getTable('token');
    }

    /**
     * Save token
     *
     * @param Auth_Model_Token $token
     */
    public function save($token)
    {
        $table = $this->_getTokenTable();
        $identity = $token->getIdentity();
        $date = date('Y-m-d H:i:s');

        $rowSet = $table->find($identity->getId());
        if ($rowSet->count()) {
             $row = $rowSet->current();
             $row['token'] = $token->getToken();
             $row['created'] =
                    new Zend_Db_Expr(
                    	"CURRENT_TIMESTAMP"
                    );
             $row['used'] = 0;
        } else {
            $data = array(
                'user_id' => $identity->getId(),
                'token' => $token->getToken(),
                'used' => 0
            );
            $row = $table->createRow($data);
        }

        return (bool) $row->save();
    }

    /**
     * Removes all tokens from the database
     *
     * @return boolean
     */
    public function clearAll()
    {
        $table = $this->_getTokenTable();
        return is_numeric($table->delete(array()));
    }

    /**
     * Find token
     *
     * @param string $token
     * @return Auth_Model_Token
     */
    public function find($tokenString)
    {
        if ($tokenString instanceof Auth_Model_Token) {
            $tokenString = $tokenString->getToken();
        }

        $table = $this->_getTokenTable();
        $select = $table->select();
        $select
            ->where('`token`.`token`=?', $tokenString)
            ->where('`token`.`used`=0');

        $row = $table->fetchRow($select);

        if ($row) {
            $identityModel = new Auth_Model_Mapper_Identity();
            $identity = $identityModel->findById($row['user_id']);
            $token = new Auth_Model_Token($identity);
            $token->setToken($tokenString);
            $row['used'] = 1;
            $row->save();
            return $token;
        }

        return null;
    }

}
