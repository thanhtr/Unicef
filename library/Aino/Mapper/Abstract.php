<?php

abstract class Aino_Mapper_Abstract
{
    protected $_namespace = null;

    protected $_dbTables = array();

    /**
     * Get table for the name
     *
     * @param string $name
     * @return Zend_Db_Table_Abstract
     * @throws Aino_Exception If name is not allowed
     *
     */
    protected function _getTable($name)
    {
        if (null == $this->_namespace) {
            require_once 'Todui/Exception.php';
            throw new Aino_Exception('Namespace is not specified');
        }

        if (array_key_exists($name, $this->_dbTables)) {
            if (is_null($this->_dbTables[$name])) {
                $class = $this->_namespace . ucfirst($name);
                $this->_dbTables[$name] = new $class;
            }

            return $this->_dbTables[$name];
        } else {
            require_once 'Todui/Exception.php';
            throw new Aino_Exception("table '{$name}' is not allowed table");
        }
    }

    public function setAllowedDbTableNames($tableNames)
    {
        foreach ($tableNames as $tableName) {
            if (!key_exists($tableName, $this->_dbTables)) {
                $this->_dbTables[$tableName] = null;
            }
        }

        return $this;
    }

    public function setDbTableNamespace($namespace)
    {
        $this->_namespace = $namespace;

        return $this;
    }
}
