<?php
namespace Aino\Model\Template\Parameter;
use Aino\Model;

abstract class Filter extends Model
{
    /**
     * @var bool
     */
    public $applied = false;

    protected $idColumnName = 'component_modifier_id';

    protected $toArrayBlacklist = array('url');

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var string
     */
    protected $type;

    protected $sequence = null;

    abstract public function apply($value);

    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->options = $options;
        }

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getSequence()
    {
        if ($this->sequence == null) {
            $this->sequence = $this->getId();
        }

        return $this->sequence;
    }

    protected function stringifyOption($options)
    {
        if (is_array($options)) {
            $result = '';
            foreach ($options as $key => $option) {
                $result .= $key . $this->stringifyOption($option);
            }
            return $result;
        } else {
            return (string) $options;
        }
    }

    public function compileCacheKey()
    {
        $cacheKey = get_called_class();
        $cacheKey .= $this->stringifyOption($this->getOptions());

        return md5($cacheKey);
    }
}