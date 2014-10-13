<?php
namespace Aino\Model\Template\Parameter\Type;
use Aino\Model\Template\Parameter;

class Scalar extends Parameter
{
    protected $attribute;

    static public function parseParameter($parameterXML)
    {
        $parameter = self::factory($parameterXML->attributes());
        $parameter
            ->setDependencies(
                $parameter->parseDependencies($parameterXML)
            );

        return $parameter;
    }

    public function setValue($value)
    {
        $attributes = $this->getElement()->attributes();

        $attributes[$this->getAttribute()] = $value;

        return $this;
    }

    public function getValue()
    {
        $attributes = $this->getElement()->attributes();

        return $attributes[$this->getAttribute()];
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }
}