<?php
namespace Aino\Model\Template;
use Aino\Model;
use Aino\Model\Set;
use Aino\Model\Template\Parameter\Dependency;

abstract class Parameter extends Model
{
    const PARAMETER_TYPE_ASSET = 'asset';

    const PARAMETER_TYPE_UGC = 'ugc';

    const PARAMETER_TYPE_SCALAR = 'scalar';

    protected $idColumnName = 'id';

    protected $toArrayBlacklist = array('url', 'template', 'value', 'element');

    protected $elementId;

    protected $template;

    protected $element;

    protected $label = '';

    protected $dependencies;

    protected $filters;

    protected $type;

    protected $copyFrom;

    protected $interactive;

    protected $component;

    static public function parseParameter($parameterXML) {}

    abstract public function setValue($value);

    abstract public function getValue();

    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
        return $this;
    }

    public function getElementId()
    {
        return $this->elementId;
    }

    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->element;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Build filter from array
     * @param array $filterArray
     * @return \Aino_Model_Asset_Filter
     */
    private function buildFilter($filterArray)
    {
        $filter = null;
        if (isset($filterArray['type'])) {
            $className =
                'Aino\\Model\\Template\\Parameter\\Filter\\' .
                ucfirst($filterArray['type']);

            if (class_exists($className)) {
                $filter = $className::factory($filterArray);
            }
        }

        return $filter;
    }

    /**
     * @param array $filters
     * @return \Aino_Model_Template_Parameter
     */
    public function addFilters($filters, $prepend = false)
    {
        foreach ($filters as $filter) {

            if (false == ($filter instanceof Parameter\Filter)) {
                $filter = $this->buildFilter($filter);
            }

            if ($filter) {
                if ($prepend) {
                    $this->filters->prependItem($filter);
                } else {
                    $this->filters->addItem($filter);
                }
            }
        }

        return $this;
    }

    public function setFilters(Set $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function getFilters()
    {
        if ($this->filters === null) {
            $this->filters = new Set();
        }

        return $this->filters;
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

    public function setCopyfrom($copyFrom)
    {
        $this->copyFrom = $copyFrom;
        return $this;
    }

    public function getCopyFrom()
    {
        return $this->copyFrom;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setInteractive($interactive)
    {
        $this->interactive = $interactive;
        return $this;
    }

    public function getInteractive()
    {
        return (bool) $this->interactive;
    }

    public function IsComponent()
    {
        return $this->getIsComponent();
    }

    public function getIsComponent()
    {
        if (isset($this->component['parentId']) &&
            $this->component['parentId']){
            return true;
        }

        return false;
    }

    public function setComponent($component)
    {
        $this->component = $component;
        return $this;
    }

    public function getComponent()
    {
        return $this->component;
    }


    static protected function parseDependencies($parameterXML)
    {
        $dependenciesXML = $parameterXML->xpath(
            './aino:dependencies/aino:dependency'
        );
        $dependencies = new Set();

        foreach ($dependenciesXML as $dependencyXML) {
            $dependencies->addItem(
                Dependency::parseDependency($dependencyXML)
            );
        }

        return $dependencies;
    }

    static function castBoolean($value)
    {
        $value = strtolower((string) $value);

        if ($value === 'false') {
            return false;
        }

        return (bool) $value;
    }

    static public function parseSelectOptions($xml)
    {
        $optionsXML = $xml->xpath('./aino:selectOptions/aino:option');
        $options = new Set();
        foreach($optionsXML as $index => $optionXML) {
            $editable = true;
            if (isset($optionXML['editable'])) {
                $editable = self::castBoolean($optionXML['editable']);
            }

            $option = array(
                'label' => (string) $optionXML['label'],
                'value' => (string) $optionXML,
                'editable' => $editable
            );

            $options->addItem(Parameter\Option::factory($option));
        }

        return $options;
    }

    static public function parseFiltersOptions($filterXML)
    {
        $options = array();

        foreach($filterXML->xpath('./aino:option') as $optionXML) {

            $value = $optionXML;
            $name = (string) $optionXML['name'];
            /*
            $copyFrom = (string) $optionXML['copyFrom'];

            if ($copyFrom) {
                $parameter = $this->parameters->findById($copyFrom);

                if ($parameter) {
                    $value = $parameter->getValue();
                }
            }*/

            if (isset($options[$name])) {
                if (!(is_array($options[$name]))) {
                    $options[$name] = array($options[$name]);
                }
                $options[$name][] = $value;
            } else {
                $options[$name] = $value;
            }
        }

        return $options;
    }

    static public function parseFilters($xml)
    {
        $filtersXML = $xml->xpath('./aino:filters/aino:filter');
        $filters = new Set();
        foreach($filtersXML as $index => $filterXML) {
            $type = $filterXML['type'];
            $className =
                'Aino\\Model\\Template\\Parameter\\Filter\\' .
                ucfirst($type);

            if (class_exists($className)) {

                $filter = new $className();
                $filter
                    ->setId($index)
                    ->setType($type)
                    ->setOptions(self::parseFiltersOptions($filterXML));

                $filters->addItem($filter);
            } else {
                //@TODO Log filter class not found
            }
        }

        return $filters;
    }

    static public function parseComponent($xml)
    {
        $componentsXML = $xml->xpath('./aino:component');
        if($componentsXML) {
            $component = array(
                'name' => $componentsXML[0]['name'],
                'parentId' => $componentsXML[0]['of']
            );
        } else {
            $component = array(
                'name' => '',
                'parentId' => null
            );
        }

        return $component;
    }
}