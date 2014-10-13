<?php
namespace Aino;

/**
 * Abstract class for models
 */
abstract class Model
{
    static $serverUrl = null;

    protected $idColumnName;

    protected $id;

    protected $toArrayBlacklist = array();

    protected $apiUrl = '';

    /**
     * Factory method
     * @param array|ArrayAccess $properties
     * @return \self
     */
    public static function factory($properties)
    {
        $className = get_called_class();
        $object = new $className();

        $object->updateFromArray($properties);

        if (isset($properties[$object->getIdColumnName()])) {
            $object->setId(
                (string) $properties[$object->getIdColumnName()]
            );
        }

        return $object;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return \self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get id column name
     *
     * @return string
     */
    public function getIdColumnName()
    {
        return $this->idColumnName;
    }

    /**
     * Get Server url
     * @return type
     */
    protected static function compileServerUrl()
    {
        if (self::$serverUrl == null) {
            $baseUrl = \Zend_Controller_Front::getInstance()->getBaseUrl();
            $serverUrl = new \Zend_View_Helper_ServerUrl();
            self::$serverUrl = $serverUrl->serverUrl($baseUrl);
        }

        return self::$serverUrl;
    }

    /**
     * Get template url
     * @return type
     */
    public function getUrl()
    {
        $baseUrl = self::compileServerUrl();

        if ($this->apiUrl) {
            return $baseUrl . $this->apiUrl . $this->getId();
        }

        return null;
    }

    /**
     * Normalize the value for toArray method
     *
     * @param mixed $value
     * @return mixed
     */
    private function normalizeValue($value)
    {
        if (is_array($value) || $value instanceof SeekableIterator) {
            $returnArray = array();
            foreach ($value as $index => $subValue) {
                $returnArray[$index] = $this->normalizeValue($subValue);
            }

            return $returnArray;
        } else if (is_object($value)) {
            switch (true) {
                case method_exists($value, 'toArray'):
                    return $value->toArray();
                    break;
                case method_exists($value, '__toString'):
                    return (string) $value;
                    break;
                default:
                    return $value;
                    break;
            }
        }

        return $value;
    }

    /**
     * To array method
     *
     * @param bool $hideIdColumnRealName
     * @return array
     */
    public function toArray(
        $hideIdColumnRealName = true,
        $ignoreBlacklist = false)
    {
        $propertyArray = array();
        foreach (get_class_methods($this) as $method) {

            if (stripos($method, 'get') === 0) {
                $property = substr($method, 3);
                $property{0} = strtolower($property{0});

                if (!in_array($property, $this->toArrayBlacklist) ||
                    $ignoreBlacklist) {
                    $propertyArray[$property] =
                        $this->normalizeValue($this->$method());
                }
            }

        }

        unset($propertyArray['idColumnName']);

        if (false === $hideIdColumnRealName && $this->getIdColumnName()) {
            $propertyArray[$this->getIdColumnName()] = $propertyArray['id'];
            unset($propertyArray['id']);
        }

        return $propertyArray;
    }

    /**
     * To json format
     * @param bool $hideIdColumnRealName
     * @return string
     */
    public function toJson($hideIdColumnRealName = true)
    {
        return \Zend_Json::encode($this->toArray($hideIdColumnRealName));
    }

    public function updateFromArray($properties)
    {
        foreach($properties as $property => $value) {
            $method = 'set' . ucfirst((string) $property);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
}
