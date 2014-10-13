<?php
namespace Aino\Model\Template\Parameter\Type;
use Aino\Model\Template\Parameter;
use Aino\Model\Set;

class Text extends Parameter
{
    protected $value = '';

    protected $selectOptions = null;

    protected $editable = false;

    static public function parseParameter($parameterXML)
    {
        $parameter = self::factory($parameterXML->attributes());
        $parameter
            ->setDependencies(
                $parameter->parseDependencies($parameterXML)
            )
            ->setSelectOptions(
                $parameter->parseSelectOptions($parameterXML)
            )
            ->setFilters(
                $parameter->parseFilters($parameterXML)
            );

        $parameter->setEditable(self::castBoolean($parameterXML['editable']));

        return $parameter;
    }

    public function setSelectOptions(Set $options)
    {
        $this->selectOptions = $options;
        return $this;
    }

    public function getSelectOptions()
    {
        if ($this->selectOptions === null) {
            $this->selectOptions = new Set();
        }

        return $this->selectOptions;
    }

    protected function emptyElement()
    {
        $element = $this->getElement();

        $tags = array();
        foreach ($element as $tagName => $node) {
            $tags[$tagName] = $tagName;
        }

        foreach ($tags as $tag) {
            unset($element->$tag);
        }

        return $this;
    }

    public function setValue($value)
    {
        $this->emptyElement();
        $element = $this->getElement();

        $filters = $this->getFilters();

        if (count($filters)) {
            foreach ($filters as $filter) {
                $filter->apply(
                    array(
                        'value' => $value,
                        'element' => $element
                    )
                );
            }
        } else {
            $element[] = $value;
        }

        return $this;
    }

    public function getValue()
    {
        return (string) $this->getElement();
    }

    public function setEditable($editable)
    {
        $this->editable = (bool) $editable;
        return $this;
    }

    public function getEditable()
    {
        return $this->editable;
    }
}