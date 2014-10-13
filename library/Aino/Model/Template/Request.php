<?php
namespace Aino\Model\Template;
use Aino\Model;

class Request extends Model
{
    /**
     * @var string
     */
    protected $idColumnName = 'name';

    /**
     * @var array
     */
    protected $toArrayBlacklist = array('url');

    /**
     * @var array
     */
    protected $value;

    /**
     * @var array 
     */
    protected $filters = array();

    /**
     *
     * @param array $value
     * @return \Aino_Model_Template_Parameter_Http
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }
}